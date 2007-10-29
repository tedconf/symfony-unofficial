<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfJavascriptView.class.php 3864 2007-04-24 15:27:52Z fabien $
 */
class sfJavascriptView extends sfPHPView
{
  protected $extensions = array('.pjs', '.js.php', '.js', '.php');

  public function configure()
  {
    parent::configure();

    $this->context->getResponse()->setContentType('application/x-javascript');
  }
}
