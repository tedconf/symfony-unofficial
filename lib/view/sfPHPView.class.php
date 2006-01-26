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
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfPHPView extends sfView
{
  public function execute()
  {
  }

  protected function renderFile($file)
  {
    $this->attribute_holder->add($this->getGlobalVars());
    $this->attribute_holder->add($this->getModuleVars());

    extract($this->attribute_holder->getAll());

    $this->loadCoreAndStandardHelpers();

    // render to variable
    ob_start();
    ob_implicit_flush(0);
    require($file);
    $retval = ob_get_clean();

    return $retval;
  }

  /**
   * Retrieve the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function &getEngine()
  {
    return null;
  }

  /**
   * Loop through all template slots and fill them in with the results of
   * presentation data.
   *
   * @param string A chunk of decorator content.
   *
   * @return string A decorated template.
   */
  protected function &decorate(&$content)
  {
    $template = $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate();

    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info('{sfPHPView} decorate content with "'.$template.'"');
    }

    // call our parent decorate() method
    parent::decorate($content);

    // render the decorator template and return the result
    $retval = $this->renderFile($template);

    return $retval;
  }

  /**
   * Render the presentation.
   *
   * When the controller render mode is sfView::RENDER_CLIENT, this method will
   * render the presentation directly to the client and null will be returned.
   *
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise null.
   */
  public function &render()
  {
    $template         = $this->getDirectory().'/'.$this->getTemplate();
    $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
    $actionInstance   = $actionStackEntry->getActionInstance();

    $moduleName = $actionInstance->getModuleName();
    $actionName = $actionInstance->getActionName();

    $retval = null;

    // execute pre-render check
    $this->preRenderCheck();

    // get the render mode
    $mode = $this->getContext()->getController()->getRenderMode();

    if ($mode != sfView::RENDER_NONE)
    {
      if ($sf_logging_active = sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfPHPView} render "'.$template.'"');
      }

      $retval = $this->getCacheContent();

      // render template if no cache
      if ($retval === null)
      {
        $retval = $this->renderFile($template);

        // tidy our cache content
        if (sfConfig::get('sf_tidy'))
        {
          $retval = sfTidy::tidy($retval, $template);
        }

        $retval = $this->setCacheContent($retval);
      }

      // now render decorator template, if one exists
      if ($this->isDecorator())
      {
        $retval =& $this->decorate($retval);
      }

      // render to client
      if ($mode == sfView::RENDER_CLIENT)
      {
        if ($sf_logging_active)
        {
          $this->getContext()->getLogger()->info('{sfPHPView} render to client');
        }

        $retval = $this->setPageCacheContent($retval);

        $this->getContext()->getResponse()->setContent($retval);

        $retval = null;
      }
    }

    return $retval;
  }
}

?>