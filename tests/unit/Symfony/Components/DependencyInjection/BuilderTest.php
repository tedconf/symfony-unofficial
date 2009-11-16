<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../../../lib/vendor/lime/LimeAutoloader.php';
LimeAutoloader::register();

require_once __DIR__.'/../../../../../lib/Symfony/Foundation/ClassLoader.php';
$loader = new Symfony\Foundation\ClassLoader('Symfony', __DIR__.'/../../../../../lib');
$loader->register();

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;

$fixturesPath = __DIR__.'/../../../../fixtures/Symfony/Components/DependencyInjection/';

$t = new LimeTest(46);

// ->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()
$t->diag('->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()');
$builder = new Builder();
$definitions = array(
  'foo' => new Definition('FooClass'),
  'bar' => new Definition('BarClass'),
);
$builder->setServiceDefinitions($definitions);
$t->is($builder->getServiceDefinitions(), $definitions, '->setServiceDefinitions() sets the service definitions');
$t->ok($builder->hasServiceDefinition('foo'), '->hasServiceDefinition() returns true if a service definition exists');
$t->ok(!$builder->hasServiceDefinition('foobar'), '->hasServiceDefinition() returns false if a service definition does not exist');

$builder->setServiceDefinition('foobar', $foo = new Definition('FooBarClass'));
$t->is($builder->getServiceDefinition('foobar'), $foo, '->getServiceDefinition() returns a service definition if defined');
$t->ok($builder->setServiceDefinition('foobar', $foo = new Definition('FooBarClass')) === $foo, '->setServiceDefinition() implements a fuild interface by returning the service reference');

$builder->addServiceDefinitions($defs = array('foobar' => new Definition('FooBarClass')));
$t->is($builder->getServiceDefinitions(), array_merge($definitions, $defs), '->addServiceDefinitions() adds the service definitions');

