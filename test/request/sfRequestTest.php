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
    $value_key1_1 = "error1_1";
    $value_key1_2 = "error1_2";
    $key2 = "test 2";
    $value_key2_1 = "error2_1";
    $array_errors = array($key1 => $value_key1_2, $key2 => $value_key2_1);
    $error_names = array($key1, $key2);

    $this->request->setError($key1, $value_key1_1);
    $this->request->setErrors($array_errors);

    $this->assertTrue($this->request->hasError($key1));
    $this->assertTrue($this->request->hasErrors());
    $this->assertEqual($error_names, $this->request->getErrorNames());
    $this->assertEqual($array_errors, $this->request->getErrors());
    $this->assertEqual($value_key1_2, $this->request->getError($key1));
    $this->assertEqual($value_key1_2, $this->request->removeError($key1));
    $this->assertTrue($this->request->hasErrors());
    $this->assertEqual($value_key2_1, $this->request->removeError($key2));
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

  protected function populateVariables($request_uri, $with_layout)
  {
    $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    if ($request_uri[0] != '/')
    {
      $request_uri = '/'.$request_uri;
    }

    // add index.php if needed
    if (!strpos($request_uri, '.php'))
    {
      $request_uri = '/index.php'.$request_uri;
    }

    // query string
    $_SERVER['QUERY_STRING'] = '';
    if ($query_string_pos = strpos($request_uri, '?'))
    {
      $_SERVER['QUERY_STRING'] = substr($request_uri, $query_string_pos + 1);
    }
    else
    {
      $query_string_pos = strlen($request_uri);
    }

    // path info
    $_SERVER['PATH_INFO'] = '/';
    $script_pos = strpos($request_uri, '.php') + 5;
    if ($script_pos < $query_string_pos)
    {
      $_SERVER['PATH_INFO'] = '/'.substr($request_uri, $script_pos, $query_string_pos - $script_pos);
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
    if (!$with_layout)
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
