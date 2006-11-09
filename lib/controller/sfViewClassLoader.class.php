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
 * sfViewClassLoader finds and loads view classes
 *
 * @package    symfony
 * @subpackage controller
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfViewClassLoader.class.php,v 1.2 2006/06/22 18:51:00 salisbur Exp $
 */

class sfViewClassLoader 
{
  /**
   * Initialize this loader.
   *
   * @return void
   */
  public function initialize ()
  {
    if (sfConfig::get('sf_logging_active'))
    {
      sfContext::getInstance()->getLogger()->info('{sfViewClassLoader} initialization');
    }
  }

  /**
   * Find and load a View implementation
   *
   * @param string A module name.
   * @param string An action name.
   * @param string A view name.
   *
   * @return The view classname if the view exists, otherwise null.
   */
  public function loadClass ($moduleName, $actionName, $viewName)
  {
    // user view exists?
    $file = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/'.sfConfig::get('sf_app_module_view_dir_name').'/'.$viewName.'View.class.php';

    if (is_readable($file))
    {
      require_once($file);

      $class = $viewName.'View';

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
      $viewName = sfContext::getInstance()->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', '', 'symfony/action/view') ? $this->getContext()->getRequest()->getAttribute($moduleName.'_'.$actionName.'_view_name', '', 'symfony/action/view') : sfConfig::get('mod_'.strtolower($moduleName).'_view_class');
      $file     = sfConfig::get('sf_symfony_lib_dir').'/view/'.$viewName.'View.class.php';
      $class    = is_readable($file) ? $viewName.'View' : 'sfPHPView';
    }

    return $class;
  }
}

?>
