<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
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
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfBasicSecurityFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    $controller = $this->context->getController();
    $request    = $this->context->getRequest();

    // get the current action instance
    $actionEntry    = $controller->getActionStack()->getLastEntry();
    $actionInstance = $actionEntry->getActionInstance();

    // only redirect if not posting and we actually have an http(s) request
    if(!$request->isMethod('post') && substr($request->getUri(), 0, 4) == 'http')
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

    // disable security on login and secure actions
    if ((sfConfig::get('sf_login_module') == $this->context->getModuleName()) && (sfConfig::get('sf_login_action') == $this->context->getActionName())
        ||
        (sfConfig::get('sf_secure_module') == $this->context->getModuleName()) && (sfConfig::get('sf_secure_action') == $this->context->getActionName()))
    {
      $filterChain->execute();

      return;
    }

    // does this action require security?
    if($actionInstance->isSecure())
    {
      // NOTE: the nice thing about the Action class is that getCredential()
      //       is vague enough to describe any level of security and can be
      //       used to retrieve such data and should never have to be altered
      if (!$this->context->getUser()->isAuthenticated())
      {
        // the user is not authenticated
        $this->forwardToLoginAction();
      }

      // the user is authenticated
      $credential = $this->getUserCredential();
      if (!is_null($credential) && !$this->context->getUser()->hasCredential($credential))
      {
        // the user doesn't have access
        $this->forwardToSecureAction();
      }
    }

    // the user has access, continue
    $filterChain->execute();
  }

  /**
   * Forwards the current request to the secure action.
   *
   * @throws sfStopException
   */
  protected function forwardToSecureAction()
  {
    $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

    throw new sfStopException();
  }

  /**
   * Forwards the current request to the login action.
   *
   * @throws sfStopException
   */
  protected function forwardToLoginAction()
  {
    $this->context->getController()->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));

    throw new sfStopException();
  }

  /**
   * Returns the credential required for this action.
   *
   * @return mixed The credential required for this action
   */
  protected function getUserCredential()
  {
    return $this->context->getController()->getActionStack()->getLastEntry()->getActionInstance()->getCredential();
  }
}
