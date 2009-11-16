<?php

require_once __DIR__.'/../includes/classes.php';

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\Parameter;

$container = new Builder();
$container->
  register('foo', 'FooClass')->
  setConstructor('getInstance')->
  setArguments(array('foo', new Reference('foo.baz'), array('%foo%' => 'foo is %foo%'), true, new Reference('service_container')))->
  setFile(realpath(__DIR__.'/../includes/foo.php'))->
  setShared(false)->
  addMethodCall('setBar', array('bar'))->
  addMethodCall('initialize')->
  setConfigurator('sc_configure')
;
$container->
  register('bar', 'FooClass')->
  setArguments(array('foo', new Reference('foo.baz'), new Parameter('foo_bar')))->
  setShared(true)->
  setConfigurator(array(new Reference('foo.baz'), 'configure'))
;
$container->
  register('foo.baz', '%baz_class%')->
  setConstructor('getInstance')->
  setConfigurator(array('%baz_class%', 'configureStatic1'))
;
$container->register('foo_bar', 'FooClass');
$container->setParameters(array(
  'baz_class' => 'BazClass',
  'foo' => 'bar',
  'foo_bar' => new Reference('foo_bar'),
));
$container->setAlias('alias_for_foo', 'foo');

return $container;
