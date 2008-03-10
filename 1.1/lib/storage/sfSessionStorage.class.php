<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSessionStorage allows you to store persistent symfony data in the user session.
 *
 * <b>Optional parameters:</b>
 *
 * # <b>auto_start</b>   - [Yes]     - Should session_start() automatically be called?
 * # <b>session_name</b> - [symfony] - The name of the session.
 *
 * @package    symfony
 * @subpackage storage
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfSessionStorage extends sfStorage
{
  static protected
    $sessionStarted = false;

  /**
   * Initializes this Storage instance.
   *
   * @param array   An associative array of initialization parameters
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Storage
   */
  public function initialize($parameters = null)
  {
    // initialize parent
    parent::initialize($parameters);

    // set max life time for session garabage handler
    ini_set("session.gc_maxlifetime", sfConfig::get('sf_timeout'));

    // set session name
    $sessionName = $this->getParameter('session_name', 'symfony');

    session_name($sessionName);

    if (!(boolean) ini_get('session.use_cookies'))
    {
      if ($sessionId = $this->getParameter('session_id'))
      {
        session_id($sessionId);
      }
    }

    $cookieDefaults = session_get_cookie_params();
    $lifetime = $this->getParameter('session_cookie_lifetime', $cookieDefaults['lifetime']);
    $path     = $this->getParameter('session_cookie_path',     $cookieDefaults['path']);
    $domain   = $this->getParameter('session_cookie_domain',   $cookieDefaults['domain']);
    $secure   = $this->getParameter('session_cookie_secure',   $cookieDefaults['secure']);
    $httpOnly = $this->getParameter('session_cookie_httponly', isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false);
    if (version_compare(phpversion(), '5.2', '>='))
    {
      session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
    }
    else
    {
      session_set_cookie_params($lifetime, $path, $domain, $secure);
    }

    if ($this->getParameter('auto_start', true) && !self::$sessionStarted)
    {
      session_start();
      self::$sessionStarted = true;
    }
  }

  /**
   * Reads data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function read($key)
  {
    $retval = null;

    if (isset($_SESSION[$key]))
    {
      $retval = $_SESSION[$key];
    }

    return $retval;
  }

  /**
   * Removes data from this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   *
   * @return mixed Data associated with the key
   */
  public function remove($key)
  {
    $retval = null;

    if (isset($_SESSION[$key]))
    {
      $retval = $_SESSION[$key];
      unset($_SESSION[$key]);
    }

    return $retval;
  }

  /**
   * Writes data to this storage.
   *
   * The preferred format for a key is directory style so naming conflicts can be avoided.
   *
   * @param string A unique key identifying your data
   * @param mixed  Data associated with your key
   *
   */
  public function write($key, $data)
  {
    $_SESSION[$key] = $data;
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param boolean Destroy session when regenerating?
   * @return boolean True if session regenerated, false if error
   *
   */
  public function regenerate($destroy = false)
  {
    // regenerate a new session id
    session_regenerate_id($destroy);
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
    session_write_close();
  }
}
