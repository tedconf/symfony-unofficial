<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * Copyright (c) 2006 Yahoo! Inc.  All rights reserved.  
 * The copyrights embodied in the content in this file are licensed 
 * under the MIT open source license
 *
 * For the full copyright and license information, please view the LICENSE
 * and LICENSE.yahoo files that was distributed with this source code.
 */

/**
 * sfConfigHandler allows a developer to create a custom formatted configuration
 * file pertaining to any information they like and still have it auto-generate
 * PHP code.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id$
 */
abstract class sfConfigHandler
{
  protected
    $parameter_holder = null;

  /**
   * Add a set of replacement values.
   *
   * @param string The old value.
   * @param string The new value which will replace the old value.
   *
   * @return void
   */
  public function addReplacement($oldValue, $newValue)
  {
    $this->oldValues[] = $oldValue;
    $this->newValues[] = $newValue;
  }

  /**
   * returns the config files for the given config path.
   * Cache will check the 'files' key for a list of all
   * config files used by this handler.  The rest of $config
   * is free to be anything.
   * configCache will call us back with executeNew($config) if
   * the cache isn't up to date.
   */
  public function getConfig($configPath)
  {
    $config = array();
    $config['files'] = self::getConfigFiles($configPath);
    return $config;
  }

  public static function getConfigFiles($configPath)
  {
    $globalConfigPath = basename(dirname($configPath)).'/'.basename($configPath);
    $files = array(
      sfConfig::get('sf_symfony_data_dir').'/'.$globalConfigPath, // default symfony configuration
      sfConfig::get('sf_app_dir').'/'.$globalConfigPath,          // default project configuration
      sfConfig::get('sf_plugin_data_dir').'/'.$configPath,        // used for plugin modules
      sfConfig::get('sf_symfony_data_dir').'/'.$configPath,       // core modules or global plugins
      sfConfig::get('sf_root_dir').'/'.$globalConfigPath,         // used for main configuration
      sfConfig::get('sf_cache_dir').'/'.$configPath,              // used for generated modules
      sfConfig::get('sf_app_dir').'/'.$configPath,
    );

    return array_filter($files, 'is_readable');
  }

  /**
   * Executes this configuration handler.  By default, calls
   * the original execute() method.  Each ConfigHandler should
   * override either executeConfig or execute.
   * This could be phased in as the regular execute method
   * if we rewrite the various ConfigHandlers to understand
   * this arg.
   */
  public function executeConfig($config)
  {
    return $this->execute($config['files']);
  }

  /**
   * Execute this configuration handler.
   *
   * @param array An array of filesystem path to a configuration file.
   *
   * @return string Data to be written to a cache file.
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file
   *                                       does not exist or is not readable.
   * @throws <b>sfParseException</b> If a requested configuration file is
   *                               improperly formatted.
   */
  public function execute($configFiles)
  {
    return '';
  }

  /**
   * Initialize this ConfigHandler.
   *
   * @param array An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this ConfigHandler.
   */
  public function initialize($parameters = null)
  {
    $this->getParameterHolder()->add($parameters);
  }

  public function __construct()
  {
    $this->parameter_holder = new sfParameterHolder();
  }

  /**
   * Literalize a string value.
   *
   * @param string The value to literalize.
   *
   * @return string A literalized value.
   */
  public static function literalize($value)
  {
    static
      $keys = array("\\", "%'", "'"),
      $reps = array("\\\\", "\"", "\\'");

    if ($value == null)
    {
      // null value
      return 'null';
    }

    // lowercase our value for comparison
    $value  = trim($value);
    $lvalue = strtolower($value);

    if ($lvalue == 'on' || $lvalue == 'yes' || $lvalue == 'true')
    {
      // replace values 'on' and 'yes' with a boolean true value
      return 'true';
    }
    else if ($lvalue == 'off' || $lvalue == 'no' || $lvalue == 'false')
    {
      // replace values 'off' and 'no' with a boolean false value
      return 'false';
    }
    else if (!is_numeric($value))
    {
      $value = str_replace($keys, $reps, $value);

      return "'".$value."'";
    }

    // numeric value
    return $value;
  }

  /**
   * Replace constant identifiers in a value.
   *
   * If the value is an array replacements are made recursively.
   * 
   * @param mixed The value on which to run the replacement procedure.
   *
   * @return string The new value.
   */
  public static function replaceConstants($value)
  {
    if (is_array($value))
    {
      array_walk_recursive($value, array('self', 'replaceConstantsCallback'));
    }
    else
    {
      self::replaceConstantsCallback($value);
    }

    return $value;
  }

  /**
   * Replaces constant identifiers in a scalar value.
   *
   * This is used by the {@link replaceConstants}.
   *
   * @param string the value to perform the replacement on
   * @return string the value with substitutions made
   */
  private static function replaceConstantsCallback(&$value)
  {
    $value = preg_replace('/%(.+?)%/e', 'sfConfig::get(strtolower("\\1"))', $value);
  }

  /**
   * Replace a relative filesystem path with an absolute one.
   *
   * @param string A relative filesystem path.
   *
   * @return string The new path.
   */
  public static function replacePath($path)
  {
    if (!sfToolkit::isPathAbsolute($path))
    {
      // not an absolute path so we'll prepend to it
      $path = sfConfig::get('sf_app_dir').'/'.$path;
    }

    return $path;
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }
}
