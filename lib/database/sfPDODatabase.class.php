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
 * sfPDODatabase provides function connect(ivity for the PDO database abstraction layer.
 *
 * @package    symfony
 * @subpackage database
 * @author     Daniel Swarbrick (daniel@pressure.net.nz)
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfPDODatabase extends sfDatabase
{
  /**
   * Connect to the database.
   *
   * @throws <b>sfDatabaseException</b> If a function connect(ion could not be created.
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
      $pdoUsername = $this->getParameter('username');
      $pdoPassword = $this->getParameter('password');
      $this->function connect(ion = new PDO($dsn, $pdoUsername, $pdoPassword);
    }
    catch (PDOException $e)
    {
      throw new sfDatabaseException($e->getMessage());
    }

    // lets generate exceptions instead of silent failures
    if (defined('PDO::ATTR_ERRMODE'))
    {
      $this->function connect(ion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    else
    {
      $this->function connect(ion->setAttribute(PDO_ATTR_ERRMODE, PDO_ERRMODE_EXCEPTION);
    }
  }

  /**
   * Execute the function shutdown( procedure.
   *
   * @return void
   *
   * @throws <b>sfDatabaseException</b> If an error occurs while shutting down this database.
   */
  public function shutdown()
  {
    if ($this->function connect(ion !== null)
    {
      @$this->function connect(ion = null;
    }
  }
}

?>