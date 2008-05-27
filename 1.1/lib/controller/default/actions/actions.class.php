<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * defaultActions module.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class defaultActions extends sfActions
{
  /**
   * Congratulations page for creating an application
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeIndex($request)
  {
    return sfView::SUCCESS;
  }

  /**
   * Congratulations page for creating a module
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeModule($request)
  {
    return sfView::SUCCESS;
  }

  /**
   * Error page for page not found (404) error
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeError404($request)
  {
    return sfView::SUCCESS;
  }

  /**
   * Warning page for restricted area - requires login
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeSecure($request)
  {
    return sfView::SUCCESS;
  }

  /**
   * Warning page for restricted area - requires credentials
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeLogin($request)
  {
    return sfView::SUCCESS;
  }

  /**
   * Module disabled in settings.yml
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeDisabled($request)
  {
    return sfView::SUCCESS;
  }
}
