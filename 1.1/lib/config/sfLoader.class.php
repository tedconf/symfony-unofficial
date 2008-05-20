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
   * Gets the helper directories for a given module name.
   *
   * @param string $moduleName The module name
   *
   * @return array An array of directories
   */
  static public function getHelperDirs($moduleName = '')
  {
    $dirs = array();

    if ($moduleName)
    {
      $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/lib/helper'; // module

      if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
      {
        $dirs = array_merge($dirs, $pluginDirs);                                  // module plugins
      }
    }

    $dirs[] = sfConfig::get('sf_app_lib_dir').'/helper';                          // application
    $dirs[] = sfConfig::get('sf_lib_dir').'/helper';                              // project

    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
    {
      $dirs = array_merge($dirs, $pluginDirs);                                    // plugins
    }

    if ($bundledPluginDirs = glob(sfConfig::get('sf_symfony_lib_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'helper'))
    {
      $dirs = array_merge($dirs, $bundledPluginDirs);                                                                         // bundled plugins
    }

    $dirs[] = sfConfig::get('sf_symfony_lib_dir').'/helper';                      // symfony

    return $dirs;
  }

  /**
   * Loads helpers.
   *
   * @param array  $helpers     An array of helpers to load
   * @param string $moduleName  A module name (optional)
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
          include_once($dir.DIRECTORY_SEPARATOR.$fileName);

          $included = true;
          break;
        }
      }

      if (!$included)
      {
        // search in the include path
        if ((include_once('helper'.DIRECTORY_SEPARATOR.$fileName)) != 1)
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
}
