<?php

Mock::generate('sfContext');

class sfRequestTest extends UnitTestCase
{
  private
    $context = null,
    $request = null;

  public function SetUp()
  {
    sfRouting::getInstance()->clearRoutes();

    // can't initialize directly the sfRequest class (abstract)
    // using sfWebRequest class to test sfRequest

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

  public function test_single_error()
  {
    $key = "test";
    $value = "error";

    $this->request->setError($key, $value);
    $this->assertEqual($this->request->hasError($key), true);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->getError($key), $value);
    $this->assertEqual($this->request->removeError($key), $value);
    $this->assertEqual($this->request->hasError($key), false);
    $this->assertEqual($this->request->hasErrors(), false);
  }
  
  public function test_multiple_errors()
  {
    $key1 = "test1";
    $valueKey1_1 = "error1_1";
    $valueKey1_2 = "error1_2";
    $key2 = "test 2";
    $valueKey2_1 = "error2_1";
    $arrayErrors = array($key1 => $valueKey1_2, $key2 => $valueKey2_1);
    $errorNames = array($key1, $key2);

    $this->request->setError($key1, $valueKey1_1);
    $this->request->setErrors($arrayErrors);
    $this->assertEqual($this->request->hasError($key1), true);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->getErrorNames(), $errorNames);
    $this->assertEqual($this->request->getErrors(), $arrayErrors);
    $this->assertEqual($this->request->getError($key1), $valueKey1_2);
    $this->assertEqual($this->request->removeError($key1), $valueKey1_2);
    $this->assertEqual($this->request->hasErrors(), true);
    $this->assertEqual($this->request->removeError($key2), $valueKey2_1);
    $this->assertEqual($this->request->hasErrors(), false);
  }
  
  public function test_method()
  {
    $this->request->setMethod(sfRequest::GET);
    $this->assertEqual($this->request->getMethod(), sfRequest::GET);
  }

  public function test_parameter()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';
    $this->assertEqual($this->request->hasParameter($name1), false);
    $this->assertEqual($this->request->getParameter($name1, $value1), $value1);
    $this->request->setParameter($name1, $value1);
    $this->assertEqual($this->request->hasParameter($name1), true);
    $this->assertEqual($this->request->getParameter($name1), $value1);
    $this->request->setParameter($name2, $value2, $ns);
    $this->assertEqual($this->request->hasParameter($name2), false);
    $this->assertEqual($this->request->hasParameter($name2, $ns), true);
    $this->assertEqual($this->request->getParameter($name2, '', $ns), $value2);
  }

  public function test_attribute()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';
    $this->assertEqual($this->request->hasAttribute($name1), false);
    $this->assertEqual($this->request->getAttribute($name1, $value1), $value1);
    $this->request->setAttribute($name1, $value1);
    $this->assertEqual($this->request->hasAttribute($name1), true);
    $this->assertEqual($this->request->getAttribute($name1), $value1);
    $this->request->setAttribute($name2, $value2, $ns);
    $this->assertEqual($this->request->hasAttribute($name2), false);
    $this->assertEqual($this->request->hasAttribute($name2, $ns), true);
    $this->assertEqual($this->request->getAttribute($name2, '', $ns), $value2);
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