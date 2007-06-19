<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Francois Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
sfToolkit::clearDirectory(dirname(__FILE__).'/fixtures/project/cache');

$app = 'routing';
$debug = true;
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

$b = new sfTestBrowser();
$b->initialize();

$b->test()->diag('View class depending on routing');
$b->
  get('/')->
  isStatusCode(200)->
  isRequestParameter('module', 'default')->
  isRequestParameter('action', 'index')->
  isRequestParameter('sf_default_view', '')->
  checkResponseElement('body', '/congratulations/i')
;
$b->test()->is($b->getResponse()->getContentType(), 'text/html; charset=utf-8', 'content-type is html');
$b->
  get('/test/index')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'index')->
  isRequestParameter('sf_default_view', '')->
  checkResponseElement('body', '/html response/i')
;
$b->test()->is($b->getResponse()->getContentType(), 'text/html; charset=utf-8', 'content-type is html');
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'normal requests end up with a PHP View');
$b->
  get('/test/index/foo/bar')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'index')->
  isRequestParameter('foo', 'bar')->
  isRequestParameter('sf_default_view', '')->
  checkResponseElement('body', '/html response/i')
;
$b->test()->is($b->getResponse()->getContentType(), 'text/html; charset=utf-8', 'content-type is html');
$b->
  get('/js/test/index.pjs')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'index')->
  isRequestParameter('sf_default_view', 'sfJavascript')->
  responseContains('javascript response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfJavascriptView', 'PJS requests end up with a Javascript View');
$b->test()->is($b->getResponse()->getContentType(), 'application/x-javascript; charset=utf-8', 'content-type is x-javascript');
$b->
  get('/js/test/index/foo/bar.pjs')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'index')->
  isRequestParameter('foo', 'bar')->
  isRequestParameter('sf_default_view', 'sfJavascript')->
  responseContains('javascript response')
;
$b->test()->is($b->getResponse()->getContentType(), 'application/x-javascript; charset=utf-8', 'content-type is x-javascript');
$b->
  get('/not_js/test/index.pjs')->
  isStatusCode(404)
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'erroneous PJS requests end up with a PHP View');
$b->
  get('test/index.pjs')->
  isStatusCode(404)
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'erroneous PJS requests end up with a PHP View');
$b->
  get('/js/test/index')->
  isStatusCode(404)
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'erroneous PJS requests end up with a PHP View');
$b->
  get('/test/htmlOnly')->
  isRequestParameter('sf_default_view', '')->
  checkResponseElement('body', '/html response/i')
;
$b->
  get('/js/test/htmlOnly.js')->
  isStatusCode(404)
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'PJS requests to pages that exist in PHP only end up with a PHP View');


$b->test()->diag('');
$b->test()->diag('Multiformat actions');
$b->
  get('/test_multiformat.html')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformat')->
  responseContains('html response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'requests with html format end up with a PHP View');
$b->
  get('/test_multiformat.pjs')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformat')->
  responseContains('javascript response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfJavascriptView', 'requests with pjs format end up with a Javascript View');
$b->
  get('/test_multiformat.titi')->
  responseContains('There is no default view for format "titi"')
;
$b->
  get('/test_multiformat.toto')->
  responseContains('The format "toto" is not allowed in this action')
;

$b->test()->diag('Multiformat actions with default parameters');
$b->
  get('/test_multiformat_defaults.html')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatDefaults')->
  responseContains('html response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'requests with html format end up with a PHP View');
$b->
  get('/test_multiformat_defaults.pjs')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatDefaults')->
  responseContains('javascript response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfJavascriptView', 'requests with pjs format end up with a Javascript View');
$b->
  get('/test_multiformat_defaults.toto')->
  responseContains('There is no default view for format "toto"')
;

$b->test()->diag('Multiformat actions with undefined format');
$b->
  get('/test/testMultiformatUndefined')->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatUndefined')->
  responseContains('The format parameter "format" is not defined in the request')
;

$b->test()->diag('Multiformat actions implemented by hand');
$b->
  get('/test_multiformat_by_hand.html')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatByHand')->
  responseContains('html response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfPHPView', 'requests with html format end up with a PHP View');
$b->
  get('/test_multiformat_by_hand.pjs')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatByHand')->
  responseContains('javascript response')
;
$b->test()->isa_ok($b->getContext()->getCurrentViewInstance(), 'sfJavascriptView', 'requests with pjs format end up with a Javascript View');
$b->
  get('/test_multiformat_by_hand.toto')->
  isStatusCode(200)->
  isRequestParameter('module', 'test')->
  isRequestParameter('action', 'testMultiformatByHand')->
  responseContains('javascript response')
;

$b->test()->diag('');
$b->test()->diag('PJS helpers');
$b->
  get('/test/helper')->
  checkResponseElement('#test1', '/index.php/js/test/index.pjs')->
  checkResponseElement('#test2 script[type="text/javascript"][src="/index.php/js/test/index.pjs"]')->
  checkResponseElement('#test3 script[type="text/javascript"][src="/index.php/js/test/index.pjs"]')->
  checkResponseElement('#test4', '/index.php/js/test/index/foo/bar.pjs')->
  checkResponseElement('#test5', '/index.php/js/test/index.pjs?foo=bar')->
  checkResponseElement('#test6', 'http://routing-test/index.php/js/test/index.pjs')
;