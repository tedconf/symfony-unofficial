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
 * sfConfigCache allows you to customize the format of a configuration file to
 * make it easy-to-use, yet still provide a PHP formatted result for direct
 * inclusion into your modules.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfConfigCache
{
  protected
    $handlers = array(),
    $userHandlers = array();

  protected static
    $instance = null;

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfConfigCache A sfConfigCache instance
   */
  public static function getInstance()
  {
    if (!self::$instance)
    {
      self::$instance = new sfConfigCache();
    }

    return self::$instance;
  }

  /**
   * Loads a configuration handler.
   *
   * @param string The handler to use when parsing a configuration file
   * @param array  An array of absolute filesystem paths to configuration files
   * @param string An absolute filesystem path to the cache file that will be written
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file does not have an associated configuration handler
   */
  protected function callHandler($handler, $configs, $cache)
  {
    if (count($this->handlers) == 0)
    {
      // we need to load the handlers first
      $this->loadConfigHandlers();
    }

    if (count($this->userHandlers) != 0)
    {
      // we load user defined handlers
      $this->mergeUserConfigHandlers();
    }

    // handler key to call for this configuration file
    $handlerKey = null;

    $handler = str_replace(DIRECTORY_SEPARATOR, '/', $handler);

    // grab the base name of the handler
    $basename = basename($handler);
    if (isset($this->handlers[$handler]))
    {
      // we have a handler associated with the full configuration path
      $handlerKey = $handler;
    }
    else if (isset($this->handlers[$basename]))
    {
      // we have a handler associated with the configuration base name
      $handlerKey = $basename;
    }
    else
    {
      // let's see if we have any wildcard handlers registered that match this basename
      foreach (array_keys($this->handlers) as $key)
      {
        // replace wildcard chars in the configuration
        $pattern = strtr($key, array('.' => '\.', '*' => '.*?'));

        // create pattern from config
        if (preg_match('#'.$pattern.'$#', $handler))
        {
          $handlerKey = $key;

          break;
        }
      }
    }

    if (!$handlerKey)
    {
      // we do not have a registered handler for this file
      throw new sfConfigurationException(sprintf('Configuration file "%s" does not have a registered handler.', implode(', ', $configs)));
    }

    // call the handler and retrieve the cache data
    $data = $this->getHandler($handlerKey)->execute($configs);

    $this->writeCacheFile($handler, $cache, $data);
  }

  /**
   * Returns the config handler configured for the given name
   *
   * @param  string The config handler name
   *
   * @return sfConfigHandler A sfConfigHandler instance
   */
  protected function getHandler($name)
  {
    if (is_array($this->handlers[$name]))
    {
      $class = $this->handlers[$name][0];
      $this->handlers[$name] = new $class($this->handlers[$name][1]);
    }

    return $this->handlers[$name];
  }

  /**
   * Checks to see if a configuration file has been modified and if so
   * recompile the cache file associated with it.
   *
   * The recompilation only occurs in a non debug environment.
   *
   * If the configuration file path is relative, symfony will look in directories
   * defined in the sfLoader::getConfigPaths() method.
   *
   * @param string A filesystem path to a configuration file
   *
   * @return string An absolute filesystem path to the cache filename associated with this specified configuration file
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist
   *
   * @see sfLoader::getConfigPaths()
   */
  public function checkConfig($configPath, $optional = false)
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer('Configuration');
    }

    // the cache filename we'll be using
    $cache = $this->getCacheName($configPath);

    if (!sfConfig::get('sf_debug') && !sfConfig::get('sf_test') && is_readable($cache))
    {
      return $cache;
    }

    if (!sfToolkit::isPathAbsolute($configPath))
    {
      $files = sfLoader::getConfigPaths($configPath);
    }
    else
    {
      $files = is_readable($configPath) ? array($configPath) : array();
    }

    if (!isset($files[0]))
    {
      if ($optional)
      {
        return null;
      }

      // configuration does not exist
      throw new sfConfigurationException(sprintf('Configuration "%s" does not exist or is unreadable.', $configPath));
    }

    // find the more recent configuration file last modification time
    $mtime = 0;
    foreach ($files as $file)
    {
      if (filemtime($file) > $mtime)
      {
        $mtime = filemtime($file);
      }
    }

    if (!is_readable($cache) || $mtime > filemtime($cache))
    {
      // configuration has changed so we need to reparse it
      $this->callHandler($configPath, $files, $cache);
    }

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $cache;
  }

  /**
   * Clears all configuration cache files.
   */
  public function clear()
  {
    sfToolkit::clearDirectory(sfConfig::get('sf_config_cache_dir'));
  }

  /**
   * Converts a normal filename into a cache filename.
   *
   * @param string A normal filename
   *
   * @return string An absolute filesystem path to a cache filename
   */
  public function getCacheName($config)
  {
    if (strlen($config) > 3 && ctype_alpha($config[0]) && $config[1] == ':' && ($config[2] == '\\' || $config[2] == '/'))
    {
      // file is a windows absolute path, strip off the drive letter
      $config = substr($config, 3);
    }

    // replace unfriendly filename characters with an underscore
    $config  = str_replace(array('\\', '/', ' '), '_', $config);
    $config .= '.php';

    return sfConfig::get('sf_config_cache_dir', sfToolkit::getTmpDir()).'/'.$config;
  }

  /**
   * Imports a configuration file.
   *
   * @param string A filesystem path to a configuration file
   * @param bool   Only allow this configuration file to be included once per request?
   *
   * @see checkConfig()
   */
  public function import($config, $once = true, $optional = false)
  {
    $cache = $this->checkConfig($config, $optional);

    if ($optional && !$cache)
    {
      return;
    }

    // include cache file
    if ($once)
    {
      include_once($cache);
    }
    else
    {
      include($cache);
    }
  }

  /**
   * Loads all configuration application and module level handlers.
   *
   * @throws <b>sfConfigurationException</b> If a configuration related error occurs.
   */
  protected function loadConfigHandlers()
  {
    // manually create our config_handlers.yml handler
    $this->handlers['config_handlers.yml'] = new sfRootConfigHandler();

    // application configuration handlers

    require_once($this->checkConfig(sfConfig::get('sf_app_config_dir_name').'/config_handlers.yml'));

    // module level configuration handlers

    // checks modules directory exists
    if (!is_readable($sf_app_modules_dir = sfConfig::get('sf_app_modules_dir')))
    {
      return;
    }

    // ignore names
    $ignore = array('.', '..', 'CVS', '.svn');

    // create a file pointer to the module dir
    $fp = opendir($sf_app_modules_dir);

    // loop through the directory and grab the modules
    while (($directory = readdir($fp)) !== false)
    {
      if (in_array($directory, $ignore))
      {
        continue;
      }

      $configPath = $sf_app_modules_dir.'/'.$directory.'/'.sfConfig::get('sf_app_module_config_dir_name').'/config_handlers.yml';

      if (is_readable($configPath))
      {
        // initialize the root configuration handler with this module name
        $params = array('module_level' => true, 'module_name' => $directory);

        $this->handlers['config_handlers.yml']->initialize($params);

        // replace module dir path with a special keyword that
        // checkConfig knows how to use
        $configPath = sfConfig::get('sf_app_modules_dir_name').'/'.$directory.'/'.sfConfig::get('sf_app_module_config_dir_name').'/config_handlers.yml';

        require_once($this->checkConfig($configPath));
      }
    }

    // close file pointer
    fclose($fp);
  }

  /**
   * Writes a cache file.
   *
   * @param string An absolute filesystem path to a configuration file
   * @param string An absolute filesystem path to the cache file that will be written
   * @param string Data to be written to the cache file
   *
   * @throws sfCacheException If the cache file cannot be written
   */
  protected function writeCacheFile($config, $cache, $data)
  {
    $current_umask = umask();
    umask(0000);

    if (!is_dir(dirname($cache)))
    {
      // create directory structure if needed
      @mkdir(dirname($cache), 0777, true);
    }

    if (!$fp = fopen($cache, 'wb'))
    {
      throw new sfCacheException(sprintf('Unable to write cache file "%s".', $cache));
    }

    @flock($fp, LOCK_EX);

    if (false === @fwrite($fp, $data))
    {
      throw new sfCacheException(sprintf('Failed to write cache file "%s" generated from configuration file "%s".', $cache, $config));
    }

    @flock($fp, LOCK_UN);
    @fclose($fp);

    // change file mode
    chmod($cache, 0666);

    umask($current_umask);
  }

  /**
   * Registers a configuration handler.
   *
   * @param string The handler to use when parsing a configuration file
   * @param class  A configuration handler class
   * @param string An array of options for the handler class initialization
   */
  public function registerConfigHandler($handler, $class, $params = array())
  {
    $this->userHandlers[$handler] = new $class($params);
  }

  /**
   * Merges configuration handlers from the config_handlers.yml
   * and the ones defined with registerConfigHandler()
   *
   */
  protected function mergeUserConfigHandlers()
  {
    // user defined configuration handlers
    $this->handlers = array_merge($this->handlers, $this->userHandlers);

    $this->userHandlers = array();
  }
}
