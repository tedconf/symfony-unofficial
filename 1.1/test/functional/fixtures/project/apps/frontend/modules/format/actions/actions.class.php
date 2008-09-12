<?php

/**
 * format actions.
 *
 * @package    project
 * @subpackage format
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class formatActions extends sfActions
{
  public function executeIndex($request)
  {
    if ('xml' == $request->getRequestFormat())
    {
      $this->setLayout('layout');
    }
  }

  public function executeForTheIPhone($request)
  {
    $this->setTemplate('index');
  }

  public function executeJs($request)
  {
    $request->setRequestFormat('js');
  }

  public function executeJsWithAccept()
  {
    $this->setTemplate('index');
  }
}