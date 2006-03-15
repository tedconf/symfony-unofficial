<?php

Mock::generate('sfContext');

class sfWebRequestTest extends UnitTestCase
{
  private
    $context = null,
    $request = null;

  public function SetUp()
  {
    sfRouting::getInstance()->clearRoutes();

    sfConfig::set('sf_stats', false);
    sfConfig::set('sf_path_info_array', 'SERVER');
    sfConfig::set('sf_path_info_key', true);
    sfConfig::set('sf_logging_active', false);
    sfConfig::set('sf_i18n', 0);
    $this->populateVariables('/', true);

    $this->context = new MockSfContext($this);
    $this->request = sfRequest::newInstance('sfWebRequest');
    $this->request->initialize($this->context);
  }

  public function test_pathinfo()
  {
//    $this->populateVariables('http://domain.com/index.php/test/value', true);
//    $this->assertEqual($this->request->getPathInfo(), '/test/value');
  }

  protected function populateVariables($requestUri, $withLayout)
  {
    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $requestUri;
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
      if (!$param) continue;

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