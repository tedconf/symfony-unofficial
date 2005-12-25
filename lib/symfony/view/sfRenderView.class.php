<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfRenderView.class.php 502 2005-10-06 16:02:14Z fabien $
 */
class sfRenderView extends sfPHPView
{
  public function execute()
  {
    $context          = $this->getContext();
    $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
    $action           = $actionStackEntry->getActionInstance();

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/view.yml';
    require(sfConfigCache::checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$viewConfigFile));

    $viewType = sfView::SUCCESS;
    $regexp = sfView::SUCCESS.'|'.sfView::ERROR;
    if (preg_match("/($regexp)$/i", $this->viewName, $match))
    {
      $viewType = $match[1];
    }

    // set template name
    $templateFile = $templateName.$viewType.'.php';
    $this->setTemplate($templateFile);

    // set template directory
    $module = $context->getModuleName();
    if (!is_readable($this->getDirectory().'/'.$templateFile))
    {
      // search template in a symfony module directory
      if (is_readable(sfConfig::get('sf_symfony_data_dir').'/symfony/modules/'.$module.'/templates/'.$templateFile))
      {
        $this->setDirectory(sfConfig::get('sf_symfony_data_dir').'/symfony/modules/'.$module.'/templates');
      }

      // search template for generated templates in cache
      if (is_readable(sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($module).'/templates/'.$templateFile))
      {
        $this->setDirectory(sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($module).'/templates');
      }
    }

    if (sfConfig::get('sf_logging_active')) $context->getLogger()->info('{sfRenderView} execute view for template "'.$templateName.$viewType.'.php"');
  }
}

?>