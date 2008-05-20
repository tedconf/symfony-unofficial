<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCommonFilter automatically adds javascripts and stylesheets information in the sfResponse content.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommonFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    $response = $this->context->getResponse();

    /** don't add common html elements for:
     * for XHR requests
     * if content type not html
     * if 304
     * if not rendering to the client
     * if HTTP headers only
     */
    if($this->context->getRequest()->isXmlHttpRequest() ||
       strpos($response->getContentType(), 'html') === false ||
       $response->getStatusCode() == 304 ||
       $this->context->getController()->getRenderMode() != sfView::RENDER_CLIENT ||
       $response->isHeaderOnly()
    )
    {
      return;
    }

    // include javascripts and stylesheets
    $content = $response->getContent();
    if(false !== ($pos = strpos($content, '</head>')))
    {
      sfLoader::loadHelpers(array('Tag', 'Asset'));
      $html = '';
      if (!sfConfig::get('symfony.asset.javascripts_included', false))
      {
        $html .= get_javascripts($response);
      }
      if (!sfConfig::get('symfony.asset.stylesheets_included', false))
      {
        $html .= get_stylesheets($response);
      }

      if ($html)
      {
        $response->setContent(substr($content, 0, $pos).$html.substr($content, $pos));
      }
    }

    sfConfig::set('symfony.asset.javascripts_included', false);
    sfConfig::set('symfony.asset.stylesheets_included', false);
  }
}
