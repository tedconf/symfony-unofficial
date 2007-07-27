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
 * sfControllerStack keeps a list of all requested actions and provides accessor
 * methods for retrieving individual entries.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @version    $Id$
 */
class sfControllerStack
{
  protected
    $context = null,
    $stack = array();

  public function initialize($context)
  {
    $this->context = $context;
  }

  /**
   * Adds an entry to the controller stack.
   *
   * @param mixed  Either a sfControllerEntry instance or an array of attributes.
   *               If an array is passed an sfControllerEntry instance is automatically created.
   * @param string Classname to be used to create the entry if entry is an array.
   *
   * @return sfControllerEntry sfControllerEntry instance
   */
  public function push($entry, $type = null)
  {
    if (is_object($entry))
    {
      return $this->pushObject($entry);
    }

    if (is_array($entry))
    {
      return $this->pushArray($entry, $type);
    }

    throw new sfException('Parameter entry is of invalid type. Must be either object or array.');
  }

  /**
   * Adds a sfControllerEntry to the stack.
   *
   * @param sfControllerEntry A sfControllerEntry instance.
   *
   * @return sfControllerEntry sfControllerEntry instance.
   */
  public function pushObject($entry)
  {
    if (!$entry instanceof sfControllerEntry)
    {
      throw new sfException('Parameter entry must be an instance of sfControllerEntry.');
    }

    $this->stack[] = $entry;

    return $entry;
  }

  /**
   * Adds a sfControllerEntry to the stack.
   * The entry is build using given array of attributes.
   *
   * @param array  Attributes to build the entry.
   * @param string Optional classne to use to create the entry instance.
   *
   * @return sfControllerEntry sfControllerEntry instance.
   */
  public function pushArray($attributes, $type = null)
  {
    $class = $type ? $type : sfConfig::get('sf_controller_entry_classname', 'sfControllerEntry');
    $entry = new $class($attributes);

    return $this->pushObject($entry);
  }

  /**
   * Removes the last entry from the stack.
   *
   * @return sfControllerEntry A controller entry implementation.
   */
  public function pop()
  {
    return array_pop($this->stack);
  }

  /**
   * Retrieves the first entry.
   *
   * @return mixed A controller entry implementation or null if there is none in the stack.
   */
  public function getFirst()
  {
    return !empty($this->stack[0]) ? $this->stack[0] : null;
  }

  /**
   * Retrieves the last entry.
   *
   * @return mixed An action stack entry implementation or null if there is no sfAction instance in the stack
   */
  public function getLast()
  {
    return end($this->stack);
  }

  /**
   * Retrieves the entry at a specific index.
   *
   * @param int An entry index
   *
   * @return sfControllerEntry A controller entry implementation.
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