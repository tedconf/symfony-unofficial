<?php

namespace Symfony\Components\DependencyInjection;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * A BuilderConfiguration is a consistent set of definitions and parameters.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: Definition.php 269 2009-03-26 20:39:16Z fabien $
 */
class BuilderConfiguration
{
  protected $definitions = array();
  protected $parameters  = array();
  protected $aliases     = array();

  public function __construct(array $definitions = array(), array $parameters = array())
  {
    $this->setDefinitions($definitions);
    $this->setParameters($parameters);
  }

  /**
   * Merges a BuilderConfiguration with the current one.
   *
   * @param BuilderConfiguration $configuration
   */
  public function merge(BuilderConfiguration $configuration = null)
  {
    if (null === $configuration)
    {
      return;
    }

    $this->addDefinitions($configuration->getDefinitions());
    $this->addAliases($configuration->getAliases());
    $this->addParameters($configuration->getParameters());
  }

  /**
   * Sets the service container parameters.
   *
   * @param array $parameters An array of parameters
   */
  public function setParameters(array $parameters)
  {
    $this->parameters = array();
    foreach ($parameters as $key => $value)
    {
      $this->parameters[strtolower($key)] = $value;
    }
  }

  /**
   * Adds parameters to the service container parameters.
   *
   * @param array $parameters An array of parameters
   */
  public function addParameters(array $parameters)
  {
    $this->setParameters(array_merge($this->parameters, $parameters));
  }

  /**
   * Gets the service container parameters.
   *
   * @return array An array of parameters
   */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Sets a service container parameter.
   *
   * @param string $name       The parameter name
   * @param mixed  $parameters The parameter value
   */
  public function setParameter($name, $value)
  {
    $this->parameters[strtolower($name)] = $value;
  }

  /**
   * Sets an alias for an existing service.
   *
   * @param string $alias The alias to create
   * @param string $id    The service to alias
   */
  public function setAlias($alias, $id)
  {
    $this->aliases[$alias] = $id;
  }

  /**
   * Adds definition aliases.
   *
   * @param array $aliases An array of aliases
   */
  public function addAliases(array $aliases)
  {
    foreach ($aliases as $alias => $id)
    {
      $this->setAlias($alias, $id);
    }
  }

  /**
   * Gets all defined aliases.
   *
   * @return array An array of aliases
   */
  public function getAliases()
  {
    return $this->aliases;
  }

  /**
   * Sets a definition.
   *
   * @param  string     $id         The identifier
   * @param  Definition $definition A Definition instance
   */
  public function setDefinition($id, Definition $definition)
  {
    unset($this->aliases[$id]);

    return $this->definitions[$id] = $definition;
  }

  /**
   * Adds the definitions.
   *
   * @param array $definitions An array of definitions
   */
  public function addDefinitions(array $definitions)
  {
    foreach ($definitions as $id => $definition)
    {
      $this->setDefinition($id, $definition);
    }
  }

  /**
   * Sets the definitions.
   *
   * @param array $definitions An array of definitions
   */
  public function setDefinitions(array $definitions)
  {
    $this->definitions = array();
    $this->addDefinitions($definitions);
  }

  /**
   * Gets all definitions.
   *
   * @return array An array of Definition instances
   */
  public function getDefinitions()
  {
    return $this->definitions;
  }
}
