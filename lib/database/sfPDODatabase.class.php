<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPDODatabase provides connectivity for the PDO database abstraction layer.
 *
 * @package    symfony
 * @subpackage database
 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPDODatabase extends sfDatabase
{
  /**
   * Connects to the database.
   *
   * @throws <b>sfDatabaseException</b> If a connection could not be created
   */
  public function connect()
  {
    // determine how to get our parameters
    $method = $this->getParameter('method', 'dsn');

    // get parameters
    switch ($method)
    {
      case 'dsn':
        $dsn = $this->getParameter('dsn');

        if ($dsn == null)
        {
          // missing required dsn parameter
          $error = 'Database configuration specifies method "dsn", but is missing dsn parameter';

          throw new sfDatabaseException($error);
        }

        break;
    }

    try
    {
      $username = $this->getParameter('username');
      $password = $this->getParameter('password');

      $persistent = $this->getParameter('persistent');

      $options = ($persistent) ? array(PDO::ATTR_PERSISTENT => true) : array(PDO::ATTR_PERSISTENT => false);

      $this->connection = new PDO($dsn, $username, $password, $options);
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException($e->getMessage());
    }

    // lets generate exceptions instead of silent failures
    if(sfConfig::get('sf_debug'))
    {
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    else
    {
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    // compatability
    $compatability = $this->getParameter('compat');
    if($compatability)
    {
      $this->connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
    }

    // nulls
    $nulls = $this->getParameter('nulls');
    if($nulls)
    {
      $this->connection->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
    }

    // auto commit
    $autocommit = $this->getParameter('autocommit');
    if($autocommit)
    {
      $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    }
  }

  /**
   * Executes the shutdown procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this database
   */
  public function shutdown()
  {
    if ($this->connection !== null)
    {
      $this->connection = null;
    }
  }
}
