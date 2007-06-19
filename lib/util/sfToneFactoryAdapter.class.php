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
  protected $context;

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
  protected $reservedArgs = array('Tone', 'Config');

  public function setContext($context)
  {
    $this->context = $context;
  }

  /**
   * Initializes the factory.
   */
  public function initialize()
  {
    $this->logger = sfLogger::getInstance();

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
   * Removes tone from this factory.
   *
   * This action also removes tone definition, aliases and of course,
   * the instance itself. Executes destruction methods, if such were
   * defined.
   *
   * @param  string  Tone id or alias
   * @return boolean True if tone was successfully removed, false if
   *                 the tone was not instantiated at all
   */
  public function removeTone($name)
  {
    if (!$this->isInstantiated($name))
    {
      return false;
    }

    $name = $this->getAlias($name);
    $aliases = $this->getAliases($name);
    $definition = $this->getToneDefinition($name);

    if ($destroyMethod = $definition['destroy_method'])
    {
      $this->getTone($name)->{$destroyMethod}();
    }

    if ($definition['abstract'])
    {
      unset($this->abstracts[$name]);
    }
    elseif ($definition['singleton'])
    {
      unset($this->singletons[$name]);
    }
    else
    {
      unset($this->prototypes[$name]);
    }

    unset ($definition, $this->toneDefinitions[$name]);

    foreach ($aliases as $key => $alias)
    {
      if ($alias == $name)
      {
        unset($this->aliases[$key]);
      }
    }

    $this->logger->info('{sfToneFactory} successfully removed tone: ' . $name);
    return true;
  }

  /**
   * Remove all instantiated tones.
   */
  public function shutdown()
  {
    foreach ($this->toneDefinitions as $id => $definition)
    {
      $this->removeTone($id);
    }
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

  protected function instantiateTone($definition)
  {
    $tone   = null;
    $class  = $definition['class'];
    $parent = $definition['parent'];
    $name   = $definition['id'];

    $this->lockTone($name);

    //make sure all dependencies are loaded before instantiation
    $this->preloadDependencies($definition);

    if ($parent)
    {
      if (!$this->isInstantiated($parent))
      {
        $this->instantiateTone($this->getToneDefinition($this->getAlias($parent)));
      }
    }

    $this->includeToneFile($definition);

    if ($definition['abstract'])
    {
      $this->unlockTone($name);
      return $this->publishTone(null, $definition);
    }

    if ($definition['factory_method'])
    {
      $tone = $this->createToneUsingFactory($definition);
    }
    else
    {
      $tone = $this->createTone($definition);
    }
    if (!$tone)
    {
      throw new sfException('Failed to instantiate tone: ' . $name);
    }

    if ($parent)
    {
      // initialize parent first and then tone
      $parentDef = $this->getToneDefinition($this->getAlias($parent));
      $this->setToneProperties($tone, $parentDef);
      $this->initializeTone($tone, $parentDef);
      $this->setToneProperties($tone, $definition);
    }
    else
    {
      $this->setToneProperties($tone, $definition);
    }

    $this->initializeTone($tone, $definition);
    $this->unlockTone($name);
    $this->publishTone($tone, $definition);
    $this->logger->info('{sfToneFactory} successfully created tone: ' . $name);
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
      throw new sfException("Class '" . $definition['class'] . "' for tone " . $definition['id'] . ' does not exist');
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

  /**
   * Creates new object from tone definition using a factory instead of constructor
   *
   * @param array tone definition
   * @return object
   * @throws sfException on failure
   */
  protected function createToneUsingFactory($definition)
  {
    $external = true;
    $factoryToneName = $definition['factory_tone'];

    if (!$factoryToneName)
    {
      $factoryToneName = $definition['class'];
      $external = false;
    }

    if (!$factoryToneName)
    {
      throw new sfException('Tone ' . $definition['id'] . ' has no '
        . 'factory_tone or class attribute');
    }

    if ($external)
    {
      $factoryDefinition = $this->getToneDefinition($factoryToneName);

      // as factories are supposed to be static, we will simply try
      // to include factory file if it's necessary
      if (!class_exists($factoryDefinition['class']))
      {
        if ($factoryClassFile = $factoryDefinition['file'])
        {
          require_once $factoryClassFile;
        }
      }
    }

    $factoryMethod = $definition['factory_method'];
    if (!$factoryMethod)
    {
      throw new sfException('Tone ' . $definition['id'] . ' has factory tone '
        . $factoryToneName . ' defined without factory method');
    }

    if (!is_callable(array($factoryToneName, $factoryMethod)))
    {
      throw new sfException('Tone ' . $definition['id'] . ' factory not '
        . 'callable: ' . $factoryToneName . '::' . $factoryMethod . '()');
    }

    $constructorArgValues = $this->evaluateConstructorArgs($definition);
    $tone = call_user_func_array(
      array($factoryToneName, $factoryMethod), $constructorArgValues);

    if (!$tone)
    {
      throw new sfException('Failed to create tone ' . $definition['id']
        . ' using factory ' . $factoryToneName . '::' . $factoryMethod . '()');
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

  protected function evaluateProperties($properties)
  {
    $propertiesValues = array();
    foreach ($properties as $arg)
    {
      $propertiesValues[] = $this->evaluateValue($arg);
    }
    return $propertiesValues;
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
          return ($value[1] == 'Context') ? $this->context : $this->getTone($value[1]);
        case 'Config':
          return sfConfig::get($value[1]);
      }
    }

    return $value;
  }

  /**
   * Injects a fresh tone with properties
   *
   * @param Object tone instance
   * @param Array  tone definition
   */
  protected function setToneProperties($tone, $definition)
  {
    foreach ($definition['properties'] as $setter => $properties)
    {
      $this->setToneProperty($tone, $setter, $this->evaluateProperties($properties));
    }
  }

  /**
   * Convenience method for setter injection
   *
   * @param object Tone instance
   * @param string setter name
   * @param mixed  setter arguments
   */
  protected function setToneProperty($tone, $name, $properties)
  {
    $setter = 'set' . ucfirst($name);
    if (!is_callable(array($tone, $setter)))
    {
      throw new sfException('Unable to call setter method: '
        . get_class($tone) . '->' . $setter . '()');
    }
    call_user_func_array(array($tone, $setter), $properties);
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
   * Preloads all non-lazy tones for quick access
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

      $configCondition =
        !$toneDef['preload_config_condition'] ||
        sfConfig::get((string) $toneDef['preload_config_condition']);

      if (!$toneDef['lazy_init'])
      {
        if (!$configCondition)
        {
          $this->logger->info('{sfToneFactory} skipped preloading of tone: ' . $id . ', config condition does not match');
          continue;
        }

        if ($this->isInstantiated($id))
        {
          continue;
        }

        $this->logger->info('{sfToneFactory} preloading tone: ' . $id);
        $this->instantiateTone($toneDef);
      }
    }
  }

  /**
   * Tone initializer
   *
   * It calls the init method, if such is defined.
   *
   * @param  object tone instance
   * @param  array tone definition
   * @throws sfException on failure
   */
  protected function initializeTone($tone, $definition)
  {
    if ($initMethod = $definition['init_method'])
    {
      if (!is_callable(array($tone, $initMethod)))
      {
        throw new sfException('Init method for ' . $definition['id']
          . ' (' . $initMethod . ') is not callable');
      }
      call_user_func(array($tone, $initMethod));
    }
  }

  /**
   * Includes tone file, if necessary
   *
   * @param  array tone definition
   * @throws sfException on failure
   */
  protected function includeToneFile($definition)
  {
    $class = $definition['class'];
    $file = $definition['file'];

    if ($file)
    {
      require_once $file;
    }

    if ($class && $file && !class_exists($class, false))
    {
      throw new sfException('Class ' . $class . ' not found in file: ' . $file);
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
   * A method which puts freshly baked tone in place where it's ready to use
   *
   * @param  object tone instance
   * @param  array tone definition
   */
  protected function publishTone($tone, $definition)
  {
    $name = $definition['id'];

    if ($definition['abstract'])
    {
      $this->abstracts[$name] = null;
    }

    if ($definition['singleton'])
    {
      $this->singletons[$name] = $tone;
    }

    $this->prototypes[$name] = $tone;
  }

  /**
   * Reserves tone id to avoid recursive infinite loops
   *
   * @param  string Tone id
   * @throws sfException on recursion detection
   */
  protected function lockTone($id)
  {
    if (!empty($this->locked[$id]))
    {
      throw new sfException('Attempted to instantiate tone ' . $id . ' which'
        . ' is already in progress. Please check your tone definition'
        . ' file for recursions.');
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
