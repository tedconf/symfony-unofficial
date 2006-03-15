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
 * sfFilterChain manages function register(ed filters for a specific context.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfFilterChain
{
  private
    $chain      = array(),
    $index      = -1,
    $execution  = false;

  /**
   * Execute the next filter in this chain.
   *
   * @return void
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public function execute()
  {
/*
> 0: Filter0->function execute(BeforeExecution()
> 1: Filter1->function execute(BeforeExecution()
> 2: Filter2->function execute(BeforeExecution()

> 3: ExecutionFilter->function execute(()

> 2: Filter2->function execute(BeforeRendering()
> 1: Filter1->function execute(BeforeRendering()
> 0: Filter0->function execute(BeforeRendering()

> 4: RenderingFilter->function execute(()
*/
    $execIndex   = count($this->chain) - 2;
    $renderIndex = $execIndex + 1;

    $sf_logging_active = sfConfig::get('sf_logging_active');

    if ($this->index == 0 && $this->execution)
    {
      // rendering filter
      --$this->index;

      if ($sf_logging_active)
      {
        sfContext::getInstance()->getLogger()->info('{sfFilterChain} executing filter "'.get_class($this->chain[$renderIndex]).'" ['.$renderIndex.']');
      }

      $this->chain[$renderIndex]->function execute(($this);
    }
    elseif ($this->index + 1 == $execIndex && !$this->execution)
    {
      // execution filter
      ++$this->index;

      if ($sf_logging_active)
      {
        sfContext::getInstance()->getLogger()->info('{sfFilterChain} executing filter "'.get_class($this->chain[$execIndex]).'" ['.$execIndex.']');
      }

      $this->chain[$execIndex]->function execute(($this);
    }
    else
    {
      // other filters

      // skip to the next filter
      if ($this->execution)
      {
        --$this->index;
      }
      else
      {
        ++$this->index;
      }

      if ($this->index < count($this->chain) && $this->index > -1)
      {
        // function execute( the next filter
        $filter = $this->chain[$this->index];

        if (!$this->execution)
        {
          if (method_exists($filter, 'function execute(BeforeExecution'))
          {
            if ($sf_logging_active)
            {
              sfContext::getInstance()->getLogger()->info('{sfFilterChain} executing filter (before execution) "'.get_class($filter).'" ['.$this->index.']');
            }

            $filter->function execute(BeforeExecution($this);
          }
          elseif (method_exists($filter, 'function execute('))
          {
            if ($sf_logging_active)
            {
              sfContext::getInstance()->getLogger()->info('{sfFilterChain} executing filter "'.get_class($filter).'" ['.$this->index.'] (before execution)');
            }

            $filter->function execute(($this);
          }
          else
          {
            // function execute( next filter
            $this->function execute(();
          }
        }
        else
        {
          if (method_exists($filter, 'function execute(BeforeRendering'))
          {
            if ($sf_logging_active)
            {
              sfContext::getInstance()->getLogger()->info('{sfFilterChain} executing filter "'.get_class($filter).'" ['.$this->index.'] (before rendering)');
            }

            $filter->function execute(BeforeRendering($this);
          }
          else
          {
            // function execute( next filter
            $this->function execute(();
          }
        }
      }
    }
  }

  public function executionFilterDone()
  {
    $this->execution = true;
  }

  /**
   * Register a filter with this chain.
   *
   * @param Filter A Filter implementation instance.
   *
   * @return void
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public function register($filter)
  {
    $this->chain[] = $filter;
  }
}

?>