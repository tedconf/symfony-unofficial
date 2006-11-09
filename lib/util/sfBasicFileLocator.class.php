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
 * sfBasicFileLocator handles default file locating.
 *
 * @package    symfony
 * @subpackage utility
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfBasicFileLocator.class.php,v 1.4 2006/06/22 18:51:54 salisbur Exp $
 */

/**
 * Combines various file locator classes in one place.
 */
class sfBasicFileLocator extends sfFileLocator
{
  protected $actionClassLoader = null;
  protected $viewClassLoader = null;
  protected $viewFinder = null;

  /**
   * Initialize this locator.
   *
   * @return void
   */
  public function initialize ()
  {
    $this->actionClassLoader = new sfActionClassLoader();
    $this->viewClassLoader = new sfViewClassLoader();
    $this->viewFinder = new sfViewFinder();

    $this->actionClassLoader->initialize();
    $this->viewClassLoader->initialize();
    $this->viewFinder->initialize();

    if (sfConfig::get('sf_logging_active'))
    {
      sfContext::getInstance()->getLogger()->info('{sfBasicFileLocator} initialization');
    }
  }

  public function loadActionClass($moduleName, $controllerName, $extension)
  {
    return $this->actionClassLoader->loadClass($moduleName, $controllerName, $extension);
  }

  public function loadViewClass($moduleName, $actionName, $viewName)
  {
    return $this->viewClassLoader->loadClass($moduleName, $actionName, $viewName);
  }

  public function getViewTemplateDir($viewType, $moduleName, $templateFile,
                                  $templateDir)
  {
    return $this->viewFinder->getTemplateDir($viewType, $moduleName, 
                                             $templateFile, $templateDir);
  }

/*
  public function findPartial($moduleName, $partialName)
  {
    return $this->viewFinder->findPartial($moduleName, $partialName);
  }
*/

  public function findDecorator($directory, $filename)
  {
    return $this->viewFinder->findDecorator($directory, $filename);
  }

}

?>
