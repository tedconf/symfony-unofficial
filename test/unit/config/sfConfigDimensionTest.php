<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'unit.php');

$t = new lime_test(18, new lime_output_color());

// use fixtures
define('SF_ROOT_DIR', $_test_dir.DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'project');

// ->getInstance()
$t->diag('->getInstance()');
$t->isa_ok(sfConfigDimension::getInstance(), 'sfConfigDimension', 'returns a sfConfigDimension instance');
$t->is(sfConfigDimension::getInstance(), sfConfigDimension::getInstance(), 'is a singleton');

$t->diag('->initialize()');
sfConfigDimension::getInstance()->initialize();

$t->is(sfConfigDimension::getInstance()->get(), array('culture' => 'en',  'theme' => 'classic',  'host' => 'production'), 'dimension is valid after initialization');
$t->is(sfConfigDimension::getInstance()->getDefault(), array('culture' => 'en',  'theme' => 'classic',  'host' => 'production'), 'default is valid after initialization');
$t->is(sfConfigDimension::getInstance()->getAllowed(), array('culture' => array('en', 'fr', 'it', 'de'), 'theme' => array('classic', 'mybrand'), 'host' => array('production', 'development', 'qa', 'staging')), 'allowed is valid after initialization');
$t->is(sfConfigDimension::getInstance()->getCascade(), array(0 => 'en_classic_production', 1 => 'en_classic', 2 => 'production', 3 => 'classic', 4 => 'en'), 'cascade is valid after initialization');

$t->diag('::clean()');
$t->is(sfConfigDimension::getInstance()->clean(array('culture' => 'en', 'theme' => null, 'host' => null)), array('culture' => 'en'), 'removes keys with null values');
$t->is(sfConfigDimension::getInstance()->clean(array('culture' => 'en', 'theme' => 'CLASSIC', 'host' => 'PRODUCTION')), array('culture' => 'en', 'theme' => 'classic', 'host' => 'production'), 'strtolower all values');

$t->diag('::check()');

$t->diag('::set()');
try
{
  sfConfigDimension::getInstance()->check(array('culture' => 'ru', 'theme' => 'v2', 'host' => 'development'));
  $t->fail('does not throw exception on setting of invalid dimension');
}
catch (Exception $e)
{
  $t->pass('check throws exception when given an invalid dimension');
}

$t->diag('::set()');
try
{
  sfConfigDimension::getInstance()->set(array('culture' => 'ru', 'theme' => 'v2', 'host' => 'development'));
  $t->fail('does not throw exception on setting of invalid dimension');
}
catch (Exception $e)
{
  $t->pass('can not set an invalid dimension');
}

sfConfigDimension::getInstance()->set(array('culture' => 'en', 'theme' => 'classic', 'host' => 'production'));
$t->is(sfConfigDimension::getInstance()->get(), array('culture' => 'en', 'theme' => 'classic', 'host' => 'production'), 'can set a valid dimension');

$t->diag('::get()');
$t->is(sfConfigDimension::getInstance()->get(), array('culture' => 'en',  'theme' => 'classic',  'host' => 'production'), 'dimension is valid');
$t->is(sfConfigDimension::getInstance()->getCascade(), array(0 => 'en_classic_production', 1 => 'en_classic', 2 => 'production', 3 => 'classic', 4 => 'en'), 'cascade is valid with most specific dimension');
$t->is(sfConfigDimension::getInstance()->__toString(), 'en_classic_production', 'dimension string is valid');

$t->diag('::set() simple dimension');
sfConfigDimension::getInstance()->set(array('culture' => 'en'));
$t->is(sfConfigDimension::getInstance()->get(), array('culture' => 'en'), 'can set a simple dimension');
$t->is(sfConfigDimension::getInstance()->get(), array('culture' => 'en'), 'simple dimension is valid');
$t->is(sfConfigDimension::getInstance()->getCascade(), array(0 => 'en'), 'simple cascade is valid');
$t->is(sfConfigDimension::getInstance()->__toString(), 'en', 'simple dimension string is valid');
