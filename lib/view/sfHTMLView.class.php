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
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfHTMLView.class.php 3864 2007-04-24 15:27:52Z fabien $
 */
class sfHTMLView extends sfPHPView
{
  protected $extensions = array('.html.php', '.html', '.php');

  public function configure()
  {
    parent::configure();

    $this->getContext()->getResponse()->setContentType('text/html');

    sfConfig::set('sf_use_xhtml_tags', false); // merge patch for taghelper and settings option use_xhtml_tags
  }
}
