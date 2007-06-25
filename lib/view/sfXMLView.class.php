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
 * @version    SVN: $Id: sfXMLView.class.php 3864 2007-04-24 15:27:52Z fabien $
 */
class sfXMLView extends sfPHPView
{
  protected $extensions = array('.xml.php', '.xml', '.php');

  public function configure()
  {
    parent::configure();

    $this->getContext()->getResponse()->setPreferredContentType(array('application/xml', 'text/xml'));

    /*
    // http://manalang.wordpress.com/2004/06/17/xslts-for-rss-and-atom-feeds/
    // http://dotnetjunkies.com/Tutorial/9FB56D07-4052-458C-B247-37C9E4B6D719.dcik

    // should we handle xml + xslt = xhtml transformations (as part of a stylesheet (xml + xslt = (xml + xsl) || (xhtml + css)))?

    // automatically add the xml declaration: <?xml version="1.0" encoding="UTF-8"?>

    $parser = xslt_create();
    $xhtml = xslt_process($parser, "example.xml", "example.xsl");
    xslt_free($parser);

    how about xsl as normal stylsheets from view.yml?
    <?xml-stylesheet type="text/xsl" href="test.xsl">
    */
  }
}
