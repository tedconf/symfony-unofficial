<?php

namespace Symfony\Components\DependencyInjection\Loader;

use Symfony\Components\DependencyInjection\Container;
use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\BuilderConfiguration;
use Symfony\Components\YAML\YAML;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * YamlFileLoader loads YAML files service definitions.
 *
 * The YAML format does not support anonymous services (cf. the XML loader).
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfServiceContainerLoaderFileYaml.php 269 2009-03-26 20:39:16Z fabien $
 */
class YamlFileLoader extends FileLoader
{
  /**
   * Loads an array of XML files.
   *
   * @param  array $files An array of XML files
   *
   * @return array An array of definitions and parameters
   */
  public function load($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    return $this->parse($this->getFilesAsArray($files));
  }

  protected function parse($data)
  {
    $configuration = new BuilderConfiguration();

    foreach ($data as $file => $content)
    {
      // imports
      $this->parseImports($configuration, $content, $file);

      // parameters
      if (isset($content['parameters']))
      {
        foreach ($content['parameters'] as $key => $value)
        {
          $configuration->setParameter(strtolower($key), $this->resolveServices($value));
        }
      }

      // services
      $this->parseDefinitions($configuration, $content, $file);
    }

    return $configuration;
  }

  protected function parseImports(BuilderConfiguration $configuration, $content, $file)
  {
    if (!isset($content['imports']))
    {
      return;
    }

    foreach ($content['imports'] as $import)
    {
      $configuration->merge($this->parseImport($import, $file));
    }
  }

  protected function parseImport($import, $file)
  {
    if (isset($import['class']) && $import['class'] != get_class($this))
    {
      $class = $import['class'];
      $loader = new $class($this->paths);
    }
    else
    {
      $loader = $this;
    }

    $importedFile = $this->getAbsolutePath($import['resource'], dirname($file));

    return $loader->load($importedFile);
  }

  protected function parseDefinitions(BuilderConfiguration $configuration, $content, $file)
  {
    if (!isset($content['services']))
    {
      return;
    }

    foreach ($content['services'] as $id => $service)
    {
      $this->parseDefinition($configuration, $id, $service, $file);
    }
  }

  protected function parseDefinition(BuilderConfiguration $configuration, $id, $service, $file)
  {
    if (is_string($service) && 0 === strpos($service, '@'))
    {
      $configuration->setAlias($id, substr($service, 1));

      return;
    }

    $definition = new Definition($service['class']);

    if (isset($service['shared']))
    {
      $definition->setShared($service['shared']);
    }

    if (isset($service['constructor']))
    {
      $definition->setConstructor($service['constructor']);
    }

    if (isset($service['file']))
    {
      $definition->setFile($service['file']);
    }

    if (isset($service['arguments']))
    {
      $definition->setArguments($this->resolveServices($service['arguments']));
    }

    if (isset($service['configurator']))
    {
      if (is_string($service['configurator']))
      {
        $definition->setConfigurator($service['configurator']);
      }
      else
      {
        $definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
      }
    }

    if (isset($service['calls']))
    {
      foreach ($service['calls'] as $call)
      {
        $definition->addMethodCall($call[0], $this->resolveServices($call[1]));
      }
    }

    $configuration->setDefinition($id, $definition);
  }

  protected function getFilesAsArray(array $files)
  {
    $yamls = array();
    foreach ($files as $file)
    {
      $file = $this->getAbsolutePath($file);

      if (!file_exists($file))
      {
        throw new \InvalidArgumentException(sprintf('The service file "%s" does not exist (in: %s).', $file, implode(', ', $this->paths)));
      }

      $yamls[$file] = $this->validate(YAML::load($file), $file);
    }

    return $yamls;
  }

  protected function validate($content, $file)
  {
    if (null === $content)
    {
      return $content;
    }

    if (!is_array($content))
    {
      throw new \InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
    }

    foreach (array_keys($content) as $key)
    {
      if (!in_array($key, array('imports', 'parameters', 'services')))
      {
        throw new \InvalidArgumentException(sprintf('The service file "%s" is not valid ("%s" is not recognized).', $file, $key));
      }
    }

    return $content;
  }

  protected function resolveServices($value)
  {
    if (is_array($value))
    {
      $value = array_map(array($this, 'resolveServices'), $value);
    }
    else if (is_string($value) && 0 === strpos($value, '@@'))
    {
      $value = new Reference(substr($value, 2), Container::IGNORE_ON_INVALID_REFERENCE);
    }
    else if (is_string($value) && 0 === strpos($value, '@'))
    {
      $value = new Reference(substr($value, 1));
    }

    return $value;
  }
}
