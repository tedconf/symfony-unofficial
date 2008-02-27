<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(sfConfig::get('sf_symfony_lib_dir').'/config/sfProjectConfiguration.class.php');
class ProjectConfiguration extends sfProjectConfiguration
{

}

class ApplicationConfiguration extends ProjectConfiguration
{

}

class sfContext
{
  protected static
    $instance = null;

  public
    $configuration = null,
    $request    = null,
    $response   = null,
    $controller = null,
    $routing    = null,
    $user       = null,
    $storage    = null,
    $i18n       = null,
    $cache      = null;

  protected
    $sessionPath = '';

  static public function getInstance($factories = array())
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();

      self::$instance->sessionPath = sfToolkit::getTmpDir().'/sessions_'.rand(11111, 99999);
      self::$instance->storage = new sfSessionTestStorage(array('session_path' => self::$instance->sessionPath));

      self::$instance->cache = new sfNoCache();
      self::$instance->configuration = new ApplicationConfiguration();
      self::$instance->dispatcher = self::$instance->configuration->getEventDispatcher();

      foreach ($factories as $type => $class)
      {
        self::$instance->inject($type, $class);
      }
    }

    return self::$instance;
  }

  public function __destruct()
  {
    sfToolkit::clearDirectory($this->sessionPath);
  }

  static public function hasInstance()
  {
    return true;
  }

  public function getEventDispatcher()
  {
    return self::$instance->dispatcher;
  }

  public function getModuleName()
  {
    return 'module';
  }

  public function getActionName()
  {
    return 'action';
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getResponse()
  {
    return $this->response;
  }

  public function getRouting()
  {
    return $this->routing;
  }

  public function getStorage()
  {
    return $this->storage;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function getI18n()
  {
    return $this->i18n;
  }

  public function getController()
  {
    return $this->controller;
  }

  public function getCache()
  {
    return $this->cache;
  }

  public function getConfiguration()
  {
    return $this->configuration;
  }

  public function getConfigCache()
  {
    return $this->configuration->getConfigCache();
  }

  public function inject($type, $class, $parameters = array())
  {
    switch ($type)
    {
      case 'routing':
        $object = new $class($this->dispatcher, null, $parameters);
        break;
      case 'response':
        $object = new $class($this->dispatcher, $parameters);
        break;
      case 'request':
        $object = new $class($this->dispatcher, $this->routing, $parameters);
        break;
      case 'user':
        $object = new $class($this->dispatcher, $this->storage, $parameters);
        break;
      case 'i18n':
        $object = new $class($this->configuration, $this->cache, $parameters);
        break;
      default:
        $object = new $class($this, $parameters);
    }

    $this->$type = $object;
  }
}
