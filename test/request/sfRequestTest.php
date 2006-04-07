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

    $this->assertTrue($this->request->hasError($key));
    $this->assertTrue($this->request->hasErrors());
    $this->assertEqual($value, $this->request->getError($key));
    $this->assertEqual($value, $this->request->removeError($key));
    $this->assertFalse($this->request->hasError($key));
    $this->assertFalse($this->request->hasErrors());
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

    $this->assertTrue($this->request->hasError($key1));
    $this->assertTrue($this->request->hasErrors());
    $this->assertEqual($errorNames, $this->request->getErrorNames());
    $this->assertEqual($arrayErrors, $this->request->getErrors());
    $this->assertEqual($valueKey1_2, $this->request->getError($key1));
    $this->assertEqual($valueKey1_2, $this->request->removeError($key1));
    $this->assertTrue($this->request->hasErrors());
    $this->assertEqual($valueKey2_1, $this->request->removeError($key2));
    $this->assertFalse($this->request->hasErrors());
  }

  public function test_method()
  {
    $this->request->setMethod(sfRequest::GET);
    $this->assertEqual(sfRequest::GET, $this->request->getMethod());
  }

  public function test_parameter()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';

    $this->assertFalse($this->request->hasParameter($name1));
    $this->assertEqual($value1, $this->request->getParameter($name1, $value1));

    $this->request->setParameter($name1, $value1);
    $this->assertTrue($this->request->hasParameter($name1));
    $this->assertEqual($value1, $this->request->getParameter($name1));

    $this->request->setParameter($name2, $value2, $ns);
    $this->assertFalse($this->request->hasParameter($name2));
    $this->assertTrue($this->request->hasParameter($name2, $ns));
    $this->assertEqual($value2, $this->request->getParameter($name2, '', $ns));
  }

  public function test_attribute()
  {
    $name1 = 'test_name1';
    $value1 = 'test_value1';
    $name2 = 'test_name2';
    $value2 = 'test_value2';
    $ns = 'test_ns';

    $this->assertFAlse($this->request->hasAttribute($name1));
    $this->assertEqual($value1, $this->request->getAttribute($name1, $value1));

    $this->request->setAttribute($name1, $value1);
    $this->assertTrue($this->request->hasAttribute($name1));
    $this->assertEqual($value1, $this->request->getAttribute($name1));

    $this->request->setAttribute($name2, $value2, $ns);
    $this->assertFalse($this->request->hasAttribute($name2));
    $this->assertTrue($this->request->hasAttribute($name2, $ns));
    $this->assertEqual($value2, $this->request->getAttribute($name2, '', $ns));
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