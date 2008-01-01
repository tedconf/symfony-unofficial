<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Core symfony class - loads symfony classes and bootstraps the symfony environment.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCore
{
  /**
   * The current symfony version.
   */
  const VERSION = '1.1.0-DEV';

  /**
   * Bootstraps the symfony environment.
   *
   * @param  string The path to the project directory.
   * @param  mixed  The application name or null.
   * @param  mixed  The dimension name or null.
   *
   * @return void
   */
  static public function bootstrap($sf_symfony_lib_dir, $sf_symfony_data_dir, $sf_dimension = null)
  {
    try
    {
      sfCore::initConfiguration($sf_symfony_lib_dir, $sf_symfony_data_dir, false, $sf_dimension);

      sfCore::initIncludePath();

      sfCore::callBootstrap();

      if (sfConfig::get('sf_check_lock'))
      {
        sfCore::checkLock();
      }

      if (sfConfig::get('sf_check_symfony_version'))
      {
        sfCore::checkSymfonyVersion();
      }
    }
    catch (sfException $e)
    {
      echo $e->getStackTrace();
    }
    catch (Exception $e)
    {
      echo sfException::createFromException($e)->getStackTrace();
    }
  }

  /**
   * Loads symfony core classes and configuration.
   *
   * @return void
   */
  static public function callBootstrap()
  {
    $configCache = sfConfigCache::getInstance();

    // load base settings
    require_once($configCache->checkConfig(sfConfig::get('sf_app_config_dir_name').DIRECTORY_SEPARATOR.'settings.yml'));

    if ($file = $configCache->checkConfig(sfConfig::get('sf_app_config_dir_name').'/app.yml', true))
    {
      require_once($file);
    }

    // force setting default timezone if not set
    if ($default_timezone = sfConfig::get('sf_default_timezone'))
    {
      date_default_timezone_set($default_timezone);
    }
    else if (sfConfig::get('sf_force_default_timezone', true))
    {
      date_default_timezone_set(@date_default_timezone_get());
    }

    // set default locale for formatting, collation, and internal error messages
    setlocale(LC_ALL, sfConfig::get('sf_default_locale', 'en_US.utf8'));

    // required core classes for the framework
    if (!sfConfig::get('sf_debug') && !sfConfig::get('sf_test'))
    {
      $configCache->import(sfConfig::get('sf_app_config_dir_name').DIRECTORY_SEPARATOR.'core_compile.yml', false);
    }

    // error settings
    ini_set('display_errors', SF_DEBUG ? 'on' : 'off');
    error_reporting(sfConfig::get('sf_error_reporting'));

    // php compat settings
    ini_set('magic_quotes_runtime', 'off');
    ini_set('register_globals', 'off');

    // include all config.php from plugins
    sfLoader::loadPluginConfig();

    // compress output
    ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : '');
  }

  /**
   * Loads symfony core classes + configuration + autoloader.
   *
   * @param  string  The path to the project directory.
   * @param  mixed   The application name or null.
   * @param  boolean In a test?
   * @param  mixed   The dimension name or null.
   *
   * @return void
   */
  static public function initConfiguration($sf_symfony_lib_dir, $sf_symfony_data_dir, $test = false, $sf_dimension = null)
  {
    // YAML support
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'sfYaml.class.php');

    // APC cache support
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'sfCache.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'sfAPCCache.class.php');

    // config support
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfConfig.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfConfigCache.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfConfigHandler.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfYamlConfigHandler.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfAutoloadConfigHandler.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfRootConfigHandler.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfLoader.class.php');

    // exceptions
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'sfException.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'sfConfigurationException.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'sfCacheException.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'sfParseException.class.php');

    // utils
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'sfParameterHolder.class.php');
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'sfToolkit.class.php');

    // autoloading
    require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'sfAutoload.class.php');

    // in debug mode, load timer classes and start global timer
    if (SF_DEBUG)
    {
      require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'debug'.DIRECTORY_SEPARATOR.'sfTimerManager.class.php');
      require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'debug'.DIRECTORY_SEPARATOR.'sfTimer.class.php');
      sfConfig::set('sf_timer_start', microtime(true));
    }

    // main configuration
    sfConfig::add(array(
      'sf_debug'            => SF_DEBUG,
      'sf_symfony_lib_dir'  => $sf_symfony_lib_dir,
      'sf_symfony_data_dir' => $sf_symfony_data_dir,
      'sf_test'             => $test,
    ));

    // dimensions config support
    if(!empty($sf_dimension) && is_array($sf_dimension))
    {
      require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'CartesianIterator.class.php');
      require_once($sf_symfony_lib_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'sfConfigDimension.class.php');

      $dimension = sfConfigDimension::getInstance();

      $dimension->set($sf_dimension);

      // dimension configuration
      sfConfig::add(array(
        'sf_dimension'         => $sf_dimension = $dimension->__toString(),
        'sf_dimension_cascade' => $dimension->getCascade()
      ));
    }
    else
    {
      $sf_dimension = null;
    }

    sfAutoload::getInstance()->register();

    // directory layout
    self::initDirectoryLayout(SF_ROOT_DIR, SF_APP, SF_ENVIRONMENT, $sf_dimension);
  }

  /**
   * Extends php include path to include symfony path.
   *
   * @return void
   */
  static public function initIncludePath()
  {
    set_include_path(
      sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_root_dir').PATH_SEPARATOR.
      sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'vendor'.PATH_SEPARATOR.
      get_include_path()
    );
  }

  /**
   * Check lock files to see if we're not in a cache cleaning process.
   *
   * @return void
   */
  static public function checkLock()
  {
    if (sfToolkit::hasLockFile(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.'_'.SF_ENVIRONMENT.'.lck', 5))
    {
      // application is not available
      $file = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'errors'.DIRECTORY_SEPARATOR.'unavailable.php';
      include(is_readable($file) ? $file : sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'errors'.DIRECTORY_SEPARATOR.'unavailable.php');

      die(1);
    }
  }

  /**
   * Checks symfony version and clears cache if recent update.
   *
   * @return void
   */
  static public function checkSymfonyVersion()
  {
    // recent symfony update?
    if (self::VERSION != @file_get_contents(sfConfig::get('sf_config_cache_dir').DIRECTORY_SEPARATOR.'VERSION'))
    {
      // clear cache
      sfToolkit::clearDirectory(sfConfig::get('sf_config_cache_dir'));
    }
  }

  /**
   * Initializes directory layout for the project.
   *
   * @param  string The path to the project directory.
   * @param  mixed  The application name or null.
   * @param  mixed  The environment name or null.
   * @param  mixed  The dimension name or null.
   *
   * @return void
   */
  static public function initDirectoryLayout($sf_root_dir, $sf_app = null, $sf_environment = null, $sf_dimension = null)
  {
    sfConfig::add(array(
      'sf_root_dir'         => $sf_root_dir,

      // root directory names
      'sf_bin_dir_name'     => $sf_bin_dir_name     = 'batch',
      'sf_cache_dir_name'   => $sf_cache_dir_name   = 'cache',
      'sf_log_dir_name'     => $sf_log_dir_name     = 'log',
      'sf_lib_dir_name'     => $sf_lib_dir_name     = 'lib',
      'sf_web_dir_name'     => $sf_web_dir_name     = 'web',
      'sf_upload_dir_name'  => $sf_upload_dir_name  = 'uploads',
      'sf_data_dir_name'    => $sf_data_dir_name    = 'data',
      'sf_config_dir_name'  => $sf_config_dir_name  = 'config',
      'sf_apps_dir_name'    => $sf_apps_dir_name    = 'apps',
      'sf_test_dir_name'    => $sf_test_dir_name    = 'test',
      'sf_doc_dir_name'     => $sf_doc_dir_name     = 'doc',
      'sf_plugins_dir_name' => $sf_plugins_dir_name = 'plugins',

      // global directory structure
      'sf_apps_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_apps_dir_name,
      'sf_lib_dir'        => $sf_lib_dir = $sf_root_dir.DIRECTORY_SEPARATOR.$sf_lib_dir_name,
      'sf_bin_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_bin_dir_name,
      'sf_web_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_web_dir_name,
      'sf_upload_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_web_dir_name.DIRECTORY_SEPARATOR.$sf_upload_dir_name,
      'sf_log_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_log_dir_name,
      'sf_data_dir'       => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_data_dir_name,
      'sf_config_dir'     => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_config_dir_name,
      'sf_test_dir'       => $sf_test_dir = $sf_root_dir.DIRECTORY_SEPARATOR.$sf_test_dir_name,
      'sf_doc_dir'        => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_doc_dir_name,
      'sf_plugins_dir'    => $sf_root_dir.DIRECTORY_SEPARATOR.$sf_plugins_dir_name,
      'sf_cache_dir'      => $sf_cache_dir = is_null($sf_dimension) ? $sf_root_dir.DIRECTORY_SEPARATOR.$sf_cache_dir_name : $sf_root_dir.DIRECTORY_SEPARATOR.$sf_cache_dir_name.DIRECTORY_SEPARATOR.$sf_dimension,

      // test directory names
      'sf_test_bootstrap_dir_name'  => $sf_test_bootstrap_dir_name = 'bootstrap',
      'sf_test_unit_dir_name'       => $sf_test_unit_dir_name = 'unit',
      'sf_test_functional_dir_name' => $sf_test_functional_dir_name = 'functional',

      // test directory structure
      'sf_test_bootstrap_dir'   => $sf_test_dir.DIRECTORY_SEPARATOR.$sf_test_bootstrap_dir_name,
      'sf_test_unit_dir'        => $sf_test_dir.DIRECTORY_SEPARATOR.$sf_test_unit_dir_name,
      'sf_test_functional_dir'  => $sf_test_dir.DIRECTORY_SEPARATOR.$sf_test_functional_dir_name,

      // lib directory names
      'sf_model_dir_name' => $sf_model_dir_name = 'model',

      // lib directory structure
      'sf_model_lib_dir'  => $sf_lib_dir.DIRECTORY_SEPARATOR.$sf_model_dir_name,

      // SF_APP_DIR sub-directories names
      'sf_app_i18n_dir_name'     => $sf_app_i18n_dir_name     = 'i18n',
      'sf_app_config_dir_name'   => $sf_app_config_dir_name   = 'config',
      'sf_app_lib_dir_name'      => $sf_app_lib_dir_name      = 'lib',
      'sf_app_module_dir_name'   => $sf_app_module_dir_name   = 'modules',
      'sf_app_template_dir_name' => $sf_app_template_dir_name = 'templates',

      // SF_APP_MODULE_DIR sub-directories names
      'sf_app_module_action_dir_name'   => 'actions',
      'sf_app_module_template_dir_name' => 'templates',
      'sf_app_module_lib_dir_name'      => 'lib',
      'sf_app_module_view_dir_name'     => 'views',
      'sf_app_module_validate_dir_name' => 'validate',
      'sf_app_module_config_dir_name'   => 'config',
      'sf_app_module_i18n_dir_name'     => 'i18n',
    ));

    // current application structure
    if (!is_null($sf_app))
    {
      sfConfig::add(array(
        'sf_app'                => $sf_app,
        'sf_environment'        => $sf_environment,

        'sf_app_dir'            => $sf_app_dir = $sf_root_dir.DIRECTORY_SEPARATOR.$sf_apps_dir_name.DIRECTORY_SEPARATOR.$sf_app,
        'sf_app_base_cache_dir' => $sf_app_base_cache_dir = $sf_cache_dir.DIRECTORY_SEPARATOR.$sf_app,
        'sf_app_cache_dir'      => $sf_app_cache_dir = $sf_app_base_cache_dir.DIRECTORY_SEPARATOR.$sf_environment,

        // SF_APP_DIR directory structure
        'sf_app_config_dir'     => $sf_app_dir.DIRECTORY_SEPARATOR.$sf_app_config_dir_name,
        'sf_app_lib_dir'        => $sf_app_dir.DIRECTORY_SEPARATOR.$sf_app_lib_dir_name,
        'sf_app_module_dir'     => $sf_app_dir.DIRECTORY_SEPARATOR.$sf_app_module_dir_name,
        'sf_app_template_dir'   => $sf_app_dir.DIRECTORY_SEPARATOR.$sf_app_template_dir_name,
        'sf_app_i18n_dir'       => $sf_app_dir.DIRECTORY_SEPARATOR.$sf_app_i18n_dir_name,

        // SF_CACHE_DIR directory structure
        'sf_template_cache_dir' => $sf_app_cache_dir.DIRECTORY_SEPARATOR.'template',
        'sf_i18n_cache_dir'     => $sf_app_cache_dir.DIRECTORY_SEPARATOR.'i18n',
        'sf_config_cache_dir'   => $sf_app_cache_dir.DIRECTORY_SEPARATOR.$sf_config_dir_name,
        'sf_test_cache_dir'     => $sf_app_cache_dir.DIRECTORY_SEPARATOR.'test',
        'sf_module_cache_dir'   => $sf_app_cache_dir.DIRECTORY_SEPARATOR.'modules',
      ));
    }
  }
}
