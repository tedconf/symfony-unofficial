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
