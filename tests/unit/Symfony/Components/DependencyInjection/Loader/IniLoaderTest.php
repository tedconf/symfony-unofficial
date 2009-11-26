<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../../../bootstrap.php';

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Loader\IniFileLoader;

$t = new LimeTest(5);

$fixturesPath = realpath(__DIR__.'/../../../../../fixtures/Symfony/Components/DependencyInjection/');

$loader = new IniFileLoader($container = new Builder(), $fixturesPath.'/ini');
$loader->load(array('parameters.ini'));
$t->is($container->getParameters(), array('foo' => 'bar', 'bar' => 'bar'), '->load() takes an array of file names as its first argument');

$loader = new IniFileLoader($container = new Builder(), $fixturesPath.'/ini');
$loader->load('parameters.ini');
$t->is($container->getParameters(), array('foo' => 'bar', 'bar' => 'bar'), '->load() takes a single file name as its first argument');

$loader = new IniFileLoader($container = new Builder(), $fixturesPath.'/ini');
$loader->load(array('parameters.ini', 'parameters1.ini'));
$t->is($container->getParameters(), array('foo' => 'foo', 'bar' => 'foo', 'baz' => 'baz'), '->load() merges parameters from all given files');

try
{
  $loader->load('foo.ini');
  $t->fail('->load() throws an InvalidArgumentException if the loaded file does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->load() throws an InvalidArgumentException if the loaded file does not exist');
}

try
{
  @$loader->load('nonvalid.ini');
  $t->fail('->load() throws an InvalidArgumentException if the loaded file is not parseable');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->load() throws an InvalidArgumentException if the loaded file is not parseable');
}
