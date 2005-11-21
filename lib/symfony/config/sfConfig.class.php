<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfig stores all configuration information for a symfony application.
 *
 * @package    symfony
 * @subpackage core
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfConfig
{
  private
    $config = array();

  private static
    $instance          = null;

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfConfig A sfConfig implementation instance.
   */
  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      $class = __CLASS__;
      self::$instance = new $class();
    }

    return self::$instance;
  }

  /**
   * Retrieve a config parameter.
   *
   * @param string A config parameter name.
   * @param mixed  A default config parameter value.
   *
   * @return mixed A config parameter value, if the config parameter exists, otherwise null.
   */
  public function get ($name, $default = null)
  {
    return isset($this->config[$name]) ? $this->config[$name] : $default;
  }

  /**
   * Set a config parameter.
   *
   * If a config parameter with the name already exists the value will be overridden.
   *
   * @param string A config parameter name.
   * @param mixed  A config parameter value.
   *
   * @return void
   */
  public function set ($name, $value)
  {
    $this->config[$name] = $value;
  }

  /**
   * Set an array of config parameters.
   *
   * If an existing config parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array An associative array of config parameters and their associated values.
   *
   * @return void
   */
  public function add ($parameters)
  {
    if ($parameters === null) return;

    foreach ($parameters as $key => $value)
    {
      $this->config[$key] = $value;
    }
  }
}

?>