<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProjectConfiguration represents a configuration for a symfony project.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectConfiguration
{
  protected
    $rootDir       = null,
    $symfonyLibDir = null,
    $plugins       = array('sfPropelPlugin'),
    $pluginPaths   = array();

  static protected
    $active = null;

  /**
   * Constructor.
   *
   * @param string              $rootDir    The project root directory
   * @param sfEventDispatcher   $dispatcher The event dispatcher
   */
  public function __construct($rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    if (is_null(sfProjectConfiguration::$active) || $this instanceof sfApplicationConfiguration)
    {
      sfProjectConfiguration::$active = $this;
    }

    $this->rootDir = is_null($rootDir) ? self::guessRootDir() : realpath($rootDir);

    $this->symfonyLibDir = realpath(dirname(__FILE__).'/..');

    // initializes autoloading for symfony core classes
    require_once $this->symfonyLibDir.'/autoload/sfCoreAutoload.class.php';
    sfCoreAutoload::register();

    $this->dispatcher = is_null($dispatcher) ? new sfEventDispatcher() : $dispatcher;

    ini_set('magic_quotes_runtime', 'off');
    ini_set('register_globals', 'off');

    sfConfig::set('sf_symfony_lib_dir', $this->symfonyLibDir);

    $this->setRootDir($this->rootDir);

    $this->setup();
  }

  /**
   * Setups the current configuration.
   *
   * Override this method if you want to customize your project configuration.
   */
  public function setup()
  {
  }

  /**
   * Sets the project root directory.
   *
   * @param string $rootDir The project root directory
   */
  public function setRootDir($rootDir)
  {
    $this->rootDir = $rootDir;

    sfConfig::add(array(
      'sf_root_dir' => $rootDir,

      // global directory structure
      'sf_apps_dir'    => $rootDir.DIRECTORY_SEPARATOR.'apps',
      'sf_lib_dir'     => $rootDir.DIRECTORY_SEPARATOR.'lib',
      'sf_log_dir'     => $rootDir.DIRECTORY_SEPARATOR.'log',
      'sf_data_dir'    => $rootDir.DIRECTORY_SEPARATOR.'data',
      'sf_config_dir'  => $rootDir.DIRECTORY_SEPARATOR.'config',
      'sf_test_dir'    => $rootDir.DIRECTORY_SEPARATOR.'test',
      'sf_doc_dir'     => $rootDir.DIRECTORY_SEPARATOR.'doc',
      'sf_plugins_dir' => $rootDir.DIRECTORY_SEPARATOR.'plugins',
    ));

    $this->setWebDir($rootDir.DIRECTORY_SEPARATOR.'web');
    $this->setCacheDir($rootDir.DIRECTORY_SEPARATOR.'cache');
  }

  /**
   * Returns the project root directory.
   *
   * @return string The project root directory
   */
  public function getRootDir()
  {
    return $this->rootDir;
  }

  /**
   * Sets the cache root directory.
   *
   * @param string $cacheDir The absolute path to the cache dir.
   */
  public function setCacheDir($cacheDir)
  {
    sfConfig::set('sf_cache_dir', $cacheDir);
  }

  /**
   * Sets the log directory.
   *
   * @param string $logDir The absolute path to the log dir.
   */
  public function setLogDir($logDir)
  {
    sfConfig::set('sf_log_dir', $logDir);
  }

  /**
   * Sets the web root directory.
   *
   * @param string $webDir The absolute path to the web dir.
   */
  public function setWebDir($webDir)
  {
    sfConfig::add(array(
      'sf_web_dir'    => $webDir,
      'sf_upload_dir' => $webDir.DIRECTORY_SEPARATOR.'uploads',
    ));
  }

  /**
   * Gets directories where model classes are stored. The order of returned paths is lowest precedence
   * to highest precedence.
   *
   * @return array An array of directories
   */
  public function getModelDirs()
  {
    return array_merge(
      $this->getPluginSubPaths('/lib/model'),     // plugins
      array(sfConfig::get('sf_lib_dir').'/model') // project
    );
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string $class  The generator class name
   * @param string $theme  The theme name
   *
   * @return array An array of directories
   */
  public function getGeneratorTemplateDirs($class, $theme)
  {
    return array_merge(
      array(sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/template'), // project
      $this->getPluginSubPaths('/data/generator/'.$class.'/'.$theme.'/template')       //plugins
    );
  }

  /**
   * Gets directories where the skeleton is stored for a generator class and a specific theme.
   *
   * @param string $class   The generator class name
   * @param string $theme   The theme name
   *
   * @return array An array of directories
   */
  public function getGeneratorSkeletonDirs($class, $theme)
  {
    return array_merge(
      array(sfConfig::get('sf_data_dir').'/generator/'.$class.'/'.$theme.'/skeleton'), // project
      $this->getPluginSubPaths('/data/generator/'.$class.'/'.$theme.'/skeleton')       // plugins
    );
  }

  /**
   * Gets the template to use for a generator class.
   *
   * @param string $class   The generator class name
   * @param string $theme   The theme name
   * @param string $path    The template path
   *
   * @return string A template path
   *
   * @throws sfException
   */
  public function getGeneratorTemplate($class, $theme, $path)
  {
    $dirs = $this->getGeneratorTemplateDirs($class, $theme);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.'/'.$path))
      {
        return $dir.'/'.$path;
      }
    }

    throw new sfException(sprintf('Unable to load "%s" generator template in: %s.', $path, implode(', ', $dirs)));
  }

  /**
   * Sets the enabled plugins.
   *
   * @param array An array of plugin names
   */
  public function setPlugins(array $plugins)
  {
    $this->plugins = $plugins;
  }

  /**
   * Enables a plugin or a list of plugins.
   *
   * @param array|string A plugin name or a plugin list
   */
  public function enablePlugins($plugins)
  {
    if (!is_array($plugins))
    {
      $plugins = array($plugins);
    }

    $this->plugins = array_merge($this->plugins, $plugins);
  }

  /**
   * Disables a plugin.
   *
   * @param array|string A plugin name or a plugin list
   */
  public function disablePlugins($plugins)
  {
    if (!is_array($plugins))
    {
      $plugins = array($plugins);
    }

    foreach ($plugins as $plugin)
    {
      if (false !== $pos = array_search($plugin, $this->plugins))
      {
        unset($this->plugins[$pos]);
      }
    }
  }

  /**
   * Enabled all installed plugins except the one given as argument.
   *
   * @param array|string A plugin name or a plugin list
   */
  public function enableAllPluginsExcept($plugins = array())
  {
    $this->plugins = array();
    foreach ($this->getAllPluginPaths() as $plugin)
    {
      $this->plugins[] = basename($plugin);
    }

    $this->disablePlugins($plugins);
  }

  /**
   * Gets the list of enabled plugins.
   *
   * @return array An array of enabled plugins
   */
  public function getPlugins()
  {
    return $this->plugins;
  }

  /**
   * Gets the paths plugin sub-directories, minding overloaded plugins.
   *
   * @param  string $subPath The subdirectory to look for
   *
   * @return array The plugin paths.
   */
  public function getPluginSubPaths($subPath = '')
  {
    if (array_key_exists($subPath, $this->pluginPaths))
    {
      return $this->pluginPaths[$subPath];
    }

    $this->pluginPaths[$subPath] = array();
    $pluginPaths = $this->getPluginPaths();
    foreach ($pluginPaths as $pluginPath)
    {
      if (is_dir($pluginPath.$subPath))
      {
        $this->pluginPaths[$subPath][] = $pluginPath.$subPath;
      }
    }

    return $this->pluginPaths[$subPath];
  }

  /**
   * Gets the paths to plugins root directories, minding overloaded plugins.
   *
   * @return array The plugin root paths.
   */
  public function getPluginPaths()
  {
    if (array_key_exists('', $this->pluginPaths))
    {
      return $this->pluginPaths[''];
    }

    $pluginPaths = $this->getAllPluginPaths();

    // order the plugins
    $basePaths = array_map(create_function('$v', 'return basename($v);'), $pluginPaths);
    $this->pluginPaths[''] = array();

    foreach ($this->getPlugins() as $plugin)
    {
      if (false !== $pos = array_search($plugin, $basePaths))
      {
        $this->pluginPaths[''][] = $pluginPaths[$pos];
      }
      else
      {
        throw new InvalidArgumentException(sprintf('The plugin "%s" does not exist.', $plugin));
      }
    }

    return $this->pluginPaths[''];
  }

  protected function getAllPluginPaths()
  {
    $pluginPaths = array();
    $finder = sfFinder::type('dir')->maxdepth(0)->follow_link()->relative();

    $bundledPlugins = $finder->discard('.*')->prune('.*')->in(sfConfig::get('sf_symfony_lib_dir').'/plugins');
    $projectPlugins = $finder->discard('.*')->prune('.*')->in(sfConfig::get('sf_plugins_dir'));

    // bundled plugins
    foreach ($bundledPlugins as $plugin)
    {
      // plugins can override bundle plugins
      if (false !== $pos = array_search($plugin, $projectPlugins))
      {
        $pluginPaths[] = sfConfig::get('sf_plugins_dir').'/'.$plugin;
        unset($projectPlugins[$pos]);
      }
      else
      {
        $pluginPaths[] = sfConfig::get('sf_symfony_lib_dir').'/plugins/'.$plugin;
      }
    }

    // project plugins
    foreach ($projectPlugins as $plugin)
    {
      $pluginPaths[] = sfConfig::get('sf_plugins_dir').'/'.$plugin;
    }

    return $pluginPaths;
  }

  /**
   * Returns the event dispatcher.
   *
   * @return sfEventDispatcher A sfEventDispatcher instance
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Returns the symfony lib directory.
   *
   * @return string The symfony lib directory
   */
  public function getSymfonyLibDir()
  {
    return $this->symfonyLibDir;
  }

  /**
   * Returns the active configuration.
   *
   * @return sfProjectConfiguration The current sfProjectConfiguration instance
   */
  static public function getActive()
  {
    if (is_null(sfProjectConfiguration::$active))
    {
      throw new RuntimeException('There is no active configuration.');
    }

    return sfProjectConfiguration::$active;
  }

  static public function guessRootDir()
  {
    $r = new ReflectionClass('ProjectConfiguration');

    return realpath(dirname($r->getFileName()).'/..');
  }

  /**
   * Returns a sfApplicationConfiguration configuration for a given application.
   *
   * @param string            $application    An application name
   * @param string            $environment    The environment name
   * @param Boolean           $debug          true to enable debug mode
   * @param string            $rootDir        The project root directory
   * @param sfEventDispatcher $dispatcher     An event dispatcher
   *
   * @return sfApplicationConfiguration A sfApplicationConfiguration instance
   */
  static public function getApplicationConfiguration($application, $environment, $debug, $rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    $class = $application.'Configuration';

    if (is_null($rootDir))
    {
      $rootDir = self::guessRootDir();
    }

    if (!file_exists($file = $rootDir.'/apps/'.$application.'/config/'.$class.'.class.php'))
    {
      throw new InvalidArgumentException(sprintf('The application "%s" does not exist.', $application));
    }

    require_once $file;

    return new $class($environment, $debug, $rootDir, $dispatcher);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'configuration.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}