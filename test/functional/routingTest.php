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
