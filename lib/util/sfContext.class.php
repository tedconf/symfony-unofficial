<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfContext provides information about the current application context, such as
 * the module and action names and the module directory. References to the
 * current controller, request, and user implementation instances are also
 * provided. Support for tones was introduced in June, 2007.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfContext
{
  const DEFAULT_TONE_NAMESPACE = 'symfony/default';

  protected
    $actionStack       = null,
    $controller        = null,
    $databaseManager   = null,
    $request           = null,
    $response          = null,
    $storage           = null,
    $viewCacheManager  = null,
    $i18n              = null,
    $logger            = null,
    $user              = null,
    $toneFactories     = array();

  protected static
    $instance          = null,
    $class             = __CLASS__;

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfContext A sfContext implementation instance.
   */
  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      if (!class_exists(self::$class))
      {
        throw new sfException('The class ' . self::$class . " used for creating the instance of sfContext doesn't exist");
      }

      self::$instance = new self::$class();
      self::$instance->initialize();
    }

    return self::$instance;
  }

  /**
   * Checks whether sfContext has an instance.
   *
   * @return boolean true if sfContext has an instance, false if not.
   */
  public static function hasInstance()
  {
    return isset(self::$instance);
  }

  /**
   * Removes current sfContext instance
   *
   * This method only exists for testing purpose. Don't use it in your application code.
   */
  public static function removeInstance()
  {
    self::$instance = null;
  }

  /**
   * Sets the class name to be used for creating the instance of sfContext.
   *
   * This allowes you to subclass sfContext and add or overwrite methods of it.
   * Recommended place to call it is in yourapp/config/config.php
   * Example:
   * <code>
   *   sfContext::setInstanceClass('MyContext');
   * </code>
   * @param string Class name used to create the sfContext instance.
   */
  public static function setInstanceClass($class)
  {
    self::$class = $class;
  }

  protected function initialize()
  {
    $this->logger = sfLogger::getInstance();
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->logger->info('{sfContext} initialization');
    }

    if (sfConfig::get('sf_use_database'))
    {
      // setup our database connections
      $this->databaseManager = new sfDatabaseManager();
      $this->databaseManager->initialize();
    }

    // create a new action stack
    $this->actionStack = new sfActionStack();

    // include the factories configuration
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/factories.yml'));

    $this->registerToneFactory('sfToneFactoryAdapter', self::DEFAULT_TONE_NAMESPACE);

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));
  }

  /**
   * Retrieve the action name for this context.
   *
   * @return string The currently executing action name, if one is set,
   *                otherwise null.
   */
  public function getActionName()
  {
    // get the last action stack entry
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getActionName();
    }
  }

  /**
   * Retrieve the ActionStack.
   *
   * @return sfActionStack the sfActionStack instance
   */
  public function getActionStack()
  {
    return $this->actionStack;
  }

  /**
   * Retrieve the controller.
   *
   * @return sfController The current sfController implementation instance.
   */
   public function getController()
   {
     return $this->controller;
   }

   public function getLogger()
   {
     return $this->logger;
   }

  /**
   * Retrieve a database connection from the database manager.
   *
   * This is a shortcut to manually getting a connection from an existing
   * database implementation instance.
   *
   * If the [sf_use_database] setting is off, this will return null.
   *
   * @param name A database name.
   *
   * @return mixed A Database instance.
   *
   * @throws <b>sfDatabaseException</b> If the requested database name does not exist.
   */
  public function getDatabaseConnection($name = 'default')
  {
    if ($this->databaseManager != null)
    {
      return $this->databaseManager->getDatabase($name)->getConnection();
    }

    return null;
  }

  public function retrieveObjects($class, $peerMethod)
  {
    $retrievingClass = 'sf'.ucfirst(sfConfig::get('sf_orm', 'propel')).'DataRetriever';

    return call_user_func(array($retrievingClass, 'retrieveObjects'), $class, $peerMethod);
  }

  /**
   * Retrieve the database manager.
   *
   * @return sfDatabaseManager The current sfDatabaseManager instance.
   */
  public function getDatabaseManager()
  {
    return $this->databaseManager;
  }

  /**
   * Retrieve the module directory for this context.
   *
   * @return string An absolute filesystem path to the directory of the
   *                currently executing module, if one is set, otherwise null.
   */
  public function getModuleDirectory()
  {
    // get the last action stack entry
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return sfConfig::get('sf_app_module_dir').'/'.$lastEntry->getModuleName();
    }
  }

  /**
   * Retrieve the module name for this context.
   *
   * @return string The currently executing module name, if one is set,
   *                otherwise null.
   */
  public function getModuleName()
  {
    // get the last action stack entry
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getModuleName();
    }
  }

  /**
   * Retrieve the curretn view instance for this context.
   *
   * @return sfView The currently view instance, if one is set,
   *                otherwise null.
   */
  public function getCurrentViewInstance()
  {
    // get the last action stack entry
    if ($this->actionStack && $lastEntry = $this->actionStack->getLastEntry())
    {
      return $lastEntry->getViewInstance();
    }
  }

  /**
   * Retrieve the request.
   *
   * @return sfRequest The current sfRequest implementation instance.
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Retrieve the response.
   *
   * @return sfResponse The current sfResponse implementation instance.
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Set the response object.
   *
   * @param sfResponse A sfResponse instance.
   *
   * @return void.
   */
  public function setResponse($response)
  {
    $this->response = $response;
  }

  /**
   * Retrieve the storage.
   *
   * @return sfStorage The current sfStorage implementation instance.
   */
  public function getStorage()
  {
    return $this->storage;
  }

  /**
   * Retrieve the view cache manager
   *
   * @return sfViewCacheManager The current sfViewCacheManager implementation instance.
   */
  public function getViewCacheManager()
  {
    return $this->viewCacheManager;
  }

  /**
   * Retrieve the i18n instance
   *
   * @return sfI18N The current sfI18N implementation instance.
   */
  public function getI18N()
  {
    if (!$this->i18n && sfConfig::get('sf_i18n'))
    {
      $this->i18n = sfI18N::getInstance();
      $this->i18n->initialize($this);
    }

    return $this->i18n;
  }

  /**
   * Retrieve the user.
   *
   * @return sfUser The current sfUser implementation instance.
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Registers a tone factory to the context.
   *
   * @param mixed  String (class name) or Object (instance) of a tone factory
   *               implementing the sfToneFactory interface.
   * @param string Tones of the factory will be accessable by this namespace.
   * @TODO make it more flexible to also support foreign but similar factories like Garden
   */
  public function registerToneFactory($factory, $namespace)
  {
    if (!empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is already a tone factory registered at namespace ' . $namespace);
    }

    if (is_string($factory))
    {
      $this->toneFactories[$namespace] = new $factory();
      $this->toneFactories[$namespace]->initialize();
    }
    elseif (is_object($factory))
    {
      $this->toneFactories[$namespace] = $factory;
    }

    if (!$this->toneFactories[$namespace] instanceof sfToneFactory)
    {
      throw new sfException('Registering tone factory for namespace ' . $namespace .
        ' failed. Make sure that it implements the sfToneFactory interface.');
    }
  }

  /**
   * Removes a factory and all its tones from context.
   *
   * @param string Namespace of the factory that will be removed.
   */
  public function unregisterToneFactory($namespace)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    $this->toneFactories[$namespace]->shutdown();
    unset($this->toneFactories[$namespace]);
    return true;
  }

  /**
   * Checks whether there is a tone factory registered in a given namespace.
   *
   * @param string Namespace to check.
   * @return bool  True if there is a tone factory, false if not.
   */
  public function hasToneFactory($namespace)
  {
    return !empty($this->toneFactories[$namespace]);
  }

  public function getTone($name, $namespace = self::DEFAULT_TONE_NAMESPACE)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    return $this->toneFactories[$namespace]->getTone($name);
  }

  public function containsTone($name, $namespace = self::DEFAULT_TONE_NAMESPACE)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    return $this->toneFactories[$namespace]->containsTone($name);
  }

  public function getAliases($name, $namespace = self::DEFAULT_TONE_NAMESPACE)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    return $this->toneFactories[$namespace]->getAliases($name);
  }

  /**
   * Tells if a tone is singleton
   *
   * @param     String $name tone id, name or alias
   * @return     boolean
   */
  public function isSingleton($name, $namespace = self::DEFAULT_TONE_NAMESPACE)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    return $this->toneFactories[$namespace]->isSingleton($name);
  }

  public function removeTone($name, $namespace = self::DEFAULT_TONE_NAMESPACE)
  {
    if (empty($this->toneFactories[$namespace]))
    {
      throw new sfException('There is no registered tone factory for namespace ' . $namespace);
    }

    return $this->toneFactories[$namespace]->removeTone($name);
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    // shutdown all factories
    foreach ($this->toneFactories as $toneFactory)
    {
      $toneFactory->shutdown();
    }

    $this->getUser()->shutdown();
    $this->getStorage()->shutdown();
    $this->getRequest()->shutdown();
    $this->getResponse()->shutdown();

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getLogger()->shutdown();
    }

    if (sfConfig::get('sf_use_database'))
    {
      $this->getDatabaseManager()->shutdown();
    }

    if (sfConfig::get('sf_cache'))
    {
      $this->getViewCacheManager()->shutdown();
    }
  }

}
