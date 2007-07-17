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
 * sfController directs application flow.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @version    SVN: $Id$
 */
abstract class sfController
{
  const
    ERROR_NOT_FOUND = -1,
    ERROR_IS_INTERNAL = -2,
    ERROR_NOT_ENABLED = -3;

  protected
    $context                  = null,
    $controllerClasses        = array(),
    $maxForwards              = 5,
    $renderMode               = sfView::RENDER_CLIENT,
    //$viewCacheClassName       = null,
    $stackCounter             = 1,
    $stack                    = null,
    $entries                  = array();

  /**
   * Retrieves a new sfController implementation instance.
   *
   * @param string A sfController class name
   *
   * @return sfController A sfController implementation instance
   *
   * @throws sfFactoryException If a new controller implementation instance cannot be created
   */
  public static function newInstance($class)
  {
    try
    {
      $object = new $class();

      if (!$object instanceof sfController)
      {
        throw new sfFactoryException(sprintf('Class "%s" is not of the type "sfController".', $class));
      }

      return $object;
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
  }

  /**
   * Initializes this controller.
   *
   * @param sfContext A sfContext implementation instance
   */
  public function initialize($context)
  {
    $this->context = $context;

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getLogger()->info('{sfController} initialization');
    }

    // set max forwards
    $this->maxForwards = sfConfig::get('sf_max_forwards');

    $this->stack = new sfParameterHolder($this->getNextEntryName());
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext A sfContext instance
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Indicates whether or not a module has a specific component.
   *
   * @param string A module name
   * @param string An component name
   *
   * @return bool true, if the component exists, otherwise false
   */
  public function componentExists($moduleName, $componentName)
  {
    return $this->controllerExists($moduleName, $componentName, 'component', false);
  }

  /**
   * Indicates whether or not a module has a specific action.
   *
   * @param string A module name
   * @param string An action name
   *
   * @return bool true, if the action exists, otherwise false
   */
  public function actionExists($moduleName, $actionName)
  {
    return $this->controllerExists($moduleName, $actionName, 'action', false);
  }

  /**
   * Looks for a controller and optionally throw exceptions if existence is required (i.e.
   * in the case of {@link getController()}).
   *
   * @param string  The name of the module
   * @param string  The name of the controller within the module
   * @param string  Either 'action' or 'component' depending on the type of controller to look for
   * @param boolean Whether to throw exceptions if the controller doesn't exist
   *
   * @throws sfConfigurationException thrown if the module is not enabled
   * @throws sfControllerException thrown if the controller doesn't exist and the $throwExceptions parameter is set to true
   *
   * @return boolean true if the controller exists, false otherwise
   */
  protected function controllerExists($moduleName, $controllerName, $extension, $throwExceptions)
  {
    $dirs = sfLoader::getControllerDirs($moduleName);
    foreach ($dirs as $dir => $checkEnabled)
    {
      // plugin module enabled?
      if ($checkEnabled && !in_array($moduleName, sfConfig::get('sf_enabled_modules')) && is_readable($dir))
      {
        throw new sfConfigurationException(sprintf('The module "%s" is not enabled.', $moduleName));
      }

      // one action per file or one file for all actions
      $classFile   = strtolower($extension);
      $classSuffix = ucfirst(strtolower($extension));
      $file        = $dir.'/'.$controllerName.$classSuffix.'.class.php';
      if (is_readable($file))
      {
        // action class exists
        require_once($file);

        $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $controllerName.$classSuffix;

        return true;
      }

      $module_file = $dir.'/'.$classFile.'s.class.php';
      if (is_readable($module_file))
      {
        // module class exists
        require_once($module_file);

        if (!class_exists($moduleName.$classSuffix.'s', false))
        {
          if ($throwExceptions)
          {
            throw new sfControllerException(sprintf('There is no "%s" class in your action file "%s".', $moduleName.$classSuffix.'s', $module_file));
          }

          return false;
        }

        // action is defined in this class?
        if (!in_array('execute'.ucfirst($controllerName), get_class_methods($moduleName.$classSuffix.'s')))
        {
          if ($throwExceptions)
          {
            throw new sfControllerException(sprintf('There is no "%s" method in your action class "%s".', 'execute'.ucfirst($controllerName), $moduleName.$classSuffix.'s'));
          }

          return false;
        }

        $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $moduleName.$classSuffix.'s';
        return true;
      }
    }

    // send an exception if debug
    if ($throwExceptions && sfConfig::get('sf_debug'))
    {
      $dirs = array_keys($dirs);

      // remove sf_root_dir from dirs
      foreach ($dirs as &$dir)
      {
        $dir = str_replace(sfConfig::get('sf_root_dir'), '%SF_ROOT_DIR%', $dir);
      }

      throw new sfControllerException(sprintf('{sfController} controller "%s/%s" does not exist in: %s.', $moduleName, $controllerName, implode(', ', $dirs)));
    }

    return false;
  }

  /**
   * Creates and initializes an action instance and performs several checks.
   *
   * @param string  Module name.
   * @param string  Action name.
   * @param array   Optional parameters that are passed to the action instance.
   * @param boolean If false, an error code is returned on errors, otherwise exceptions are thrown.
   * @return mixed  Either an action instance or an error code on errors.
   */
  public function makeAction($moduleName, $actionName, $vars = array(), $throwExceptions = true)
  {
    if (!$this->actionExists($moduleName, $actionName))
    {
      // the action doesn't exist
      $error = sprintf('{sfController} action "%s" does not exist', $moduleName.'/'.$actionName);

      if (sfConfig::get('sf_logging_enabled'))
      {
        $this->context->getLogger()->info($error);
      }

      if ($throwExceptions)
      {
        throw new sfConfigurationException($error);
      }
      return self::ERROR_NOT_FOUND;
    }

    // check for a module generator config file
    sfConfigCache::getInstance()->import(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/generator.yml', true, true);

    // include module configuration
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/module.yml'));

    // check if this module is internal
    if ($this->count() == 1 && sfConfig::get('mod_'.strtolower($moduleName).'_is_internal') && !sfConfig::get('sf_test'))
    {
      if ($throwExceptions)
      {
        throw new sfConfigurationException(sprintf('Action "%s" from module "%s" cannot be called directly.', $actionName, $moduleName));
      }
      return self::ERROR_IS_INTERNAL;
    }

    if (!sfConfig::get('mod_'.strtolower($moduleName).'_enabled'))
    {
      if ($throwExceptions)
      {
        throw new sfConfigurationException(sprintf('Action "%s" from module "%s" is not enabled.', $actionName, $moduleName));
      }
      return self::ERROR_NOT_ENABLED;
    }

    // create an instance of the action
    $actionInstance = $this->getAction($moduleName, $actionName);

    // check for a module config.php
    $moduleConfig = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/config.php';
    if (is_readable($moduleConfig))
    {
      require_once($moduleConfig);
    }

    if (!$actionInstance->initialize($this->context))
    {
      // action failed to initialize
      throw new sfInitializationException(sprintf('Action initialization failed for module "%s", action "%s".', $moduleName, $actionName));
    }

    if (is_array($vars) && !empty($vars))
    {
      $actionInstance->getVarHolder()->add($vars);
    }

    if ($moduleName == sfConfig::get('sf_error_404_module') && $actionName == sfConfig::get('sf_error_404_action'))
    {
      $this->context->getResponse()->setStatusCode(404);
      $this->context->getResponse()->setHttpHeader('Status', '404 Not Found');

      foreach (sfMixer::getCallables('sfController:forward:error404') as $callable)
      {
        call_user_func($callable, $this, $moduleName, $actionName);
      }
    }

    return $actionInstance;
  }

  /**
   * Returns the instance for the 404 error action.
   *
   * Given parameters will be replaced by the actual parameters of this action.
   *
   * @param string Requested module name.
   * @param string Requested action name.
   */
  public function make404Action(&$moduleName, &$actionName)
  {
    // track the requested module so we have access to the data in the error 404 page
    $this->context->getRequest()->setAttribute('requested_action', $moduleName);
    $this->context->getRequest()->setAttribute('requested_module', $actionName);

    // switch to error 404 action
    $moduleName = sfConfig::get('sf_error_404_module');
    $actionName = sfConfig::get('sf_error_404_action');

    if (!$this->actionExists($moduleName, $actionName))
    {
      // cannot find unavailable module/action
      throw new sfConfigurationException(sprintf('Invalid configuration settings: [sf_error_404_module] "%s", [sf_error_404_action] "%s".', $moduleName, $actionName));
    }

    return $this->makeAction($moduleName, $actionName);
  }

  /**
   * Returns the instance for the 'module not enabled' action.
   *
   * Given parameters will be replaced by the actual parameters of this action.
   *
   * @param string Requested module name.
   * @param string Requested action name.
   */
  public function makeNotEnabledAction(&$moduleName, &$actionName)
  {
    // track the requested module so we have access to the data in the error 404 page
    $this->context->getRequest()->setAttribute('requested_action', $moduleName);
    $this->context->getRequest()->setAttribute('requested_module', $actionName);

    // module is disabled
    $moduleName = sfConfig::get('sf_module_disabled_module');
    $actionName = sfConfig::get('sf_module_disabled_action');

    if (!$this->actionExists($moduleName, $actionName))
    {
      // cannot find mod disabled module/action
      throw new sfConfigurationException(sprintf('Invalid configuration settings: [sf_module_disabled_module] "%s", [sf_module_disabled_action] "%s".', $moduleName, $actionName));
    }

    return $this->makeAction($moduleName, $actionName);
  }

  /**
   * Checks whether further forwarding is possible.
   *
   * @return True if forwarding is possible, false if not.
   */
  public function forwardAllowed()
  {
    return count($this->entries) < $this->maxForwards;
  }

  /**
   * Forwards the request to either another action or page.
   *
   * The parameters allow different signatures. The two with $pageId are only supported with sfPageController controllers:
   * <code>
   * forward($moduleName, $actionName, $options)
   * forward($moduleName, $actionName)
   * forward($pageId, $options)
   * forward($pageId)
   * </code>
   * @param string  A module name or a page id
   * @param string  An action name or options
   * @param array   Optional an array of options.
   *
   * @throws <b>sfConfigurationException</b> If an invalid configuration setting has been found
   * @throws <b>sfForwardException</b> If an error occurs while forwarding the request
   * @throws <b>sfInitializationException</b> If the action could not be initialized
   * @throws <b>sfSecurityException</b> If the action requires security but the user implementation is not of type sfSecurityUser
   */
  public function forward($moduleName, $actionName = null, $parameters = array())
  {
    if (is_array($actionName))
    {
      $parameters = $actionName;
    }

    if (!is_string($actionName))
    {
      $actionName = 'index';
    }

    // replace unwanted characters and add to parameters
    $parameters['module_name'] = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);
    $parameters['action_name'] = preg_replace('/[^a-z0-9\-_]+/i', '', $actionName);

    $this->addEntry($parameters);
  }

  /**
   * Builds the response.
   *
   * This method is called by dispatch().
   */
  protected function execute()
  {
    // create a new filter chain
    $filterChain = new sfFilterChain();

    require sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/filters.yml');

    // hook before execution starts
    if ($callable = sfMixer::getCallable('sfController:execute:pre'))
    {
      call_user_func($callable, $this, $filterChain);
    }

    // process the filter chain
    $filterChain->execute();

    // hook after execution
    if ($callable = sfMixer::getCallable('sfController:execute:post'))
    {
      call_user_func($callable, $this, $filterChain);
    }
  }

  /**
   * Retrieves an sfAction implementation instance.
   *
   * @param  string A module name
   * @param  string An action name
   *
   * @return sfAction An sfAction implementation instance, if the action exists, otherwise null
   */
  public function getAction($moduleName, $actionName)
  {
    return $this->getController($moduleName, $actionName, 'action');
  }

  /**
   * Retrieves a sfComponent implementation instance.
   *
   * @param  string A module name
   * @param  string A component name
   *
   * @return sfComponent A sfComponent implementation instance, if the component exists, otherwise null
   */
  public function getComponent($moduleName, $componentName)
  {
    return $this->getController($moduleName, $componentName, 'component');
  }

  /**
   * Retrieves a controller implementation instance.
   *
   * @param  string A module name
   * @param  string A component name
   * @param  string  Either 'action' or 'component' depending on the type of controller to look for
   *
   * @return object A controller implementation instance, if the controller exists, otherwise null
   *
   * @see getComponent(), getAction()
   */
  protected function getController($moduleName, $controllerName, $extension)
  {
    $classSuffix = ucfirst(strtolower($extension));
    if (!isset($this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix]))
    {
      $this->controllerExists($moduleName, $controllerName, $extension, true);
    }

    $class = $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix];

