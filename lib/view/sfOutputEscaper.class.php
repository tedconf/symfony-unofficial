<?php

/**
 * Abstract class that provides an interface for escaping of output.
 *
 * @package symfony
 * @subpackage view
 * @author Mike Squire <mike@somosis.co.uk>
 */
abstract class sfOutputEscaper
{

  /**
   * Decorates a PHP variable with something that will escape any data obtained
   * from it.
   *
   * The following cases are dealt with:
   *
   *    - The value is null or false: null or false is returned.
   *    - The value is scalar: the result of applying the escaping method is
   *      returned.
   *    - The value is an array or an object that implements the ArrayAccess
   *      interface: the array is decorated such that accesses to elements yield
   *      an escaped value.
   *    - The value implements the Traversable interface (either an Iterator, an
   *      IteratorAggregate or an internal PHP class that implements
   *      Traversable): decorated much like the array.
   *    - The value is another type of object: decorated such that the result of
   *      method calls is escaped.
   *
   * The escaping method is actually the name of a PHP function. There are a set
   * of standard escaping methods listed in the escaping helper
   * (sfEscapingHelper.php).
   *
   * @param string $escapingMethod the escaping method (a PHP function) to apply
   *    to the value
   * @param mixed $value the value to escape
   * @param mixed the escaped value
   */
  public static function escape($escapingMethod, $value)
  {
    if (is_null($value) || ($value === false)) {
      return $value;
    }

    // Scalars are anything other than arrays, objects and resources.
    if (is_scalar($value))
    {
      return call_user_func($escapingMethod, $value);
    }

    if (is_array($value))
    {
      return new sfOutputEscaperArrayDecorator($escapingMethod, $value);
    }

    if (is_object($value))
    {
      if ($value instanceof Traversable)
      {
        return new sfOutputEscaperIteratorDecorator($escapingMethod, $value);
      }
      else
      {
        return new sfOutputEscaperObjectDecorator($escapingMethod, $value);
      }
    }

    // It must be a resource; cannot escape that.
    throw new sfException('unable to escape value \'' . print_r($value, true) . '\'');
  }

  /**
   * Constructor stores the escaping method and value.
   *
   * Since sfOutputEscaper is an abstract class, instances cannot be created
   * directly but the constructor will be inherited by sub-classes.
   */
  public function __construct($escapingMethod, $value)
  {
    $this->value          = $value;
    $this->escapingMethod = $escapingMethod;
  }

  /**
   * The value that is to be escaped.
   *
   * @var mixed
   */
  protected $value;

  /**
   * The escaping method that is going to be applied to the value and its
   * children. This is actually the name of a PHP function.
   *
   * @var string
   */
  protected $escapingMethod;

}

?>
