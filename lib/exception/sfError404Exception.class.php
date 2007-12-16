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
   * Forwards to the 404 action.
   */
  public function printStackTrace()
  {
    // log all exceptions in php log
    $exception = is_null($this->wrappedException) ? $this : $this->wrappedException;
    error_log($exception->getMessage());

    if (sfConfig::get('sf_debug'))
    {
      sfContext::getInstance()->getResponse()->setStatusCode(404);

      return parent::printStackTrace();
    }
    else
    {
      $context = sfContext::getInstance();

      $context->getController()->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
      $context->getResponse()->send();
    }
  }
}
