<?php

/**
 * Abstract output escaping decorator class for "getter" objects.
 *
 * @package symfony
 * @subpackage view
 * @author Mike Squire <mike@somosis.co.uk>
 */
abstract class sfOutputEscaperGetterDecorator extends sfOutputEscaper
{

  /**
   * Returns the raw, unescaped value associated with the key supplied.
   *
   * The key might be an index into an array or a value to be passed to the 
   * decorated object's get() method.
   *
   * @param string $key the key to retrieve
   * @return mixed the value
   */
  public abstract function getDirty($key);

  /**
   * Returns the escaped value associated with the key supplied.
   *
   * Typically (using this implementation) the raw value is obtained using the
   * {@link getDirty()} method, escaped and the result returned.
   *
   * @param string $key the key to retieve
   * @param string $escapingMethod the escaping method (a PHP function) to use
   * @return mixed the escaped value
   */
  public function get($key, $escapingMethod = null)
  {
    if (! $escapingMethod)
    {
      $escapingMethod = $this->escapingMethod;
    }

    return sfOutputEscaper::escape($escapingMethod, $this->getDirty($key));
  }

}

?>
