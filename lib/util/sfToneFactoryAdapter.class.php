<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * NOTE: Symfony tone support is based on
 * Garden (http://garden.tigris.org), Copyright 2006 Tomas Varaneckas
 * which is originally licensed under the Apache License, Version 2.0
 * You may obtain a copy of that license at http://www.apache.org/licenses/LICENSE-2.0
 *
 * However, by totally refactoring it to symfony needs it is now shipped
 * with symfony under the license you can find in the LICENSE
 * file that was distributed with this source code.
 *
 * If you have any questions about the symfony tone implementation,
 * please consider to not contact the original author of Garden
 * but ask on the symfony mailing list.
 */

/**
 * This is the built in symfony tone factory.
 *
 * @author Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @author Tomas Varaneckas <tomas [dot] varaneckas [at] gmail [dot] com>
 */
class sfToneFactoryAdapter implements sfToneFactory
{
  /**
   * Array of ToneDefinition objects, which are available through this factory
   *
   * @var Array of ToneDefinition objects
   */
  protected $toneDefinitions = array();

  /**
   * Holds the singleton instances
   *
   * @var Array of mixed objects
   */
  protected $singletons = array();

  /**
   * Holds the clones
   *
   * @var Array of mixed objects
   */
  protected $prototypes = array();

  /**
   * Holds the names of abstract classes
   *
   * @var Array of String
   */
  protected $abstracts = array();

  /**
   * Holds tone aliases
   *
   * @var Array of String
   */
  protected $aliases = array();

  /**
   * Holds the locked tone names
   *
   * @var Array of String
   */
  protected $locked = array();

  /**
   * Holds special argument names
   *
   * @var Array of String
   */
  protected $reservedArgs = array('Tone');

  /**
   * Initializes the factory.
   */
  public function initialize()
  {
    $toneDefinitions = require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/tone.yml'));
    $this->setToneDefinitions($toneDefinitions);
  }

  /**
   * A setter for tone definitions
   *
   * @param  Array of tone definitions
   * @throws sfException if abused
   */
  protected function setToneDefinitions($toneDefinitions)
  {
    if (!is_array($toneDefinitions))
    {
      throw new sfException('Tone definitions must be of type array');
    }

    $this->toneDefinitions = $toneDefinitions;
    $this->preloadTones();
  }

  /**
   * Retrieves a ready to use Tone.
   *
   * @param  string Tone id or alias
   * @return mixed Tone object
   * @throws sfException on failure
   */
  public function getTone($name)
  {
    $name = $this->getAlias($name);

    if (!empty($this->singletons[$name]))
    {
      return $this->singletons[$name];
    }

    if (!empty($this->prototypes[$name]))
    {
      return clone $this->prototypes[$name];
    }

    if (!empty($this->abstracts[$name]))
    {
      throw new sfException('Cannot get abstract tone ' . $name);
    }

    // at this point we know that tone is not instantiated yet.
    // it may either not exist, or is lazy, so let's try instantiating it
    $definition = $this->getToneDefinition($name);
    $this->instantiateTone($definition);

    // recursively jump back after instantiation attempt. if it fails,
    // exception will break the possible loop..
    return $this->getTone($name);
  }

  /**
   * A verification method to see if tone definition exists in the factory
   *
   * @param  string tone id or alias
   * @return boolean
   */
  public function containsTone($name)
  {
    return !empty($this->toneDefinitions[$this->getAlias($name)]);
  }

  /**
   * A getter for Tone aliases
   *
   * Should return an array of all possible Tone names.
   *
   * @param  string Tone id or alias
   * @return Array of string
   */
  public function getAliases($name)
  {
    if (!$this->containsTone($name))
    {
      throw new sfException('No tone or alias found for ' . $name);
    }

    $realName = $this->getAlias($name);
    $aliases = array($realName);
    foreach ($this->aliases as $alias => $id)
    {
      if ($id == $realName)
      {
        $aliases[] = $alias;
      }
    }

    return $aliases;
  }

  /**
   * Will tell if a Tone is singleton or not
   *
   * @param string Tone id or alias
   * @return boolean
   */
  public function isSingleton($name)
  {
    $definition = $this->getToneDefinition($this->getAlias($name));
    return $definition['singleton'];
  }

  /**
   * Will remove Tone instance from the factory
   *
   * This also includes removal of aliases and definition, and the execution
   * of destroy methods, if such will be defined.
   *
   * @param string Tone id or alias
   */
  public function removeTone($name)
  {
  }

  /**
   * Remove all instantiated tones.
   *
   */
  public function shutdown()
  {
  }


  // private stuff

  protected function getToneDefinition($name)
  {
    if (!$this->containsTone($name))
    {
      throw new sfException('Tone ' . $name . ' is not registered.');
    }

    return $this->toneDefinitions[$name];
  }

  protected function instantiateTone($name)
  {
  }

  /**
   * Creates a new object an does constructor injection is if necessary.
   *
   * @param array Tone definition
   */
  protected function createTone($definition)
  {
    $class = $definition['class'];
    if (!class_exists($class))
    {
      throw sfException("Class '" . $definition['class'] . "' does not exist.");
    }

    $args = $this->evaluateConstructorArgs($definition);

    switch (count($args))
    {
      case 0:
        $tone = new $class();
        break;
      case 1:
        $tone = new $class($args[0]);
        break;
      case 2:
        $tone = new $class($args[0], $args[1]);
        break;
      case 3:
        $tone = new $class($args[0], $args[1], $args[2]);
        break;
      case 4:
        $tone = new $class($args[0], $args[1], $args[2], $args[3]);
        break;
      case 5:
        $tone = new $class($args[0], $args[1], $args[2], $args[3], $args[4]);
        break;
      case 6:
        $tone = new $class($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        break;
      case 7:
        $tone = new $class($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
        break;
      default:
        throw new sfException('Tone constructors can only have up to 7 parameters.');
        break;
    }

    if (!is_object($tone))
    {
      throw new sfException('Failed to instantiate class ' . $class . ' for tone ' . $definition['id']);
    }

    return $tone;
  }

  protected function evaluateConstructorArgs($definition)
  {
    $constructorArgValues = array();
    foreach ($definition['constructor_args'] as $arg)
    {
      $constructorArgValues[] = $this->evaluateValue($arg);
    }
    return $constructorArgValues;
  }

  /**
   * Evaluates one single value for constructor or setter injection.
   *
   * @param mixed argument value
   */
  protected function evaluateValue($value)
  {
    if (is_scalar($value))
    {
      return $value;
    }

    if (is_array($value) && count($value) == 2 && in_array($value[0], $this->reservedArgs) && is_string($value[1]))
    {
      switch ($value[0])
      {
        case 'Tone':
          return $this->getTone($value[1]);
          break;
      }
    }

    return $value;
  }

  /**
   * Preloads tones, that the defined tone depends on
   *
   * @param array tone definition
   */
  protected function preloadDependencies($definition)
  {
    $dependencies = $definition['depends_on'];
    foreach ($dependencies as $dependency)
    {
      if (!$this->isInstantiated($dependency))
      {
        $depDefinition = $this->getToneDefinition($dependency);
        $this->instantiateTone($depDefinition);
      }
    }
  }

  /**
   * Preloads all non-lazy beans for quick access
   */
  protected function preloadTones()
  {
    foreach ($this->toneDefinitions as $id => $toneDef)
    {
      $aliases = $toneDef['aliases'];
      if (count($aliases))
      {
        foreach ($aliases as $alias)
        {
          $this->aliases[$alias] = $id;
        }
      }
      if (!$toneDef['lazy_init'] && !$this->isInstantiated($id))
      {
        $this->instantiateTone($toneDef);
      }
    }
  }

  /**
   * Gets real tone name by id or other alias
   *
   * @param  string alias
   * @return Array of String
   */
  protected function getAlias($alias)
  {
    return !empty($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
  }

  /**
   * Tells if tone is instantiated
   *
   * @param  string Tone id or alias
   * @return boolean
   */
  protected function isInstantiated($name)
  {
    $name = $this->getAlias($name);
    return (
      !empty($this->singletons[$name]) ||
      !empty($this->prototypes[$name]) ||
      !empty($this->abstracts[$name])
    );
  }

  /**
   * Reserves tone id to avoid recursive infinite loops
   *
   * @param  string Tone id
   * @throws sfException on recursion detection
   */
  protected function lockBean($id)
  {
    if (empty($this->locked[$id]))
    {
      throw new sfException("Attempted to istantiate tone {$id} which"
        . " is already in progress. please recheck your tone definition"
        . " file for recursions.");
    }
    $this->locked[$id] = true;
  }

  /**
   * Releases the lock from reserved tone
   *
   * @param string Tone id
   */
  protected function unlockTone($id)
  {
    unset($this->locked[$id]);
  }

}
