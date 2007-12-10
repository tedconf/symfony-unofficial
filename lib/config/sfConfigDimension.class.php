<?php

/*
* This file is part of the symfony package.
* (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>

* Copyright (c) 2007 Yahoo! Inc.  All rights reserved.
* The copyrights embodied in the content in this file are licensed
* under the MIT open source license.
*
* For the full copyright and license information, please view the LICENSE.yahoo
* file that was distributed with this source code.
*/

/**
 * sfConfigDimension manages configuration dimensions. A dimension can be any parameter that changes configuration selection, template selection, or action execution.
 * For example, your configuration could depend on a host type (production|development|qa|staging), a culture (en|fr|it|de), and a theme (classic|mybrand).
 * You must specify allowed dimension types and values in the project dimensions.yml:
 *
 * allowed:
 *   host:       [production, development, qa, staging]
 *   culture:    [en, fr, it, de]
 *   theme:      [classic, mybrand]
 *
 * default:
 *   host:       production
 *   culture:    en
 *   theme:      classic
 *
 * If the default is not specified, the default dimension is the first value of each dimension type: host => production, culture => en, theme => classic
 *
 * @package    symfony
 * @subpackage config
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfConfigCache.class.php 5943 2007-11-09 19:59:05Z dwhittle $
 */
class sfConfigDimension
{

  protected static $instance = null;

  private $cache    = null;

  public $allowed   = array(), // array of allowed dimensions by $name => array $value
  $default   = array(), // default dimension if not set
  $dimension = array(); // current dimension


  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfConfigDimension A sfConfigDimension instance
   */
  public static function getInstance()
  {
    if (!self::$instance)
    {
      self::$instance = new sfConfigDimension();
    }

    return self::$instance;
  }

  /**
   * initializes dimensions
   */
  public function initialize()
  {
    $this->cache = new sfAPCCache(array('prefix' => 'dimension'));

    $this->configure();
  }

  /**
   * Configure loads and parses the dimensions.yml and configures acceptable dimensions that can be set,
   * and sets the dimension to the default specified in dimension.yml or to first value of each acceptable dimension.
   */
  public function configure()
  {
    // web or cli? it is hard to tell this early on, so take best guess
    $sf_root_dir = defined('SF_ROOT_DIR') ? SF_ROOT_DIR : getcwd();

    $sf_dimension_config_file = $sf_root_dir.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'dimensions.yml';

    // configure automatically based on dimension.yml
    if($dimensions = sfYaml::load($sf_dimension_config_file))
    {
      // normalize key values
      $dimensions = array_change_key_case($dimensions, CASE_LOWER);

      if(!isset($dimensions['allowed']) || (isset($dimensions['allowed']) && !is_array($dimensions['allowed'])))
      {
        throw new sfException(sprintf('You must defined allowed dimensions in %s', $sf_dimension_config_file));
      }

      // set allowed dimensions with normalized keys/values
      foreach ($dimensions['allowed'] as $key => $values)
      {
        if(is_array($values))
        {
          $values = array_map('strtolower', $values);
        }
        elseif(is_string($values))
        {
          $values = array(strtolower($values));
        }
        else
        {
          throw new sfException(sprintf('Allowed dimensions in %s must be of type array or string.', $sf_dimension_config_file));
        }

        $this->allowed[strtolower($key)] = $values;
      }

      if(isset($dimensions['default']) && is_array($dimensions['default']))
      {
        // set default dimensions with normalized keys/values
        foreach ($dimensions['default'] as $key => $value)
        {
          if(is_string($value))
          {
            $this->default[strtolower($key)] = strtolower($value);
          }
          else
          {
            throw new sfException(sprintf('Default dimensions in %s must be of type string.', $sf_dimension_config_file));
          }
        }
      }

      $this->set($this->getDefault());
    }
    else
    {
      throw new sfException(sprintf('Could not find or load %s', $sf_dimension_config_file));
    }
  }

  /**
   * getAllowed returns all allowed dimensions as an array
   *
   * @return array all allowed dimensions
   */
  public function getAllowed()
  {
    return $this->allowed;
  }

  /**
   * setDefault sets the default dimension for when no other dimension is combination is matched
   *
   * @param array the dimension to set as default
   *
   */
  public function setDefault($dimension)
  {
    $this->default = $dimension;
  }

  /**
   * getDefault returns the default dimension for when no other dimension is combination is matched
   *
   * @return array the default dimension
   */
  public function getDefault()
  {
    // if a default was not set, take the first value of each allowed dimensions
    if(empty($this->default))
    {
      $default = array();
      foreach($this->allowed as $key => $values)
      {
        $default[$key] = $values[0];
      }
      $this->default = $default;
    }

    return $this->default;
  }

  /**
   * check validates the the input dimension is valid
   *
   * @param array the dimension to check
   *
   * @return boolean true if dimension is valid
   */
  public function check($dimension)
  {
    if(!is_array($dimension))
    {
      return false;
    }
    else
    {
      $allowed = array_keys($dimension);
      foreach($allowed as $name)
      {
        if(!isset($this->allowed[$name]) || !in_array($dimension[$name], $this->allowed[$name]))
        {
          throw new sfException(sprintf('The dimension %s is not an allowed dimension.', var_export($dimension, true)));
        }
      }
    }

    return true;
  }

  /**
   * cleans dimensions by normalizing names/values + removing dimensions with null values (reduce lookups)
   *
   * @param array the dimension to clean
   *
   * @return array the cleaned dimension
   */
  public function clean($dimension)
  {
    foreach($dimension as $name => $value)
    {
      if(is_null($value))
      {
        unset($dimension[$name]);
      }
      else
      {
        $dimension[strtolower($name)] = strtolower($value);
      }
    }
    return $dimension;
  }

  /**
   * sets the current dimension
   *
   * @param array the dimension to set
   *
   */
  public function set($dimension)
  {
    $dimension = $this->clean($dimension);

    if($this->check($dimension))
    {
      $this->dimension = $dimension;

      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Get the current dimension
   *
   * @return array the current dimension
   */
  public function get()
  {
    return $this->dimension;
  }

  /**
   * Get the current dimension cascade
   *
   * @return array dimensions cascade
   */
  public function getCascade()
  {
    $cascade = array();

    if(count($this->dimension) > 1)
    {
      $cascade = array_values($this->dimension);

      // create a cacade of dimensions
      $dimensionsCascade = new CartesianIterator();
      foreach($this->dimension as $name => $values)
      {
        $dimensionsCascade->addArray(array($values));

        foreach($dimensionsCascade as $dimension)
        {
          $cascade[] = implode('_', $dimension);
        }
      }

      $cascade = array_unique($cascade);
    }
    else
    {
      $cascade = array_values($this->dimension);
    }

    return array_reverse($cascade);  // give most specific dimensions first
  }

  /**
   * Gets current dimension as a string
   *
   * @return string the current dimension as a string
   */
  public function __toString()
  {
    $dimension = $this->get();

    $dimensionString = '';
    $i = 0;
    foreach ($dimension as $name => $value)
    {
      $seperator = ($i > 0) ? '_' : '';
      $dimensionString .= $seperator.$value;
      $i++;
    }

    return $dimensionString;
  }

}