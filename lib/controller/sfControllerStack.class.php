<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfControllerStack keeps a list of all requested actions and provides accessor
 * methods for retrieving individual contexts.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @version    $Id$
 */
class sfControllerStack implements Countable
{
  protected $stack = array();

  /**
   * Adds a context to the controller stack.
   *
   * @param mixed  Either a sfControllerContext instance or an array of attributes.
   *               If an array is passed an sfControllerContext instance is automatically created.
   * @param string Classname to be used to create the context if context is an array.
   *
   * @return sfControllerContext sfControllerContext instance
   */
  public function push($context, $className = null)
  {
    if (is_object($context))
    {
      if (!$context instanceof sfControllerContext)
      {
        throw new sfException('Parameter context must be an instance of sfControllerContext.');
      }

      $this->stack[] = $context;

      return $context;
    }

    if (is_array($context))
    {
      $contextClass = $className ? $className : sfConfig::get('sf_controller_context_classname', 'sfControllerContext');
      $contextInstance = new $contextClass($context);

      return $this->push($contextInstance);
    }

    throw new sfException('Parameter context is of invalid type. Must be either object or array.');
  }

  /**
   * Removes the last context from the stack.
   *
   * @return sfControllerContext A controller context implementation.
   */
  public function pop()
  {
    return array_pop($this->stack);
  }

  /**
   * Retrieves the first context.
   *
   * @return mixed A controller context implementation or null if there is none in the stack.
   */
  public function getFirst()
  {
    return !empty($this->stack[0]) ? $this->stack[0] : null;
  }

  /**
   * Retrieves the last context.
   *
   * @return mixed An action stack context implementation or null if there is no sfAction instance in the stack
   */
  public function getLast()
  {
    return end($this->stack);
  }

  /**
   * Retrieves the context at a specific index.
   *
   * @param int An context index
   *
   * @return sfControllerContext A controller context implementation.
   */
  public function getAt($index)
  {
    $retval = null;

    if ($index > -1 && $index < count($this->stack))
    {
      $retval = $this->stack[$index];
    }

    return $retval;
  }

  /**
   * Retrieves the size of this stack.
   *
   * @return int The size of this stack.
   */
  public function count()
  {
    return count($this->stack);
  }
}