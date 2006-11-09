<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * Copyright (c) 2006 Yahoo! Inc.  All rights reserved.  
 * The copyrights embodied in the content in this file are licensed 
 * under the MIT open source license
 *
 * For the full copyright and license information, please view the LICENSE
 * and LICENSE.yahoo files that was distributed with this source code.
 */

/**
 * sfViewFinder.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfViewFinder.class.php,v 1.2 2006/06/22 18:52:32 salisbur Exp $
 */

/**
 * finds a partial implementation
 */
class sfViewFinder
{
  /**
   * Initialize this finder.
   *
   * @return void
   */
  public function initialize ()
  {
    if (sfConfig::get('sf_logging_active'))
    {
      sfContext::getInstance()->getLogger()->info('{sfViewFinder} initialization');
    }
  }

  /**
   * returns the full path for this decorator
   */
  function findDecorator($directory, $filename)
  {
    return $directory.'/'.$filename;
  }

  /**
   * returns full path/filename of this partial
   */
/*
  function findPartial($moduleName, $partialName)
  {
    $context = sfContext::getInstance();
    $filename = '_'.$partialName.'.php';
    $current_module = $context->getActionStack()->getLastEntry()->getModuleName();

    // collect possible filenames
    $filenames = array();
    if ($moduleName == 'global')
    {
      $filenames[] = sfConfig::get('sf_app_template_dir').DIRECTORY_SEPARATOR.$filename;
    }
    else if ($moduleName != null)
    {
      $filenames[] = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$moduleName.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
    }
    else
    {
      $filenames[] = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name').DIRECTORY_SEPARATOR.$current_module.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
    }

    // search partial for generated templates in cache
    $filenames[] = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($current_module).'/templates/'.$filename;

    // search partial in a symfony module directory
    $filenames[] = sfConfig::get('sf_symfony_data_dir').'/modules/'.$current_module.'/templates/'.$filename;

    // now walk through possibilities
    foreach ($filenames as $partial) 
    {
#      error_log("P:trying $partial");
      if (is_readable($partial)) {
        return $partial;
      }
    }
    return null;
  }
*/

  protected function getPossibleTemplateDirs($moduleName, $baseDir)
  {
    return array(
      // module main dir
      $baseDir,

      // local plugin
      sfConfig::get('sf_plugin_data_dir').'/modules/'.$moduleName.'/templates',

      // core modules or global plugins
      sfConfig::get('sf_symfony_data_dir').'/modules/'.$moduleName.'/templates',

      // generated templates in cache
      sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($moduleName).'/templates',
    );
  }

  /**
   * Finds the location for the given template.
   * @param viewType type of template (1=xxx, 2=yyy, 3=zzz, 4=qqq)
   * @param moduleName the name of the current module
   * @param templateFile the name of the template file
   * @param templateDir the module's template directory
   * @return string the directory of the templateFile or null if not found.
   */
  public function getTemplateDir($viewType, $moduleName, $templateFile, $templateDir)
  {
    switch ($viewType)
    {
      case 1: // global partial
      {
        $dirs = array(sfConfig::get('sf_app_template_dir'));
        break;
      }
      case 2: // partial
      case 3:
      case 4:
      {
        $dirs = $this->getPossibleTemplateDirs($moduleName, $templateDir);
        break;
      }
      default:
      {
        # error??
      }
    }

    foreach ($dirs as $dir)
    {
#error_log("trying $dir/$templateFile");
      if (is_readable($dir.'/'.$templateFile))
      {
        return $dir;
      }
    }
  }
}

?>
