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
 * sfActionClassLoader finds and loads controller action classes.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfActionClassLoader.class.php,v 1.5 2006/06/22 23:20:22 salisbur Exp $
 */

/**
 * Loads action classes given a module name, action name, extension and culture.
 * Caches classes so it doesn't need to scan directories again.
 */
class sfActionClassLoader
{
  private
    $controllerClasses  = array();

  /**
   * Initialize this loader.
   *
   * @return void
   */
  public function initialize ()
  {
    if (sfConfig::get('sf_logging_active'))
    {
      sfContext::getInstance()->getLogger()->info('{sfActionClassLoader} initialization');
    }
  }

  /**
   * Loads the class specified and returns its (string) name.
   *
   * @param string A module name.
   * @param string A controller name.
   * @param string The controller type (action, component)
   *
   * If the $culture-specific class is not found, the generic one is returned.
   * If the class doesn't exist, null is returned.
   * If error loading the class, throws sfConfigurationException
   * @return string Class name if the class exists.  Null otherwise.
   */
  public function loadClass($moduleName, $controllerName, $extension)
  {
    $class = $this->loadClassInternal($moduleName, $controllerName, $extension);
    if ($class == null) { return null; }

    // fix for same name classes
    // seems messy.  needed? ###mps
    $moduleClass = $moduleName.'_'.$class;

    if (class_exists($moduleClass, false))
    {
      return $moduleClass;
    }
    else
    {
      return $class;
    }
  }

  private function loadClassInternal ($moduleName, $controllerName, $extension)
  {
    if (isset($this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$extension]))
    {
      return $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$extension];
    }

    // all directories to look for modules
    $dirs = array(
      // application
      sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_action_dir_name') => false,

      // local plugin
      sfConfig::get('sf_plugin_data_dir').'/modules/'.$moduleName.'/actions' => true,

      // core modules or global plugins
      sfConfig::get('sf_symfony_data_dir').'/modules/'.$moduleName.'/actions' => true,
    );

    foreach ($dirs as $dir => $checkActivated)
    {
      // plugin module activated?
      if ($checkActivated && !in_array($moduleName, sfConfig::get('sf_activated_modules')) && is_readable($dir))
      {
        $error = 'The module "%s" is not activated.';
        $error = sprintf($error, $moduleName);

        throw new sfConfigurationException($error);
      }

      // one action per file or one file for all actions
      $classFile   = strtolower($extension);
      $classSuffix = ucfirst(strtolower($extension));
      $file        = $dir.'/'.$controllerName.$classSuffix.'.class.php';
      $module_file = $dir.'/'.$classFile.'s.class.php';
      if (is_readable($file))
      {
        // action class exists
        require_once($file);

        $className = $controllerName.$classSuffix;
        $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $className;

        return $className;
      }
      else if (is_readable($module_file))
      {
        // module class exists
        require_once($module_file);

        // action is defined in this class?
        $defined = in_array('execute'.ucfirst($controllerName), get_class_methods($moduleName.$classSuffix.'s'));
        if ($defined)
        {
          $className = $moduleName.$classSuffix.'s';
          $this->controllerClasses[$moduleName.'_'.$controllerName.'_'.$classSuffix] = $className;
          return $className;
        }

        return null;
      }
    }

    return null;
  }

}

?>
