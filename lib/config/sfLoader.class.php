<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoader is a class which contains the logic to look for files/classes in symfony.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLoader
{
  /**
   * Gets directories where model classes are stored.
   *
   * @return array An array of directories
   */
  static public function getModelDirs()
  {
    $dirs = array(sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'model' ? sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'model' : 'lib'.DIRECTORY_SEPARATOR.'model'); // project
    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'model'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugins
    }

    return $dirs;
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  static public function getControllerDirs($moduleName)
  {
    $suffix = $moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_action_dir_name');

    $dirs = array();
    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[$key.DIRECTORY_SEPARATOR.$suffix] = $value;
    }

    $dirs[sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$suffix] = false;                                     // application

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix))
    {
      $dirs = array_merge($dirs, array_combine($pluginDirs, array_fill(0, count($pluginDirs), true)));                // plugins
    }

    $dirs[sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix] = true;                            // core modules

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  static public function getTemplateDirs($moduleName)
  {
    $suffix = $moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name');

    $dirs = array();
    foreach (sfConfig::get('sf_module_dirs', array()) as $key => $value)
    {
      $dirs[] = $key.DIRECTORY_SEPARATOR.$suffix;
    }

    $dirs[] = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$suffix;                                   // application

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                  // plugins
    }

    $dirs[] = sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$suffix;   // core modules
    $dirs[] = sfConfig::get('sf_module_cache_dir').DIRECTORY_SEPARATOR.'auto'.ucfirst($suffix);                 // generated templates in cache

    return $dirs;
  }

  /**
   * Gets the template directory to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template directory
   */
  static public function getTemplateDir($moduleName, $templateFile)
  {
    $dirs = self::getTemplateDirs($moduleName);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.DIRECTORY_SEPARATOR.$templateFile))
      {
        return $dir;
      }
    }

    return null;
  }

  /**
   * Gets the template to use for a given module and template file.
   *
   * @param string The module name
   * @param string The template file
   *
   * @return string A template path
   */
  static public function getTemplatePath($moduleName, $templateFile)
  {
    $dir = self::getTemplateDir($moduleName, $templateFile);

    return $dir ? $dir.DIRECTORY_SEPARATOR.$templateFile : null;
  }

  /**
   * Gets the i18n directories to use globally.
   *
   * Returns null if the current i18n source is not a file based i18n backend (XLIFF or gettext).
   *
   * @return array An array of i18n directories
   */
  static public function getI18NGlobalDirs()
  {
    if (!in_array(sfConfig::get('sf_i18n_source'), array('XLIFF', 'gettext')))
    {
      return null;
    }

    $dirs = array();

    // application
    if (is_dir($dir = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name')))
    {
      $dirs[] = $dir;
    }

    // plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name'));
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    return $dirs;
  }

  /**
   * Gets the i18n directories to use for a given module.
   *
   * Returns null if the current i18n source is not a file based i18n backend (XLIFF or gettext).
   *
   * @param string The module name
   *
   * @return array An array of i18n directories
   */
  static public function getI18NDirs($moduleName)
  {
    if (!in_array(sfConfig::get('sf_i18n_source'), array('XLIFF', 'gettext')))
    {
      return null;
    }

    $dirs = array();

    // module
    if (is_dir($dir = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name')))
    {
      $dirs[] = $dir;
    }

    // application
    if (is_dir($dir = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name')))
    {
      $dirs[] = $dir;
    }

    // module in plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name'));
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    // plugins
    $pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_i18n_dir_name'));
    if (isset($pluginDirs[0]))
    {
      $dirs[] = $pluginDirs[0];
    }

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
   *
   * @return array An array of directories
   */
  static public function getGeneratorTemplateDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'template');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'template'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'template'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                         // bundled plugin
    }

    return $dirs;
  }

  /**
   * Gets directories where the skeleton is stored for a generator class and a specific theme.
   *
   * @param string The generator class name
   * @param string The theme name
   *
   * @return array An array of directories
   */
  static public function getGeneratorSkeletonDirs($class, $theme)
  {
    $dirs = array(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'skeleton');                  // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'skeleton'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                // plugin
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.$class.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.'skeleton'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                         // bundled plugin
    }

    return $dirs;
  }

  /**
   * Gets the template to use for a generator class.
   *
   * @param string The generator class name
   * @param string The theme name
   * @param string The template path
   *
   * @return string A template path
   *
   * @throws sfException
   */
  static public function getGeneratorTemplate($class, $theme, $path)
  {
    $dirs = self::getGeneratorTemplateDirs($class, $theme);
    foreach ($dirs as $dir)
    {
      if (is_readable($dir.DIRECTORY_SEPARATOR.$path))
      {
        return $dir.DIRECTORY_SEPARATOR.$path;
      }
    }

    throw new sfException(sprintf('Unable to load "%s" generator template in: %s.', $path, implode(', ', $dirs)));
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string The configuration path
   *
   * @return array An array of paths
   */
  static public function getConfigPaths($configPath)
  {
    $globalConfigPath = basename(dirname($configPath)).DIRECTORY_SEPARATOR.basename($configPath);

    $files = array(
      sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.$globalConfigPath,                    // symfony
      sfConfig::get('sf_symfony_data_dir').DIRECTORY_SEPARATOR.$configPath,                          // core modules
    );

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$globalConfigPath))
    {
      $files = array_merge($files, $bundledPluginDirs);                                             // bundled plugins
    }

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$globalConfigPath))
    {
      $files = array_merge($files, $pluginDirs);                                                    // plugins
    }

    $files = array_merge($files, array(
      sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$globalConfigPath,                            // project
      sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$configPath,                                  // project
      sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$globalConfigPath,                             // application
      sfConfig::get('sf_app_cache_dir').DIRECTORY_SEPARATOR.$configPath,                             // generated modules
    ));

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.$configPath))
    {
      $files = array_merge($files, $pluginDirs);                                                     // plugins
    }

    $files[] = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$configPath;                          // module

    $configs = array();
    foreach (array_unique($files) as $file)
    {
      if (is_readable($file))
      {
        $configs[] = $file;
      }
    }

    return $configs;
  }

  /**
   * Gets the helper directories for a given module name.
   *
   * @param string The module name
   *
   * @return array An array of directories
   */
  static public function getHelperDirs($moduleName = '')
  {
    $dirs = array();

    if ($moduleName)
    {
      $dirs[] = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_lib_dir_name').DIRECTORY_SEPARATOR.'helper'; // module

      if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
      {
        $dirs = array_merge($dirs, $pluginDirs);                                                                              // module plugins
      }
    }

    $dirs[] = sfConfig::get('sf_app_lib_dir').DIRECTORY_SEPARATOR.'helper';                                                                      // application

    $dirs[] = sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'helper';                                                                          // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                                                                // plugins
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                                         // bundled plugins
    }

    return $dirs;
  }

  /**
   * Loads helpers.
   *
   * @param array  An array of helpers to load
   * @param string A module name (optional)
   *
   * @throws sfViewException
   */
  static public function loadHelpers($helpers, $moduleName = '')
  {
    static $loaded = array();

    $dirs = self::getHelperDirs($moduleName);
    foreach ((array) $helpers as $helperName)
    {
      if (isset($loaded[$helperName]))
      {
        continue;
      }

      $fileName = $helperName.'Helper.php';
      foreach ($dirs as $dir)
      {
        $included = false;
        if (is_readable($dir.DIRECTORY_SEPARATOR.$fileName))
        {
          include($dir.DIRECTORY_SEPARATOR.$fileName);
          $included = true;
          break;
        }
      }

      if (!$included)
      {
        // search in the include path
        if ((@include('helper'.DIRECTORY_SEPARATOR.$fileName)) != 1)
        {
          $dirs = array_merge($dirs, explode(PATH_SEPARATOR, get_include_path()));

          // remove sf_root_dir from dirs
          foreach ($dirs as &$dir)
          {
            $dir = str_replace('%SF_ROOT_DIR%', sfConfig::get('sf_root_dir'), $dir);
          }

          throw new sfViewException(sprintf('Unable to load "%sHelper.php" helper in: %s.', $helperName, implode(', ', $dirs)));
        }
      }

      $loaded[$helperName] = true;
    }
  }

  static public function loadPluginConfig()
  {
    if ($pluginConfigs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
    {
      foreach ($pluginConfigs as $config)
      {
        include($config);
      }
    }

    if ($pluginConfigs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
    {
      foreach ($pluginConfigs as $config)
      {
        include($config);
      }
    }
  }
}
