<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextBrowser simulates a fake browser which can surf a symfony application.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTestBrowser
{
  /*

  FIXME/TODO:
    - POST support
    - redirect support?

  */
  private
    $presentation = '';

  private static
    $currentContext = null;

  public function initialize($hostname = null)
  {
    // setup our fake environment
    $_SERVER['HTTP_HOST'] = ($hostname ? $hostname : sfConfig::get('sf_app').'-'.sfConfig::get('sf_environment'));
    $_SERVER['HTTP_USER_AGENT'] = 'PHP5/CLI';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    // we set a session id (fake cookie / persistence)
    $_SERVER['session_id'] = md5(uniqid(rand(), true));

    sfConfig::set('sf_path_info_array', 'SERVER');

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));
  }

  public function get($requestUri = '/', $withLayout = true)
  {
    sfConfig::set('sf_timer_start', microtime(true));

    $context = $this->initRequest($requestUri, $withLayout);
    $html = $this->getContent();
    $this->closeRequest();

    return $html;
  }

  public function initRequest($requestUri = '/', $withLayout = true)
  {
    if (self::$currentContext)
    {
      throw new sfException('a request is already active');
    }

    $this->populateVariables($requestUri, $withLayout);

    // launch request via controller
    $context    = sfContext::getInstance();
    $controller = $context->getController();
    $request    = $context->getRequest();

    $request->getParameterHolder()->clear();
    $request->function initialize(($context);

    ob_start();
    $controller->dispatch();
    $this->presentation = ob_get_clean();

    // manually shutdown user to save current session data
    $context->getUser()->shutdown();

    self::$currentContext = $context;

    return $context;
  }

  public function getContext()
  {
    return self::$currentContext;
  }

  public function getContent()
  {
    if (!self::$currentContext)
    {
      throw new sfException('a request must be active');
    }

    return $this->presentation;
  }

  public function closeRequest()
  {
    if (!self::$currentContext)
    {
      throw new sfException('a request must be active');
    }

    // clean state
    self::$currentContext->shutdown();
    self::$currentContext = null;
    sfContext::removeInstance();
    sfWebDebug::removeInstance();
  }

  public function shutdown()
  {
    // we remove all session data
    sfToolkit::clearDirectory(sfConfig::get('sf_test_cache_dir'));
  }

  protected function populateVariables($requestUri, $withLayout)
  {
    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    if ($requestUri[0] != '/')
    {
      $requestUri = '/'.$requestUri;
    }

    // add index.php if needed
    if (!strpos($requestUri, '.php'))
    {
      $requestUri = '/index.php'.$requestUri;
    }

    $_SERVER['REQUEST_URI'] = $requestUri;

    // query string
    $_SERVER['QUERY_STRING'] = '';
    if ($queryStringPos = strpos($requestUri, '?'))
    {
      $_SERVER['QUERY_STRING'] = substr($requestUri, $queryStringPos + 1);
    }
    else
    {
      $queryStringPos = strlen($requestUri);
    }

    // path info
    $_SERVER['PATH_INFO'] = '/';
    $scriptPos = strpos($requestUri, '.php') + 5;
    if ($scriptPos < $queryStringPos)
    {
      $_SERVER['PATH_INFO'] = '/'.substr($requestUri, $scriptPos, $queryStringPos - $scriptPos);
    }

    // parse query string
    $params = explode('&', $_SERVER['QUERY_STRING']);
    foreach ($params as $param)
    {
      if (!$param)
      {
        continue;
      }

      list ($key, $value) = explode('=', $param);
      $_GET[$key] = urldecode($value);
    }

    // change layout
    if (!$withLayout)
    {
      // we simulate an Ajax call to disable layout
      $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }
    else
    {
      unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
  }
}

?>