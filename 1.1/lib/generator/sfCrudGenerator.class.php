<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CRUD generator.
 *
 * This class generates a basic CRUD module.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfCrudGenerator extends sfGenerator
{
  protected
    $singularName  = '',
    $pluralName    = '',
    $peerClassName = '',
    $map           = null,
    $tableMap      = null,
    $primaryKey    = array(),
    $className     = '',
    $params        = array();

  /**
   * Generates classes and templates in cache.
   *
   * @param array The parameters
   *
   * @return string The data to put in configuration cache
   */
  public function generate($params = array())
  {
    $this->params = $params;

    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
    {
      if (!isset($this->params[$entry]))
      {
        throw new sfParseException(sprintf('You must specify a "%s".', $entry));
      }
    }

    $modelClass = $this->params['model_class'];

    if (!class_exists($modelClass))
    {
      throw new sfInitializationException(sprintf('Unable to scaffold unexistant model "%s".', $modelClass));
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($this->params['moduleName']));
    $this->setModuleName($this->params['moduleName']);

    // get some model metadata
    $this->loadMapBuilderClasses();

    // load all primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($this->params['theme']) ? $this->params['theme'] : 'default';
    $themeDir = sfLoader::getGeneratorTemplate($this->getGeneratorClass(), $theme, '');
    if (!is_dir($themeDir))
    {
      throw new sfConfigurationException(sprintf('The theme "%s" does not exist.', $theme));
    }

    $this->setTheme($theme);
    $files = sfFinder::type('file')->ignore_version_control()->relative()->in($themeDir);

    $this->generatePhpFiles($this->generatedModuleName, $files);

    // require generated action class
    $data = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php');\n";

    return $data;
  }

  /**
   * Returns PHP code for primary keys parameters.
   *
   * @param integer The indentation value
   *
   * @return string The PHP code
   */
  public function getRetrieveByPkParamsForAction($indent, $callee = '$this->getRequestParameter')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = "$callee('".sfInflector::underscore($pk->getPhpName())."')";
    }

    return implode(",\n".str_repeat(' ', max(0, $indent - strlen($this->singularName.$this->className))), $params);
  }

  /**
   * Returns PHP code for primary keys parameters.
   *
   * @param integer The indentation value
   *
   * @return string The PHP code
   */
  public function getRetrieveByPkParamsForEdit($indent, $prefix)
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $name = sfInflector::underscore($pk->getPhpName());
//      $params[] = sprintf("\$request->getParameter('%s', \$request->getParameter('%s'))", sprintf('%s[%s]', $prefix, $name), $name);
      $params[] = sprintf("\$request->getParameter('%s')", $name);
    }

    return implode(",\n".str_repeat(' ', max(0, $indent - strlen($this->singularName.$this->className))), $params);
  }

  /**
   * Returns PHP code for getOrCreate() parameters.
   *
   * @return string The PHP code
   */
  public function getMethodParamsForGetOrCreate()
  {
    $method_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName       = sfInflector::underscore($pk->getPhpName());
      $method_params[] = "\$$fieldName = '$fieldName'";
    }

    return implode(', ', $method_params);
  }

  /**
   * Returns PHP code for getOrCreate() promary keys condition.
   *
   * @param boolean true if we pass the field name as an argument, false otherwise
   *
   * @return string The PHP code
   */
  public function getTestPksForGetOrCreate($fieldNameAsArgument = true)
  {
    $test_pks = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName  = sfInflector::underscore($pk->getPhpName());
      $test_pks[] = sprintf("!\$this->getRequestParameter(%s)", $fieldNameAsArgument ? "\$$fieldName" : "'".$fieldName."'");
    }

    return implode("\n     || ", $test_pks);
  }

  /**
   * Returns PHP code for primary keys parameters used in getOrCreate() method.
   *
   * @return string The PHP code
   */
  public function getRetrieveByPkParamsForGetOrCreate()
  {
    $retrieve_params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $fieldName         = sfInflector::underscore($pk->getPhpName());
      $retrieve_params[] = "\$this->getRequestParameter(\$$fieldName)";
    }

    return implode(",\n".str_repeat(' ', max(0, 45 - strlen($this->singularName.$this->className))), $retrieve_params);
  }

  /**
   * Gets the table map for the current model class.
   *
   * @return TableMap A TableMap instance
   */
  public function getTableMap()
  {
    return $this->tableMap;
  }

  /**
   * Sets the class name to use for scaffolding
   *
   * @param  string class name
   */
  protected function setScaffoldingClassName($className)
  {
    $this->singularName  = sfInflector::underscore($className);
    $this->pluralName    = $this->singularName.'s';
    $this->className     = $className;
    $this->peerClassName = $className.'Peer';
  }

  /**
   * Gets the singular name for current scaffolding class.
   *
   * @return string
   */
  public function getSingularName()
  {
    return $this->singularName;
  }

  /**
   * Gets the plural name for current scaffolding class.
   *
   * @return string
   */
  public function getPluralName()
  {
    return $this->pluralName;
  }

  /**
   * Gets the class name for current scaffolding class.
   *
   * @return string
   */
  public function getClassName()
  {
    return $this->className;
  }

  /**
   * Gets the Peer class name.
   *
   * @return string
   */
  public function getPeerClassName()
  {
    return $this->peerClassName;
  }

  /**
   * Gets the primary key name.
   *
   * @return string
   */
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  /**
   * Gets the Map object.
   *
   * @return object
   */
  public function getMap()
  {
    return $this->map;
  }

  /**
   * Returns PHP code to add to a URL for primary keys.
   *
   * @param string The prefix value
   *
   * @return string PHP code
   */
  public function getPrimaryKeyUrlParams($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $phpName   = $pk->getPhpName();
      $fieldName = sfInflector::underscore($phpName);
      $params[]  = "$fieldName='.".$this->getColumnGetter($pk, true, $prefix);
    }

    return implode(".'&", $params);
  }

  /**
   * Gets PHP code for primary key condition.
   *
   * @param string The prefix value
   *
   * @return string PHP code
   */
  public function getPrimaryKeyIsSet($prefix = '')
  {
    $params = array();
    foreach ($this->getPrimaryKey() as $pk)
    {
      $params[] = $this->getColumnGetter($pk, true, $prefix);
    }

    return implode(' && ', $params);
  }

  /**
   * Gets object tag parameters.
   *
   * @param array An array of parameters
   * @param array An array of default parameters
   *
   * @return string PHP code
   */
  protected function getObjectTagParams($params, $default_params = array())
  {
    return var_export(array_merge($default_params, $params), true);
  }

  /**
   * Loads primary keys.
   *
   * This method is ORM dependant.
   *
   * @throws sfException
   */
  abstract protected function loadPrimaryKeys();

  /**
   * Loads map builder classes.
   *
   * This method is ORM dependant.
   *
   * @throws sfException
   */
  abstract protected function loadMapBuilderClasses();

  /**
   * Generates a PHP call to an object helper.
   *
   * This method is ORM dependant.
   *
   * @param string The helper name
   * @param string The column name
   * @param array  An array of parameters
   * @param array  An array of local parameters
   *
   * @return string PHP code
   */
  abstract function getPHPObjectHelper($helperName, $column, $params, $localParams = array());

  /**
   * Returns the getter either non-developped: 'getFoo' or developped: '$class->getFoo()'.
   *
   * This method is ORM dependant.
   *
   * @param string  The column name
   * @param boolean true if you want developped method names, false otherwise
   * @param string The prefix value
   *
   * @return string PHP code
   */
  abstract function getColumnGetter($column, $developed = false , $prefix = '');

  /*
   * Gets the PHP name of the related class name.
   *
   * Used for foreign keys only; this method should be removed when we use sfAdminColumn instead.
   *
   * This method is ORM dependant.
   *
   * @param string The column name
   *
   * @return string The PHP name of the related class name
   */
  abstract function getRelatedClassName($column);

  /**
   * Returns HTML code for a column in filter mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  abstract function getColumnFilterTag($column, $params = array());

  /**
   * Returns HTML code for a column in edit mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  abstract function getColumnEditTag($column, $params = array());

  /**
   * Returns HTML code for a column in list mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  abstract function getColumnListTag($column, $params = array());

  /**
   * Returns HTML code for a column in edit mode.
   *
   * @param string  The column name
   * @param array   The parameters
   *
   * @return string HTML code
   */
  abstract function getCrudColumnEditTag($column, $params = array());

}
