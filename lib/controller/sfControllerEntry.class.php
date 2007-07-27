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
 * sfControllerEntry represents information relating to a single request
 * during a single HTTP request.
 *
 * @package    symfony
 * @subpackage controller
 * @author     Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @version    SVN: $Id$
 */
class sfControllerEntry implements ArrayAccess
{
  /**
   * Holds all attributes associated with this entry.
   *
   * @var array
   */
  protected $attributes = array();

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
   * Gets the attribute associated with the given key.
   *
   * @param string The key name
   * @param string The default value
   *
   * @return string The value associated with the key
   */
  public function get($name, $default = null)
  {
    return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
  }

  /**
   * Sets the value for the given key.
   *
   * @param string The key name
   * @param string The value
   *
   * @return void
   */
  public function set($name, $value)
  {
    $this->attributes[$name] = $value;
  }

  /**
   * Removes an attribute from the entry.
   *
   * @param string Name of the attribute to remove.
   *
   * @return void
   */
  public function remove($name)
  {
    unset($this->attributes[$name]);
  }

  /**
   * Returns true if the given key exists in the entry.
   *
   * @param string The key name
   * @param string The namespace to use
   *
   * @return boolean true if the given key exists, false otherwise
   */
  public function has($name)
  {
    return isset($this->attributes[$name]);
  }

  /**
   * Adds an array of attributes to the entry.
   *
   * @param array An array of key => value pairs.
   *
   * @return void
   */
  public function add($attributes)
  {
    $this->attributes = array_merge($this->attributes, $attributes);
  }


  // ArrayAccess implementation ------------------------------------------

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