<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfControllerContext represents information relating to a single request
 * during a single HTTP request.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @version    SVN: $Id$
 */
class sfControllerContext extends sfParameterHolder implements ArrayAccess
{
  /**
   * Constructs an instance of an entry.
   *
   * @param array  Optional array of attributes to add to this entry.
   *
   * @return void
   */
  public function __construct($attributes = array())
  {
    if (is_array($attributes) && !empty($attributes))
    {
      $this->add($attributes);
    }
  }

  /**
   * Alias for has()
   *
   * @param mixed $offset
   *
   * @return boolean True if the given key exists, false otherwise.
   * @see has()
   */
  public function offsetExists($offset)
  {
    return $this->has($offset);
  }

  /**
   * An alias for get()
   *
   * @param mixed $offset
   *
   * @return mixed
   * @see get()
   */
  public function offsetGet($offset)
  {
    return $this->get($offset);
  }

  /**
   * Alias for set()
   *
   * @param mixed $offset
   * @param mixed $value
   *
   * @return void
   * @see set()
   */
  public function offsetSet($offset, $value)
  {
    $this->set($offset, $value);
  }

  /**
   * Alias for remove()
   *
   * @param mixed $offset
   * @see remove()
   */
  public function offsetUnset($offset)
  {
    return $this->remove($offset);
  }
}