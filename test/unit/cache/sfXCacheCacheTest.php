<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$plan = 60;
$t = new lime_test($plan, new lime_output_color());

if (!function_exists('xcache_set'))
{
  $t->skip('XCache needed to run these tests', $plan);
  return;
}

$_SERVER['PHP_AUTH_USER'] = 'test';
$_SERVER['PHP_AUTH_PW'] = 'password';

require_once(dirname(__FILE__).'/sfCacheDriverTests.class.php');

// setup
sfConfig::set('sf_logging_enabled', false);

// ->initialize()
$t->diag('->initialize()');
$cache = new sfXCacheCache();
$cache->initialize();

sfCacheDriverTests::launch($t, $cache);
