<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelData
{
  private
    $deleteCurrentData = true,
    $maps              = array(),
    $objectReferences  = array();

  public function setDeleteCurrentData($boolean)
  {
    $this->deleteCurrentData = $boolean;
  }

  public function getDeleteCurrentData()
  {
    return $this->deleteCurrentData;
  }

  // symfony load-data (file|dir)
  public function loadData($directoryOrFile = null, $connectionName = 'propel')
  {
    $fixtureFiles = $this->getFiles($directoryOrFile);

    // wrap all databases operations in a single transaction
    $con = Propel::getConnection();
    try
    {
      $con->begin();

      $this->doDeleteCurrentData($fixtureFiles);

      $this->doLoadData($fixtureFiles);

      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();
      throw $e;
    }
  }

  protected function doLoadDataFromFile($fixtureFile)
  {
    // import new datas
    $mainDatas = sfYaml::load($fixtureFile);

    if ($mainDatas === null)
    {
      // no data
      return;
    }

    foreach ($mainDatas as $class => $datas)
    {
      $class = trim($class);

      $peerClass = $class.'Peer';

      // load map class
      $this->loadMapBuilder($class);

      $tableMap = $this->maps[$class]->getDatabaseMap()->getTable(constant($peerClass.'::TABLE_NAME'));

      $columnNames = call_user_func_array(array($peerClass, 'getFieldNames'), array(BasePeer::TYPE_FIELDNAME));

      // iterate through datas for this class
      // might have been empty just for force a table to be emptied on import
      if (is_array($datas))
      {
        foreach ($datas as $key => $data)
        {
          // create a new entry in the database
          $obj = new $class();
          foreach ($data as $name => $value)
          {
            // foreign key?
            try
            {
              $column = $tableMap->getColumn($name);
              if ($column->isForeignKey())
              {
                $relatedTable = $this->maps[$class]->getDatabaseMap()->getTable($column->getRelatedTableName());
                if (!isset($this->objectReferences[$relatedTable->getPhpName().'_'.$value]))
                {
                  $error = 'The object "%s" from class "%s" is not defined in your data file.';
                  $error = sprintf($error, $value, $relatedTable->getPhpName());
                  throw new sfException($error);
                }
                $value = $this->objectReferences[$relatedTable->getPhpName().'_'.$value];
              }
            }
            catch (PropelException $e)
            {
            }

            $pos = array_search($name, $columnNames);
            $method = 'set'.sfInflector::camelize($name);
            if ($pos)
            {
              $obj->setByPosition($pos, $value);
            }
            else if (is_callable(array($obj, $method)))
            {
              $obj->$method($value);
            }
            else
            {
              $error = 'Column "%s" does not exist for class "%s"';
              $error = sprintf($error, $name, $class);
              throw new sfException($error);
            }
          }
          $obj->save();

          // save the id for future reference
          if (method_exists($obj, 'getPrimaryKey'))
          {
            $this->objectReferences[$class.'_'.$key] = $obj->getPrimaryKey();
          }
        }
      }
    }
  }

  protected function doLoadData($fixtureFiles)
  {
    $this->objectReferences = array();
    $this->maps = array();

    sort($fixtureFiles);
    foreach ($fixtureFiles as $fixtureFile)
    {
      $this->doLoadDataFromFile($fixtureFile);
    }
  }

  protected function doDeleteCurrentData($fixtureFiles)
  {
    // delete all current datas in database
    if ($this->deleteCurrentData)
    {
      rsort($fixtureFiles);
      foreach ($fixtureFiles as $fixtureFile)
      {
        $mainDatas = sfYaml::load($fixtureFile);

        if ($mainDatas === null)
        {
          // no data
          continue;
        }

        $classes = array_keys($mainDatas);
        krsort($classes);
        foreach ($classes as $class)
        {
          $peerClass = trim($class.'Peer');

          require_once(sfConfig::get('sf_model_lib_dir').'/'.$peerClass.'.php');

          call_user_func(array($peerClass, 'doDeleteAll'));
        }
      }
    }
  }

  protected function getFiles($directoryOrFile = null)
  {
    // directory or file?
    $fixtureFiles = array();
    if (!$directoryOrFile)
    {
      $directoryOrFile = sfConfig::get('sf_data_dir').'/fixtures';
    }

    if (is_file($directoryOrFile))
    {
      $fixtureFiles[] = $directoryOrFile;
    }
    elseif (is_dir($directoryOrFile))
    {
      $fixtureFiles = sfFinder::type('file')->name('*.yml')->in($directoryOrFile);
    }
    else
    {
      throw new sfInitializationException('You must give a directory or a file.');
    }

    return $fixtureFiles;
  }

  private function loadMapBuilder($class)
  {
    $classMapBuilder = $class.'MapBuilder';
    if (!isset($this->maps[$class]))
    {
      require_once(sfConfig::get('sf_model_lib_dir').'/map/'.$classMapBuilder.'.php');
      $this->maps[$class] = new $classMapBuilder();
      $this->maps[$class]->doBuild();
    }
  }

  /**
   * Dumps data to fixture from 1 or more tables.
   *
   * @param string directory or file to dump to
   * @param mixed name or names of tables to dump
   * @param string connection name
   */
  public function dumpData($directoryOrFile = null, $tables = 'all', $connectionName = 'propel')
  {
    $sameFile = true;
    if (is_dir($directoryOrFile) && 'all' === $tables ||  (is_array($tables) && 1 < count($tables)))
    {
      // multi files
      $sameFile = false;
    }
    else
    {
      // same file
      // delete file
    }

    $con = sfContext::getInstance()->getDatabaseConnection($connectionName);

    // get tables
    if ('all' === $tables || null === $tables)
    {
      $tables = sfFinder::type('file')->name('/(?<!Peer)\.php$/')->maxdepth(0)->in(sfConfig::get('sf_model_lib_dir'));
      foreach ($tables as &$table)
      {
        $table = basename($table, '.php');
      }
    }
    elseif (!is_array($tables))
    {
      $tables = array($tables);
    }

    $dumpData = array();

    // load map classes
    array_walk($tables, array($this, 'loadMapBuilder'));

    foreach ($tables as $table)
    {
      $tableMap = $this->maps[$table]->getDatabaseMap()->getTable(constant($table.'Peer::TABLE_NAME'));

      // get db info
      $rs = $con->executeQuery('SELECT * FROM '.constant($table.'Peer::TABLE_NAME'));

      $dumpData[$table] = array();

      while ($rs->next())
      {
        $pk = '';
        foreach ($tableMap->getColumns() as $column)
        {
          $col = strtolower($column->getColumnName());

          if ($column->isPrimaryKey())
          {
            $pk .= '_' .$rs->get($col);
            continue;
          }
          elseif ($column->isForeignKey())
          {
            $relatedTable = $this->maps[$table]->getDatabaseMap()->getTable($column->getRelatedTableName());

            $dumpData[$table][$table.$pk][$col] = $relatedTable->getPhpName().'_'.$rs->get($col);
          }
          else
          {
            $dumpData[$table][$table.$pk][$col] = $rs->get($col);
          }
        } // foreach
      } // while
    }

    // save to file(s)
    if ($sameFile)
    {
      $yaml = Spyc::YAMLDump($dumpData);
      file_put_contents($directoryOrFile, $yaml);
    }
    else
    {
      foreach ($dumpData as $table => $data)
      {
        $yaml = Spyc::YAMLDump($data);
        file_put_contents($directoryOrFile."/$table.yml", $yaml);
      }
    }
  }
}

?>