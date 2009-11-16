<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../../../../lib/vendor/lime/LimeAutoloader.php';
LimeAutoloader::register();

require_once __DIR__.'/../../../../../lib/SymfonyTests/ClassLoader.php';
$loader = new Symfony\Tests\ClassLoader('Symfony', __DIR__.'/../../../../../../lib');
$loader->register();

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Loader\Loader;

$t = new LimeTest(11);

class ProjectLoader extends Loader
{
  public $container;

  public function doLoad($resource)
  {
    return $resource;
  }
}

// __construct()
$t->diag('__construct()');
$loader = new ProjectLoader($container = new Builder());
$t->is($loader->container, $container, '__construct() takes a container builder instance as its first argument');

// ->setServiceContainer()
$t->diag('->setServiceContainer()');
$loader = new ProjectLoader();
$loader->setServiceContainer($container = new Builder());
$t->is($loader->container, $container, '->setServiceContainer() sets the container builder attached to this loader');

// ->load()
$t->diag('->load()');
$loader = new ProjectLoader();
try
{
  $loader->load('foo');
  $t->fail('->load() throws a LogicException if no container is attached to the loader');
}
catch (LogicException $e)
{
  $t->pass('->load() throws a LogicException if no container is attached to the loader');
}

$loader->setServiceContainer($container = new Builder(array('bar' => 'foo')));
$loader->load(array(array(), array('foo' => 'bar')));
$t->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'bar'), '->load() merges current parameters with the loaded ones');

$loader->setServiceContainer($container = new Builder(array('bar' => 'foo', 'foo' => 'baz')));
$loader->load(array(array(), array('foo' => 'bar')));
$t->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'baz'), '->load() does not change the already defined parameters');

$loader->setServiceContainer($container = new Builder(array('bar' => 'foo')));
$loader->load(array(array(), array('foo' => '%bar%')));
$t->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo'), '->load() evaluates the values of the parameters towards already defined ones');

$loader->setServiceContainer($container = new Builder(array('bar' => 'foo')));
$loader->load(array(array(), array('foo' => '%bar%', 'baz' => '%foo%')));
$t->is($container->getParameters(), array('bar' => 'foo', 'foo' => 'foo', 'baz' => 'foo'), '->load() evaluates the values of the parameters towards already defined ones');

$loader->setServiceContainer($container = new Builder());
$container->register('foo', 'FooClass');
$container->register('bar', 'BarClass');
$loader->load(array(array('baz' => new Definition('BazClass'), 'alias_for_foo' => 'foo'), array()));
$t->is(array_keys($container->getServiceDefinitions()), array('foo', 'bar', 'baz'), '->load() merges definitions already defined ones');
$t->is($container->getAliases(), array('alias_for_foo' => 'foo'), '->load() registers defined aliases');

$loader->setServiceContainer($container = new Builder());
$container->register('foo', 'FooClass');
$loader->load(array(array('foo' => new Definition('BazClass')), array()));
$t->is($container->getServiceDefinition('foo')->getClass(), 'BazClass', '->load() overrides already defined services');

$loader->setServiceContainer($container = new Builder());
$loader->load(array(array(), array('foo' => 'bar')), array(array(), array('bar' => 'foo')));
$t->is($container->getParameters(), array('foo' => 'bar', 'bar' => 'foo'), '->load() accepts several resources as argument');
