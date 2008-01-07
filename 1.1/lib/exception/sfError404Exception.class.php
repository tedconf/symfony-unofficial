<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfError404Exception is thrown when a 404 error occurs in an action.
 *
 * @package    symfony
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfError404Exception extends sfException
{
  /**
   * @see sfException
   */
  public function asResponse()
  {
    $exception = is_null($this->wrappedException) ? $this : $this->wrappedException;

    if (sfConfig::get('sf_debug'))
    {
      $response = sfContext::getInstance()->getResponse();
      if (is_null($response))
      {
        $response = new sfWebResponse(sfContext::getInstance()->getEventDispatcher());
        sfContext::getInstance()->setResponse($response);
      }

      $response->setStatusCode(404);

      return parent::printStackTrace();
    }
    else
    {
      // log all exceptions in php log
      if (!sfConfig::get('sf_test'))
      {
        error_log($this->getMessage());
      }

      $context = sfContext::getInstance();
      $context->getController()->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));

      $response = $context->getResponse();
    }

    return $response;
  }
}
