<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HelperHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function use_helpers()
{
  foreach (func_get_args() as $helperName)
  {
    use_helper($helperName);
  }
}

function use_helper($helperName)
{
  $config = sfConfig::getInstance();

  if (is_readable($config->get('sf_symfony_lib_dir').'/symfony/helper/'.$helperName.'Helper.php'))
  {
    // global helper
    include_once('symfony/helper/'.$helperName.'Helper.php');
  }
  else if (is_readable($config->get('sf_app_module_dir').'/'.sfContext::getInstance()->getModuleName().'/'.$config->get('sf_app_module_lib_dir_name').'/helper/'.$helperName.'Helper.php'))
  {
    // current module helper
    include_once($config->get('sf_app_module_dir').'/'.sfContext::getInstance()->getModuleName().'/'.$config->get('sf_app_module_lib_dir_name').'/helper/'.$helperName.'Helper.php');
  }
  else
  {
    // helper in include_path
    include_once('helper/'.$helperName.'Helper.php');
  }
}

?>