    // fix for same name classes
    $moduleClass = $moduleName.'_'.$class;

    if (class_exists($moduleClass, false))
    {
      $class = $moduleClass;
    }

    return new $class();
  }

  /**
   * Retrieves the presentation rendering mode.
   *
   * @return int One of the following:
   *             - sfView::RENDER_CLIENT
   *             - sfView::RENDER_VAR
   */
  public function getRenderMode()
  {
    return $this->renderMode;
  }

  /**
   * Sets the presentation rendering mode.
   *
   * @param int A rendering mode
   *
   * @throws sfRenderException If an invalid render mode has been set
   */
  public function setRenderMode($mode)
  {
    if ($mode == sfView::RENDER_CLIENT || $mode == sfView::RENDER_VAR || $mode == sfView::RENDER_NONE)
    {
      $this->renderMode = $mode;

      return;
    }

    // invalid rendering mode type
    throw new sfRenderException(sprintf('Invalid rendering mode: %s.', $mode));
  }

  /**
   * Retrieves a sfView implementation instance.
   *
   * @param string A module name
   * @param string An action name
   * @param string A view name
   *
   * @return sfView A sfView implementation instance, if the view exists, otherwise null
   */
  public function getView($moduleName, $actionName, $viewName)
  {
    // user view exists?
    $file = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_view_dir_name').'/'.$actionName.$viewName.'View.class.php';

    if (is_readable($file))
    {
      require_once($file);

      $class = $actionName.$viewName.'View';

      // fix for same name classes
      $moduleClass = $moduleName.'_'.$class;

      if (class_exists($moduleClass, false))
      {
        $class = $moduleClass;
      }
    }
    else
    {
      // view class (as configured in module.yml or defined in action)
      $viewName = $this->context->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', sfConfig::get('mod_'.strtolower($moduleName).'_view_class'), 'symfony/action/view');
      $class    = sfAutoload::getClassPath($viewName.'View') ? $viewName.'View' : 'sfPHPView';
    }

    return new $class();
  }

  /**
   * Indicates whether or not we were called using the CLI version of PHP.
   *
   * @return bool true, if using cli, otherwise false.
   */
  public function inCLI()
  {
    return 0 == strncasecmp(PHP_SAPI, 'cli', 3);
  }

  /**
   * Sends and email from the current action.
   *
   * This methods calls a module/action with the sfMailView class.
   *
   * @param  string A module name
   * @param  string An action name
   *
   * @return string The generated mail content
   *
   * @see sfMailView, getPresentationFor(), sfController
   */
  public function sendEmail($module, $action)
  {
    return $this->getPresentationFor($module, $action, 'sfMail');
  }

  /**
   * Returns the rendered view presentation of a given module/action.
   *
   * @param  string A module name
   * @param  string An action name
   * @param  string A View class name
   *
   * @return string The generated content
   */
  # NOT TESTED !!!
  public function getPresentationFor($module, $action, $viewName = null)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getLogger()->info('{sfController} get presentation for action "'.$module.'/'.$action.'" (view class: "'.$viewName.'")');
    }

    // get original render mode
    $renderMode = $this->getRenderMode();

    // set render mode to var
    $this->setRenderMode(sfView::RENDER_VAR);

    // set viewName if needed
    if ($viewName)
    {
      $this->context->getRequest()->setAttribute($module.'_'.$action.'_view_name', $viewName, 'symfony/action/view');
    }

    $parameters = array();
    $parameters['module_name'] = preg_replace('/[^a-z0-9\-_]+/i', '', $moduleName);
    $parameters['action_name'] = preg_replace('/[^a-z0-9\-_]+/i', '', $actionName);
    $parameters['view_name'] = $viewName;

    $this->addEntry($parameters);

    $presentation = $this->getPresentation();

    $this->removeEntry();

    // put render mode back
    $this->setRenderMode($renderMode);

    // remove viewName
    if ($viewName)
    {
      $this->context->getRequest()->getAttributeHolder()->remove($module.'_'.$action.'_view_name', 'symfony/action/view');
    }

    return $presentation;
  }

  /**
   * Retrieve the current module name.
   *
   * @return string The currently executing module name, if one is set,
   *                otherwise null.
   */
  public function getModuleName()
  {
    return $this->stack->get('module_name');
  }

  /**
   * Retrieve the current action name.
   *
   * @return string The currently executing action name, if one is set,
   *                otherwise null.
   */
  public function getActionName()
  {
    return $this->stack->get('action_name');
  }

  /**
   * Retrieves the current view instance.
   *
   * @return sfView A sfView implementation instance.
   */
  public function getViewInstance()
  {
    return $this->stack->get('view_instance');
  }

  /**
   * Sets the current view instance.
   *
   * @param sfView A sfView implementation instance.
   */
  public function setViewInstance($viewInstance)
  {
    $this->stack->set('view_instance', $viewInstance);
  }

  /**
   * Retrieves the current rendered view presentation.
   *
   * This will only exist if the view has processed and the render mode is set to sfView::RENDER_VAR.
   *
   * @return string Rendered view presentation
   */
  public function getPresentation()
  {
    return $this->stack->get('presentation');
  }

  /**
   * Sets the rendered presentation of the current action.
   *
   * @param string A rendered presentation.
   */
  public function setPresentation($content)
  {
    $this->stack->set('presentation', $content);
  }

  /**
   * Returns variables for current action.
   *
   * @EXPERIMENTAL
   *
   * @return array Variables for current action.
   */
  public function getVars()
  {
    $vars = array();

    return $vars;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getStackHolder()->get()</code>
   *
   * @param string The key name
   * @param string The default value
   * @param string The namespace to use
   *
   * @return string The value associated with the key
   *
   * @see sfParameterHolder
   */
  public function get($name, $default = null, $ns = null)
  {
    return $this->stack->get($name, $default, $ns);
  }

  /**
   * Returns true if the given key exists in the parameter holder.
   *
   * This is a shortcut for:
   *
   * <code>$this->getStackHolder()->has()</code>
   *
   * @param string The key name
   * @param string The namespace to use
   *
   * @return boolean true if the given key exists, false otherwise
   *
   * @see sfParameterHolder
   */
  public function has($name, $ns = null)
  {
    return $this->stack->has($name, $ns);
  }

  /**
   * Sets the value for the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getStackHolder()->set()</code>
   *
   * @param string The key name
   * @param string The value
   * @param string The namespace to use
   *
   * @see sfParameterHolder
   */
  public function set($name, $value, $ns = null)
  {
    return $this->stack->set($name, $value, $ns);
  }

  /**
   * Returns the number of entries added to the stack.
   *
   * @return integer Number of entries added to the stack.
   */
  public function count()
  {
    return count($this->entries);
  }

  /**
   * Gets the stack holder.
   *
   * @return sfParameterHolder A sfParameterHolder instance
   */
  public function getStackHolder()
  {
    return $this->stack;
  }

  /**
   * Opens a new entry and adds it to the stack.
   *
   * An entry is the internal representation of the current request within the controller.
   */
  protected function addEntry($parameters = array())
  {
    if (!$this->forwardAllowed())
    {
      // let's kill this party before it turns into cpu cycle hell
      throw new sfForwardException(sprintf('Too many forwards have been detected for this request (> %d).', $this->maxForwards));
    }

    $name = $this->getNextEntryName();

    $this->entries[] = $name;
    $this->setCurrentEntryName($name);

    if (is_array($parameters) && !empty($parameters))
    {
      $this->stack->add($parameters);
    }
  }

  /**
   * Removes current entry from stack and returns to that entry being added before.
   */
  protected function removeEntry()
  {
    $current = $this->stack->getDefaultNamespace();
    $closed = array_pop($this->entries);

    if ($current !== $closed)
    {
      throw new sfException('Current entry and default namespace of the stack parameter holder do not match.');
    }

    $this->stack->removeNamespace($current);

    if (count($this->entries))
    {
      $name = end($this->entries);
    }
    else
    {
      $name = 1;
    }

    $this->setCurrentEntryName($name);

    return $closed;
  }

  protected function getNextEntryName()
  {
    return $this->stackCounter++;
  }

  protected function getCurrentEntryName()
  {
    return $this->stack->getDefaultNamespace();
  }

  protected function setCurrentEntryName($name)
  {
    $this->stack->setDefaultNamespace($name, false);
  }

  /**
   * Calls methods defined via the sfMixer class.
   *
   * @param string The method name
   * @param array  The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @see sfMixer
   */
  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfController:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfController::%s.', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
