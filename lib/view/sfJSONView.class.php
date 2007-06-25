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
 * @version    SVN: $Id: sfJSONView.class.php 3864 2007-04-24 15:27:52Z fabien $
 */
class sfJSONView extends sfPHPView
{
  protected $extensions = array('.json.php', '.json', '.php');

  public function configure()
  {
    parent::configure();

    $response = $this->getContext()->getResponse();
    $response->setContentType('application/x-json');
  }

  /**
   * Encodes content to json format
   *
   * @link http://gggeek.altervista.org/sw/article_20070425.html
   *
   * @param object $content
   * @return string json output
   */
  private function json_encode($content)
  {
    if(function_exists('json_encode'))
    {
      return json_encode($content);
    }
    elseif(class_exists('Zend_Json', true) && method_exists('Zend_Json', 'encode'))
    {
      return Zend_Json::encode($content);
    }
    elseif(class_exists('Services_JSON', true) && method_exists('Services_JSON', 'encode'))
    {
      $json = new Services_JSON();
      return $json->encode($content);
    }
    elseif(function_exists('php_jsonrpc_encode'))
    {
	    $json = php_jsonrpc_encode($content);
	    return $json->serialize();
    }
    else
    {
      throw new sfViewException('You must enable json support for php.');
    }
  }
}
