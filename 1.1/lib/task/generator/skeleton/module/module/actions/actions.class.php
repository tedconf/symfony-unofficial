<?php

/**
 * ##MODULE_NAME## actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage ##MODULE_NAME##
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id$
 */
class ##MODULE_NAME##Actions extends sfActions
{
  /**
   * Executes index action
   *
   * @param  sfRequest $request The current sfRequest object
   *
   * @return mixed     A string containing the view name associated with this action
   */
  public function executeIndex($request)
  {
    return sfView::SUCCESS;
  }
}
