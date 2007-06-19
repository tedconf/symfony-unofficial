<?php

/**
 * test actions.
 *
 * @package    project
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class testActions extends sfActions
{
  /**
   * Executes index action
   *
   */
  public function executeIndex()
  {
  }
  
  public function executeHtmlOnly()
  {
  }

  public function executeHelper()
  {
  }
      
  public function executeTestMultiformat()
  {
    $this->renderMultiformat('format', array('html', 'pjs', 'titi'));
  }

  public function executeTestMultiformatDefaults()
  {
    $this->setTemplate('testMultiformat');
    $this->renderMultiformat();
  }

  public function executeTestMultiformatUndefined()
  {
    $this->setTemplate('testMultiformat');
    $this->renderMultiformat();
  }

  public function executeTestMultiformatByHand()
  {
    $this->setTemplate('testMultiformat');
    switch ($this->getRequestParameter('format')) 
    {
      case 'html':
        $this->setViewClass('sfPHP');
        break;
      case 'pjs':
        $this->setViewClass('sfJavascript');
        break;
      default:
        $this->setViewClass('sfJavascript');
        break;
    }
  }
  
}
