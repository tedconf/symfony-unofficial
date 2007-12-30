<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAutoload class.
 *
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAutoload
{
  static protected
    $instance = null;

  protected
    $overriden = array(),
    $classes = array();

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfAutoload A sfAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfAutoload();
    }

    return self::$instance;
  }

  /**
   * Register sfAutoload in spl autoloader.
   *
   * @return void
   */
  public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');

    spl_autoload_register(array(self::$instance, 'autoload'));
  }

  /**
   * Unregister sfAutoload from spl autoloader.
   *
   * @return void
   */
  public function unregister()
  {
    spl_autoload_unregister(array(self::$instance, 'autoload'));
  }

  /**
   * Sets path to class.
   *
   * @param  string  A class name.
   * @param  string  Path to class.
   *
   * @return void
   */
  public function setClassPath($class, $path)
  {
    self::$instance->overriden[$class] = $path;

    self::$instance->classes[$class] = $path;
  }

  /**
   * Get path to class.
   *
   * @param  string  A class name.
   *
   * @return void
   */
  public function getClassPath($class)
  {
    return isset(self::$instance->classes[$class]) ? self::$instance->classes[$class] : null;
  }

  /**
   * Reloads all registered classes.
   *
   * @param  boolean Force delete of autoload cache?
   *
   * @return void
   */
  static public function reloadClasses($force = false)
  {
    if ($force)
    {
      @unlink(sfConfigCache::getInstance()->getCacheName(sfConfig::get('sf_app_config_dir_name').'/autoload.yml'));
    }

    $file = sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/autoload.yml');

    self::$instance->classes = include($file);

    foreach (self::$instance->overriden as $class => $path)
    {
      self::$instance->classes[$class] = $path;
    }
  }

  /**
   * Handles autoloading of classes that have been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function autoload($class)
  {
    // load the list of autoload classes
    if (!self::$instance->classes)
    {
      self::reloadClasses();
    }

    if (self::loadClass($class))
    {
      return true;
    }

    return false;
  }

  /**
   * Reloads a class.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function autoloadAgain($class)
  {
    self::reloadClasses(true);

    return self::loadClass($class);
  }

  /**
   * Tries to load a class that has been specified in autoload.yml.
   *
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function loadClass($class)
  {
    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path, let's include it
    if (isset(self::$instance->classes[$class]))
    {
      require(self::$instance->classes[$class]);

      return true;
    }

    // see if the file exists in the current module lib directory
    // must be in a module context
    if (sfContext::hasInstance() && ($module = sfContext::getInstance()->getModuleName()) && isset(self::$instance->classes[$module.'/'.$class]))
    {
      require(self::$instance->classes[$module.'/'.$class]);

      return true;
    }

    return false;
  }
}

