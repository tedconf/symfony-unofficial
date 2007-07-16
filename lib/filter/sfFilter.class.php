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
 * sfFilter provides a way for you to intercept incoming requests or outgoing responses.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfFilter
{
  /**
   * Filter is executed for the whole request.
   */
  const REQUEST = 1;

  /**
   * Filter is executed for an action.
   */
  const ACTION  = 2;

  /**
   * filter categories
   */
  const INPUT   = 1;
  const PROCESS = 2;
  const OUTPUT  = 3;

  protected
    $parameterHolder = null,
    $context         = null;

  public static
    $filterCalled    = array();

  /**
   * Returns true if this is the first call to the sfFilter instance.
   *
   * @return boolean true if this is the first call to the sfFilter instance, false otherwise
   */
  protected function isFirstCall()
  {
    $class = get_class($this);
    if (isset(self::$filterCalled[$class]))
    {
      return false;
    }
    else
    {
      self::$filterCalled[$class] = true;

      return true;
    }
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The current sfContext instance
   */
  public final function getContext()
  {
    return $this->context;
  }

  /**
   * Sets the type of this filter.
   *
   * @param integer One of the constants sfFilter::REQUEST, sfFilter::ACTION
   */
  public final function setType($type)
  {
    if ($type !== self::REQUEST && $type !== self::ACTION)
    {
      throw new sfFilterException('Invalid filter type.');
    }

    $this->setParameter('type', $type);
  }

  /**
   * Returns the type of this filter.
   *
   * @return integer one of the constants sfFilter::REQUEST, sfFilter::ACTION
   */
  public final function getType()
  {
    return $this->getParameter('type', self::REQUEST);
  }

  /**
   * Initializes this Filter.
   *
   * @param sfContext The current application context
   * @param array   An associative array of initialization parameters
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Filter
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    $type = $this->parameterHolder->get('type', self::REQUEST);
    $this->setType($type);

    return true;
  }

  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance
   */
  abstract public function execute($filterChain);

  /**
   * Returns true if this filter must not be executed.
   *
   * Override this method to check for certain conditions.
   *
   * @return boolean true if this filter must not be executed, false if it should
   */
  public function skip()
  {
    return false;
  }


  /**
   * Gets the parameter holder for this object.
   *
   * @return sfParameterHolder A sfParameterHolder instance
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->get()</code>
   *
   * @param string The key name
   * @param string The default value
   * @param string The namespace to use
   *
   * @return string The value associated with the key
   *
   * @see sfParameterHolder
   */
  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  /**
   * Returns true if the given key exists in the parameter holder.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->has()</code>
   *
   * @param string The key name
   * @param string The namespace to use
   *
   * @return boolean true if the given key exists, false otherwise
   *
   * @see sfParameterHolder
   */
  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  /**
   * Sets the value for the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->set()</code>
   *
   * @param string The key name
   * @param string The value
   * @param string The namespace to use
   *
   * @see sfParameterHolder
   */
  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameterHolder->set($name, $value, $ns);
  }
}
