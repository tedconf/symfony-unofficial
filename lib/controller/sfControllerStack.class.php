<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfControllerStack keeps a list of entries providing contextual information
 * about all requested actions and provides accessor methods for retrieving
 * individual entries.
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
   * Adds an entry to the stack.
   *
   * @param mixed  Either a sfParameterHolder instance or an array of parameters.
   *               If an array is passed an sfParameterHolder instance is automatically created.
   * @param string Classname to be used to create the entry if an array was set.
   *
   * @return sfParameterHolder sfParameterHolder instance.
   */
  public function push($entryHolder, $className = null)
  {
    if (is_object($entryHolder))
    {
      if (!$entryHolder instanceof sfParameterHolder)
      {
        throw new sfException('Parameter entryHolder must be an instance of sfParameterHolder.');
      }

      $this->stack[] = $entryHolder;

      return $entryHolder;
    }

    if (is_array($entryHolder))
    {
      $entryHolderClass = $className ? $className : sfConfig::get('sf_controller_stack_entry_classname', 'sfParameterHolder');
      $entryHolderInstance = new $entryHolderClass();
      $entryHolderInstance->add($entryHolder);

      return $this->push($entryHolderInstance);
    }

    throw new sfException('Parameter entryHolder is of invalid type. Must be either object or array.');
  }

  /**
   * Removes the last entry from the stack.
   *
   * @return sfParameterHolder A sfParameterHolder instance.
   */
  public function pop()
  {
    return array_pop($this->stack);
  }

  /**
   * Retrieves the first entry.
   *
   * @return mixed A sfParameterHolder instance or null if there is none in the stack.
   */
  public function getFirst()
  {
    return !empty($this->stack[0]) ? $this->stack[0] : null;
  }

  /**
   * Retrieves the last entry.
   *
   * @return mixed An sfParameterHolder instance or null if there is none in the stack.
   */
  public function getLast()
  {
    return end($this->stack);
  }

  /**
   * Retrieves the entry at a specific index.
   *
   * @param int An entry index.
   *
   * @return sfParameterHolder A sfParameterHolder instance.
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