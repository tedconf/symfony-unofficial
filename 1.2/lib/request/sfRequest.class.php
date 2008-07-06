<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRequest provides methods for manipulating client request information such
 * as attributes, and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfRequest
{
  /**
   * Process validation and execution for only GET requests.
   *
   */
  const GET = 2;

  /**
   * Skip validation and execution for any request method.
   *
   */
  const NONE = 1;

  /**
   * Process validation and execution for only POST requests.
   *
   */
  const POST = 4;

  /**
   * Process validation and execution for only PUT requests.
   *
   */
  const PUT = 5;

  /**
   * Process validation and execution for only DELETE requests.
   *
   */
  const DELETE = 6;

  /**
   * Process validation and execution for only HEAD requests.
   *
   */
  const HEAD = 7;

  protected
    $errors          = array(),
    $dispatcher      = null,
    $method          = null,
    $parameterHolder = null,
    $config          = null,
    $attributeHolder = null;

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    $this->initialize($dispatcher, $parameters, $attributes);
  }

  /**
   * Initializes this sfRequest.
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param  array             $parameters  An associative array of initialization parameters
   * @param  array             $attributes  An associative array of initialization attributes
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    $this->dispatcher = $dispatcher;

    // initialize parameter and attribute holders
    $this->parameterHolder = new sfParameterHolder();
    $this->attributeHolder = new sfParameterHolder();

    $this->parameterHolder->add($parameters);
    $this->attributeHolder->add($attributes);
  }

  /**
   * Extracts parameter values from the request.
   *
   * @param  array $names  An indexed array of parameter names to extract
   *
   * @return array An associative array of parameters and their values. If
   *               a specified parameter doesn't exist an empty string will
   *               be returned for its value
   */
  public function extractParameters($names)
  {
    $array = array();

    $parameters = $this->parameterHolder->getAll();
    foreach ($parameters as $key => $value)
    {
      if (in_array($key, $names))
      {
        $array[$key] = $value;
      }
    }

    return $array;
  }

  /**
   * Retrieves this request's method.
   *
   * @return int One of the following constants:
   *             - sfRequest::GET
   *             - sfRequest::POST
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Sets the request method.
   *
   * @param int $methodCode  One of the following constants:
   *
   * - sfRequest::GET
   * - sfRequest::POST
   * - sfRequest::PUT
   * - sfRequest::DELETE
   * - sfRequest::HEAD
   *
   * @throws <b>sfException</b> - If the specified request method is invalid
   */
  public function setMethod($methodCode)
  {
    $available_methods = array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD, self::NONE);
    if (in_array($methodCode, $available_methods))
    {
      $this->method = $methodCode;

      return;
    }

    // invalid method type
    throw new sfException(sprintf('Invalid request method: %s.', $methodCode));
  }

  /**
   * Retrieves the parameters for the current request.
   *
   * @return sfParameterHolder The parameter holder
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves the attributes holder.
   *
   * @return sfParameterHolder The attribute holder
   */
  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  /**
   * Retrieves an attribute from the current request.
   *
   * @param  string $name     Attribute name
   * @param  string $default  Default attribute value
   *
   * @return mixed An attribute value
   */
  public function getAttribute($name, $default = null)
  {
    return $this->attributeHolder->get($name, $default);
  }

  /**
   * Indicates whether or not an attribute exist for the current request.
   *
   * @param  string $name  Attribute name
   *
   * @return bool true, if the attribute exists otherwise false
   */
  public function hasAttribute($name)
  {
    return $this->attributeHolder->has($name);
  }

  /**
   * Sets an attribute for the request.
   *
   * @param string $name   Attribute name
   * @param string $value  Value for the attribute
   *
   */
  public function setAttribute($name, $value)
  {
    $this->attributeHolder->set($name, $value);
  }

  /**
   * Retrieves a paramater for the current request.
   *
   * @param string $name     Parameter name
   * @param string $default  Parameter default value
   *
   */
  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Indicates whether or not a parameter exist for the current request.
   *
   * @param  string $name  Parameter name
   *
   * @return bool true, if the paramater exists otherwise false
   */
  public function hasParameter($name)
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets a parameter for the current request.
   *
   * @param string $name   Parameter name
   * @param string $value  Parameter value
   *
   */
  public function setParameter($name, $value)
  {
    $this->parameterHolder->set($name, $value);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param  string $method     The method name
   * @param  array  $arguments  The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws <b>sfException</b> if call fails
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'request.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}
