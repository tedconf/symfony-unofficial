<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFillInFormFilter fills in forms.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFillInFormFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    $response = $this->context->getResponse();
    $request  = $this->context->getRequest();

    if(strpos($response->getContentType(), 'html') && strpos($content, '<form')) // only run if there is an html document and a form on the page
    {
      $fillInForm = new sfFillInForm();

      // converters
      foreach ($this->getParameter('converters', array()) as $functionName => $fields)
      {
        $fillInForm->addConverter($functionName, $fields);
      }

      // skip fields
      $fillInForm->setSkipFields((array) $this->getParameter('skip_fields', array()));

      // types
      $excludeTypes = (array) $this->getParameter('exclude_types', array('hidden', 'password'));
      $checkTypes   = (array) $this->getParameter('check_types',   array('text', 'checkbox', 'radio', 'password', 'hidden'));
      $fillInForm->setTypes(array_diff($checkTypes, $excludeTypes));

      // fill in
      $method = 'fillIn'.ucfirst(strtolower($this->getParameter('content_type', 'Html')));

      try
      {
        $content = $fillInForm->$method($response->getContent(), $this->getParameter('name'), $this->getParameter('id'), $request->getParameterHolder()->getAll());
        $response->setContent($content);
      }
      catch (sfException $e)
      {
        if (sfConfig::get('sf_logging_enabled'))
        {
          $this->context->getLogger()->error(sprintf('{sfFilter} %s', $e->getMessage()));
        }
      }
    }
  }
}
