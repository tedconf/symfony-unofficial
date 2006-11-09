<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * Copyright (c) 2006 Yahoo! Inc.  All rights reserved.  
 * The copyrights embodied in the content in this file are licensed 
 * under the MIT open source license
 *
 * For the full copyright and license information, please view the LICENSE
 * and LICENSE.yahoo files that was distributed with this source code.
 */

/**
 * sfFileLocator is the wrapper class for managing the various
 * (non-core) file location mechanisms within Symfony.  For actions,
 * templates, and partials, there are sequences of locations in which
 * to search for implementations.  This interface abstracts those
 * sequences so that the user can provide alternate search paths
 * for his application.
 *
 * @package    symfony
 * @subpackage utility
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfFileLocator.class.php,v 1.4 2006/06/22 18:51:55 salisbur Exp $
 */

/**
 * Combines various file locator classes in one place.
 */
abstract class sfFileLocator
{
  /**
   * Retrieve a new sfFileLocator implementation instance.
   *
   * @param string A sfFileLocator implementation name.
   *
   * @return sfFileLocator A sfFileLocator implementation instance.
   *
   * @throws sfFactoryException If a new file locator implementation instance cannot be created.
   */
  public static function newInstance ($class)
  {
    try
    {
      // the class exists
      $object = new $class();

      if (!($object instanceof sfFileLocator))
      {
          // the class name is of the wrong type
          $error = 'Class "%s" is not of the type sfFileLocator';
          $error = sprintf($error, $class);

          throw new sfFactoryException($error);
      }

      return $object;
    }
    catch (sfException $e)
    {
      $e->printStackTrace();
    }
  }

  /**
   * Loads the class specified and returns its (string) name.
   *
   * @param string A module name.
   * @param string A controller name.
   * @param string The controller type (action, component)
   *
   * If the $culture-specific class is not found, the generic one is returned.
   * If the class doesn't exist, null is returned.
   * If error loading the class, throws sfConfigurationException
   * @return string Class name if the class exists.  Null otherwise.
   */
  abstract public function loadActionClass($moduleName, $controllerName, $extension);

  /**
   * Find and load a View implementation
   *
   * @param string A module name.
   * @param string An action name.
   * @param string A view name.
   *
   * @return The view classname if the view exists, otherwise null.
   */
  abstract public function loadViewClass($moduleName, $actionName, $viewName);

  /**
   * Finds the name and location for the given template.
   * @param viewType type of template this is (global partial, partial, ...)
   * @param moduleName the name of the module for this template
   * @param templateFile the template name
   * @param templateDir the module's template directory
   * @return (string,string) the name and directory of the template file, dir is null if not found.
   */
  abstract public function getViewTemplateDir($viewType, $moduleName, 
                                          $templateFile, $templateDir);

  /**
   * Finds and returns full path/filename of this partial
   *
   * @param moduleName the name of the module containing the partial
   *                   or 'global' if global
   *                   or null if not module-specific
   * @param partialName the name of the partial to find
   */
//  abstract public function findPartial($moduleName, $partialName);

  /**
   * Finds and returns full path/filename of this decorator
   *
   * @param directory the base directory to search underneath
   * @param filename the leaf filename to find
   */
  abstract public function findDecorator($directory, $filename);
}

?>
