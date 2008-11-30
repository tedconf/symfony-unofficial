<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a Doctrine column
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrineAdminColumn extends sfAdminColumn
{
  /**
   * Array mapping Doctrine column types to the native symfony type
   */
  static $docToCreole = array(
    'boolean'   => 'BOOLEAN',
    'string'    => 'LONGVARCHAR',
    'integer'   => 'INTEGER',
    'date'      => 'DATE',
    'timestamp' => 'TIMESTAMP',
    'time'      => 'TIME',
    'enum'      => 'LONGVARCHAR',
    'float'     => 'FLOAT',
    'double'    => 'DOUBLE',
    'clob'      => 'CLOB',
    'blob'      => 'BLOB',
    'object'    => 'LONGVARCHAR',
    'array'     => 'LONGVARCHAR',
    'decimal'   => 'DECIMAL',
  );

  /**
   * Store the name of the related class for this column if it is
   * a foreign key
   *
   * @var string
   */
  protected $relatedClassName = null;

  /**
   * Field name of the column
   *
   * @var string
   */
  protected $name = null;

  /**
   * Real name of the column in the database
   *
   * @var string
   */
  protected $columnName;

  /**
   * Get the Doctrine type of the column
   *
   * @return void
   */
  public function getDoctrineType()
  {
    return isset($this->column['type']) ? $this->column['type'] : null;
  }

  /**
   * Get symfony type of the column
   *
   * @return void
   */
  public function getType()
  {
    $doctrineType = $this->getDoctrineType();

    // we simulate the CHAR/VARCHAR types to generate input_tags
    if(($doctrineType == 'string') and ($this->getSize() < 256))
    {
      return 'VARCHAR';
    }

    return $doctrineType ? self::$docToCreole[$doctrineType] : -1;
  }

  public function getCreoleType()
  {
    return $this->getType();
  }

  /**
   * Get size/length of the column
   *
   * @return void
   */
  public function getSize()
  {
    return $this->column['length'];
  }

  /**
   * Returns true of the column is not null and false if it is null
   *
   * @return boolean
   */
  public function isNotNull()
  {
    if (isset($this->column['notnull']))
    {
      return $this->column['notnull'];
    }
    return false;
  }

  /**
   * Returns true if the column is a primary key and false if it is not
   *
   * @return void
   */
  public function isPrimaryKey()
  {
    if (isset($this->column['primary']))
    {
      return $this->column['primary'];
    }
    return false;
  }

  /**
   * Set the name of the related class name for this column foreign key
   *
   * @param string $newName  Name of the related class
   * @return void
   */
  public function setRelatedClassName($newName)
  {
    $this->relatedClassName = $newName;
  }

  /**
   * Get the name of the related class for this column foreign key.
   *
   * @return string $relatedClassName
   */
  public function getRelatedClassName()
  {
    return $this->relatedClassName;
  }

  /**
   * Set the column name
   *
   * @param string $newName
   * @return void
   */
  public function setColumnName($newName)
  {
    $this->columnName = $newName;
  }

  /**
   * Get the column name
   *
   * @return string $columnName
   */
  public function getColumnName()
  {
    return $this->columnName;
  }

  /**
   * Set array of column information
   *
   * @param array $col
   * @return void
   */
  public function setColumnInfo($col)
  {
    $this->column = $col;
  }

  /**
   * Set the name of the column
   *
   * @param string $newName
   * @return void
   */
  public function setName($newName)
  {
    $this->name = $newName;
  }

  /**
   * Get the name of the column
   *
   * @return void
   */
  public function getName()
  {
    if (isset($this->name))
    {
      return $this->name;
    }
    // a bit kludgy: the field name is actually in $this->phpName
    return parent::getPhpName();
  }

  /**
   * Returns true if this column is a foreign key and false if it is not
   *
   * @return boolean $isForeignKey
   */
  public function isForeignKey()
  {
    return isset($this->relatedClassName);
  }

  /**
   * __call
   *
   * all the calls that were forwarded to the table object with propel
   * have to be dealt with explicitly here, otherwise:
   *
   * @param string $name
   * @param string $arguments
   * @return void
   */
  public function __call($name, $arguments)
  {
    throw new Exception(sprintf('Unhandled call: "%s"', $name));
  }
}