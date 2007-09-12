<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfBasicSecurityFilter checks security by calling the getCredential() method
 * of the action. Once the credential has been acquired, sfBasicSecurityFilter
 * verifies the user has the same credential by calling the hasCredential()
 * method of SecurityUser.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfBasicSecurityFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // get the cool stuff
    $controller = $this->context->getController();
    $user       = $this->context->getUser();
    $request    = $this->context->getRequest();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // only redirect if not posting and we actually have an http(s) request
    if($request->getMethod() != sfRequest::POST && substr($request->getUri(), 0, 4) == 'http')
    {
      // request is SSL secured
      if($request->isSecure())
      {
        // but SSL is not allowed
        if (!$actionInstance->isSslAllowed())
        {
          $controller->redirect($actionInstance->getUrl());
        }
      }
      elseif ($actionInstance->isSslRequired()) // request is not SSL secured, but SSL is required
      {
        $controller->redirect($actionInstance->getSslUrl());
      }
    }

    // disable security on [sf_login_module] / [sf_login_action]
    if (
      (sfConfig::get('sf_login_module') == $this->context->getModuleName()) && (sfConfig::get('sf_login_action') == $this->context->getActionName())
      ||
      (sfConfig::get('sf_secure_module') == $this->context->getModuleName()) && (sfConfig::get('sf_secure_action') == $this->context->getActionName())
    )
    {
      $filterChain->execute();

      return;
    }

    // get the credential required for this action
    $credential = $actionInstance->getCredential();

    // for this filter, the credentials are a simple privilege array
    // where the first index is the privilege name and the second index
    // is the privilege namespace
    //
    // NOTE: the nice thing about the Action class is that getCredential()
    //       is vague enough to describe any level of security and can be
    //       used to retrieve such data and should never have to be altered
    if ($user->isAuthenticated())
    {
      // the user is authenticated
      if ($credential === null || $user->hasCredential($credential))
      {
        // the user has access, continue
        $filterChain->execute();
      }
      else
      {
        // the user doesn't have access, exit stage left
        $controller->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
      }
    }
    else
    {
      // the user is not authenticated
      $controller->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

      throw new sfStopException();
    }
  }
}