try
{
  $builder->getServiceDefinition('baz');
  $t->fail('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
}

// ->register()
$t->diag('->register()');
$builder = new Builder();
$builder->register('foo', 'FooClass');
$t->ok($builder->hasServiceDefinition('foo'), '->register() registers a new service definition');
$t->ok($builder->getServiceDefinition('foo') instanceof Definition, '->register() returns the newly created Definition instance');

// ->hasService()
$t->diag('->hasService()');
$builder = new Builder();
$t->ok(!$builder->hasService('foo'), '->hasService() returns false if the service does not exist');
$builder->register('foo', 'FooClass');
$t->ok($builder->hasService('foo'), '->hasService() returns true if a service definition exists');
$builder->bar = new stdClass();
$t->ok($builder->hasService('bar'), '->hasService() returns true if a service exists');

// ->getService()
$t->diag('->getService()');
$builder = new Builder();
try
{
  $builder->getService('foo');
  $t->fail('->getService() throws an InvalidArgumentException if the service does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->getService() throws an InvalidArgumentException if the service does not exist');
}
$builder->register('foo', 'stdClass');
$t->ok(is_object($builder->getService('foo')), '->getService() returns the service definition associated with the id');
$builder->bar = $bar = new stdClass();
$t->is($builder->getService('bar'), $bar, '->getService() returns the service associated with the id');
$builder->register('bar', 'stdClass');
$t->is($builder->getService('bar'), $bar, '->getService() returns the service associated with the id even if a definition has been defined');

$builder->register('baz', 'stdClass')->setArguments(array(new Reference('baz')));
try
{
  @$builder->getService('baz');
  $t->fail('->getService() throws a LogicException if the service has a circular reference to itself');
}
catch (LogicException $e)
{
  $t->pass('->getService() throws a LogicException if the service has a circular reference to itself');
}

$builder->register('foobar', 'stdClass')->setShared(true);
$t->ok($builder->getService('bar') === $builder->getService('bar'), '->getService() always returns the same instance if the service is shared');

// ->getServiceIds()
$t->diag('->getServiceIds()');
$builder = new Builder();
$builder->register('foo', 'stdClass');
$builder->bar = $bar = new stdClass();
$builder->register('bar', 'stdClass');
$t->is($builder->getServiceIds(), array('foo', 'bar', 'service_container'), '->getServiceIds() returns all defined service ids');

// ->setAlias()
$t->diag('->setAlias()');
$builder = new Builder();
$builder->register('foo', 'stdClass');
$builder->setAlias('bar', 'foo');
$t->ok($builder->hasService('bar'), '->setAlias() defines a new service');
$t->ok($builder->getService('bar') === $builder->getService('foo'), '->setAlias() creates a service that is an alias to another one');

// ->getAliases()
$t->diag('->getAliases()');
$builder = new Builder();
$builder->setAlias('bar', 'foo');
$builder->setAlias('foobar', 'foo');
$t->is($builder->getAliases(), array('bar' => 'foo', 'foobar' => 'foo'), '->getAliases() returns all service aliases');
$builder->register('bar', 'stdClass');
$t->is($builder->getAliases(), array('foobar' => 'foo'), '->getAliases() does not return aliased services that have been overridden');
$builder->setService('foobar', 'stdClass');
$t->is($builder->getAliases(), array(), '->getAliases() does not return aliased services that have been overridden');

// ->createService() # file
$t->diag('->createService() # file');
$builder = new Builder();
$builder->register('foo1', 'FooClass')->setFile($fixturesPath.'/includes/foo.php');
$t->ok($builder->getService('foo1'), '->createService() requires the file defined by the service definition');
$builder->register('foo2', 'FooClass')->setFile($fixturesPath.'/includes/%file%.php');
$builder->setParameter('file', 'foo');
$t->ok($builder->getService('foo2'), '->createService() replaces parameters in the file provided by the service definition');

// ->createService() # class
$t->diag('->createService() # class');
$builder = new Builder();
$builder->register('foo1', '%class%');
$builder->setParameter('class', 'stdClass');
$t->ok($builder->getService('foo1') instanceof stdClass, '->createService() replaces parameters in the class provided by the service definition');

// ->createService() # arguments
$t->diag('->createService() # arguments');
$builder = new Builder();
$builder->register('bar', 'stdClass');
$builder->register('foo1', 'FooClass')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new Reference('bar')));
$builder->setParameter('value', 'bar');
$t->is($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() replaces parameters and service references in the arguments provided by the service definition');

// ->createService() # constructor
$t->diag('->createService() # constructor');
$builder = new Builder();
$builder->register('bar', 'stdClass');
$builder->register('foo1', 'FooClass')->setConstructor('getInstance')->addArgument(array('foo' => '%value%', '%value%' => 'foo', new Reference('bar')));
$builder->setParameter('value', 'bar');
$t->ok($builder->getService('foo1')->called, '->createService() calls the constructor to create the service instance');
$t->is($builder->getService('foo1')->arguments, array('foo' => 'bar', 'bar' => 'foo', $builder->getService('bar')), '->createService() passes the arguments to the constructor');

// ->createService() # method calls
$t->diag('->createService() # method calls');
$builder = new Builder();
$builder->register('bar', 'stdClass');
$builder->register('foo1', 'FooClass')->addMethodCall('setBar', array(array('%value%', new Reference('bar'))));
$builder->setParameter('value', 'bar');
$t->is($builder->getService('foo1')->bar, array('bar', $builder->getService('bar')), '->createService() replaces the values in the method calls arguments');

// ->createService() # configurator
require_once $fixturesPath.'/includes/classes.php';
$t->diag('->createService() # configurator');
$builder = new Builder();
$builder->register('foo1', 'FooClass')->setConfigurator('sc_configure');
$t->ok($builder->getService('foo1')->configured, '->createService() calls the configurator');

$builder->register('foo2', 'FooClass')->setConfigurator(array('%class%', 'configureStatic'));
$builder->setParameter('class', 'BazClass');
$t->ok($builder->getService('foo2')->configured, '->createService() calls the configurator');

$builder->register('baz', 'BazClass');
$builder->register('foo3', 'FooClass')->setConfigurator(array(new Reference('baz'), 'configure'));
$t->ok($builder->getService('foo3')->configured, '->createService() calls the configurator');

$builder->register('foo4', 'FooClass')->setConfigurator('foo');
try
{
  $builder->getService('foo4');
  $t->fail('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->createService() throws an InvalidArgumentException if the configure callable is not a valid callable');
}

// ->resolveValue()
$t->diag('->resolveValue()');
$builder = new Builder();
$t->is($builder->resolveValue('foo'), 'foo', '->resolveValue() returns its argument unmodified if no placeholders are found');
$builder->setParameter('foo', 'bar');
$t->is($builder->resolveValue('I\'m a %foo%'), 'I\'m a bar', '->resolveValue() replaces placeholders by their values');
$builder->setParameter('foo', true);
$t->ok($builder->resolveValue('%foo%') === true, '->resolveValue() replaces arguments that are just a placeholder by their value without casting them to strings');

$builder->setParameter('foo', 'bar');
$t->is($builder->resolveValue(array('%foo%' => '%foo%')), array('bar' => 'bar'), '->resolveValue() replaces placeholders in keys and values of arrays');

$t->is($builder->resolveValue(array('%foo%' => array('%foo%' => array('%foo%' => '%foo%')))), array('bar' => array('bar' => array('bar' => 'bar'))), '->resolveValue() replaces placeholders in nested arrays');

$t->is($builder->resolveValue('I\'m a %%foo%%'), 'I\'m a %foo%', '->resolveValue() supports % escaping by doubling it');
$t->is($builder->resolveValue('I\'m a %foo% %%foo %foo%'), 'I\'m a bar %foo bar', '->resolveValue() supports % escaping by doubling it');

try
{
  $builder->resolveValue('%foobar%');
  $t->fail('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
}
catch (RuntimeException $e)
{
  $t->pass('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
}

try
{
  $builder->resolveValue('foo %foobar% bar');
  $t->fail('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
}
catch (RuntimeException $e)
{
  $t->pass('->resolveValue() throws a RuntimeException if a placeholder references a non-existant parameter');
}

// ->resolveServices()
$t->diag('->resolveServices()');
$builder = new Builder();
$builder->register('foo', 'FooClass');
$t->is($builder->resolveServices(new Reference('foo')), $builder->getService('foo'), '->resolveServices() resolves service references to service instances');
$t->is($builder->resolveServices(array('foo' => array('foo', new Reference('foo')))), array('foo' => array('foo', $builder->getService('foo'))), '->resolveServices() resolves service references to service instances in nested arrays');
