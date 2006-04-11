<?php

/**
 * Output escaping object decorator that intercepts all method calls and escapes
 * their return values.
 *
 * @package symfony
 * @subpackage view
 * @author Mike Squire <mike@somosis.co.uk>
 */
class sfOutputEscaperObjectDecorator extends sfOutputEscaperGetterDecorator{

  /**
   * Magic PHP method that intercepts method calls, calls them on the objects
   * that is being escaped and escapes the result.
   *
   * The calling of the method is changed slightly to accommodate passing a
   * specific escaping strategy. An additional parameter is appended to the
   * argument list which is the escaping strategy. The decorator will remove
   * and use this parameter as the escaping strategy if it begins with 'esc_'
   * (the prefix all escaping helper functions have).
   *
   * For example if an object, $o, implements methods a() and b($arg):
   *
   *   $o->a()                // Escapes the return value of a()
   *   $o->a(ESC_DIRTY)       // Uses the escaping method ESC_DIRTY with a()
   *   $o->b('a')             // Escapes the return value of b('a')
   *   $o->b('a', ESC_DIRTY); // Uses the escaping method ESC_DIRTY with b('a')
   *
   * @param string $method the method on the object to be called
   * @param array $args an array of arguments to be passed to the method
   * @return mixed the escaped value returned by the method
   */
  public function __call($method, $args)
  {
    if (count($args) > 0)
    {
      $escapingMethod = $args[count($args) - 1];

      if (substr($escapingMethod, 0, 4) === 'esc_')
      {
        array_shift($args);
      }
      else
      {
        $escapingMethod = $this->escapingMethod;
      }
    }
    else
    {
      $escapingMethod = $this->escapingMethod;
    }

    $value = call_user_func_array(array($this->value, $method), $args);

    return sfOutputEscaper::escape($escapingMethod, $value);
  }

  /**
   * Returns the result of calling the get() method on the object, bypassing
   * any escaping, if that method exists.
   *
   * If the get() method does not exist this will throw an exception.
   *
   * @throws sfException if the object does not have a get() method
   * @param string $key the parameter to be passed to the get() get method
   * @return mixed the unescaped value returned
   */
  public function getDirty($key)
  {
    if (! is_callable(array($this->value, 'get')))
    {
      throw new sfException('object does not support have a get() method');
    }

    return $this->value->get($key);
  }

}

?>
