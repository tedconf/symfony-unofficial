<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PartialHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function include_partial($name, $vars = array())
{
  // partial is in another module?
  $sep = strpos($name, '/');
  if ($sep)
  {
    $type = strtolower(substr($name, 0, $sep));
    $filename = '_'.substr($name, $sep + 1).'.php';
  }
  else
  {
    $type = '';
    $filename = '_'.$name.'.php';
  }

  $context = sfContext::getInstance();

  $lastActionEntry = $context->getActionStack()->getLastEntry();
  $firstActionEntry = $context->getActionStack()->getFirstEntry();

  // global variables
  $vars = array_merge($vars, array(
    'context'       => $context,
    'params'        => $context->getRequest()->getParameterHolder(),
    'request'       => $context->getRequest(),
    'user'          => $context->getUser(),
    'last_module'   => $lastActionEntry->getModuleName(),
    'last_action'   => $lastActionEntry->getActionName(),
    'first_module'  => $firstActionEntry->getModuleName(),
    'first_action'  => $firstActionEntry->getActionName(),
  ));

  // local action variables
  $action = $context->getActionStack()->getLastEntry()->getActionInstance();
  if (method_exists($action, 'getVars'))
  {
    $vars = array_merge($vars, $action->getVars());
  }

  extract($vars);

  $config = sfConfig::getInstance();

  // render to client
  if ($sep && $type == 'global')
  {
    require $config->get('sf_app_template_dir').DS.$filename;
  }
  else if ($sep)
  {
    require $config->get('sf_app_module_dir').DS.$type.DS.$config->get('sf_app_module_template_dir_name').DS.$filename;
  }
  else
  {
    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();
    require $config->get('sf_app_dir').DS.$config->get('sf_app_module_dir_name').DS.$current_module.DS.$config->get('sf_app_module_template_dir_name').DS.$filename;
  }
}

?>