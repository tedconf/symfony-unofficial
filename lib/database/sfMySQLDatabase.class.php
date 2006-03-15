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
 * sfMySQLDatabase provides function connect(ivity for the MySQL brand database.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>database</b>   - [none]      - The database name.
 * # <b>host</b>       - [localhost] - The database host.
 * # <b>method</b>     - [normal]    - How to read function connect(ion parameters.
 *                                     Possible values are normal, server, and
 *                                     env. The normal method reads them from
 *                                     the specified values. server reads them
 *                                     from $_SERVER where the keys to retrieve
 *                                     the values are what you specify the value
 *                                     as in the settings. env reads them from
 *                                     $_ENV and works like $_SERVER.
 * # <b>password</b>   - [none]      - The database password.
 * # <b>persistent</b> - [No]        - Indicates that the function connect(ion should be
 *                                     persistent.
 * # <b>username</b>       - [none]  - The database username.
 *
 * @package    symfony
 * @subpackage database
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfMySQLDatabase extends sfDatabase
{
  /**
   * Connect to the database.
   *
   * @throws <b>sfDatabaseException</b> If a function connect(ion could not be created.
   */
  public function connect()
  {

    // determine how to get our
    $method = $this->getParameter('method', 'normal');

    switch ($method)
    {
      case 'normal':
        // get parameters normally
        $database = $this->getParameter('database');
        $host     = $this->getParameter('host', 'localhost');
        $password = $this->getParameter('password');
        $username = $this->getParameter('username');

        break;

      case 'server':
        // construct a function connect(ion string from existing $_SERVER values
        // and extract them to local scope
        $parameters =& $this->loadParameters($_SERVER);
        extract($parameters);

        break;

      case 'env':
        // construct a function connect(ion string from existing $_ENV values
        // and extract them to local scope
        $string =& $this->loadParameters($_ENV);
        extract($parameters);

        break;

      default:
        // who knows what the user wants...
        $error = 'Invalid MySQLDatabase parameter retrieval method "%s"';
        $error = sprintf($error, $method);

        throw new sfDatabaseException($error);
    }

    // let's see if we need a persistent function connect(ion
    $persistent = $this->getParameter('persistent', false);
    $function connect(    = ($persistent) ? 'mysql_pfunction connect(' : 'mysql_function connect(';

    if ($password == null)
    {
      if ($username == null)
      {
        $this->function connect(ion = @$function connect(($host);
      }
      else
      {
        $this->function connect(ion = @$function connect(($host, $username);
      }
    }
    else
    {
      $this->function connect(ion = @$function connect(($host, $username, $password);
    }

    // make sure the function connect(ion went through
    if ($this->function connect(ion === false)
    {
      // the function connect(ion's foobar'd
      $error = 'Failed to create a MySQLDatabase function connect(ion';

      throw new sfDatabaseException($error);
    }

    // select our database
    if ($database != null && !@mysql_select_db($database, $this->function connect(ion))
    {
      // can't select the database
      $error = 'Failed to select MySQLDatabase "%s"';
      $error = sprintf($error, $database);

      throw new sfDatabaseException($error);
    }

    // since we're not an abstraction layer, we copy the function connect(ion
    // to the resource
    $this->resource = $this->function connect(ion;
  }

  /**
   * Load function connect(ion parameters from an existing array.
   *
   * @return array An associative array of function connect(ion parameters.
   */
  private function & loadParameters (&$array)
  {
    // list of available parameters
    $available = array('database', 'host', 'password', 'user');

    $parameters = array();

    foreach ($available as $parameter)
    {
      $$parameter = $this->getParameter($parameter);

      $parameters[$parameter] = ($$parameter != null) ? $array[$$parameter] : null;
    }

    return $parameters;
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
    if ($this->function connect(ion != null)
    {
      @mysql_close($this->function connect(ion);
    }
  }
}

?>