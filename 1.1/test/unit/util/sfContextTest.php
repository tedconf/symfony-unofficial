<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(32, new lime_output_color());

class myContext extends sfContext
{
  public function initialize(sfApplicationConfiguration $configuration)
  {
    $this->configuration = $configuration;
  }
}

class ProjectConfiguration extends sfProjectConfiguration
{
}

class testConfiguration extends sfApplicationConfiguration
{
}

$configuration = new testConfiguration('test', true, $sf_root_dir);
$context1 = sfContext::createInstance($configuration, 'context1', 'myContext');
$context2 = sfContext::createInstance($configuration, 'context2', 'myContext');

foreach (array('initialize', 'loadFactories', 'getInstance', 'hasInstance', 'get', 'set', 'has', 'switchTo', 'getActionName', 'getActionStack', 'getLogger', 'getEventDispatcher', 'getConfiguration', 'getConfigCache', 'getCache', 'getController', 'dispatch', 'getRequest', 'getResponse', 'getRouting', 'getI18n', 'getUser', 'getViewCacheManager', 'getDatabaseConnection') as $method)
{
  $t->can_ok($context1, $method, sprintf('"%s" is a method of sfContext', $method));
}

// ::getInstance()
$t->diag('::getInstance()');
$t->isa_ok(sfContext::getInstance('context1', 'myContext'), 'myContext', '::getInstance() takes a sfContext class name as its second argument');

$t->is(sfContext::getInstance('context1'), $context1, '::getInstance() returns the named context if it already exists');

// ::switchTo();
$t->diag('::switchTo()');
sfContext::switchTo('context1');
$t->is(sfContext::getInstance(), $context1, '::switchTo() changes the default context instance returned by ::getInstance()');
sfContext::switchTo('context2');
$t->is(sfContext::getInstance(), $context2, '::switchTo() changes the default context instance returned by ::getInstance()');


// ->get() ->set() ->has()
$t->diag('->get() ->set() ->has()');
$t->is($context1->has('object'), false, '->has() returns false if no object of the given name exist');
$object = new stdClass();
$context1->set('object', $object, '->set() stores an object in the current context instance');
$t->is($context1->has('object'), true, '->has() returns true if an object is stored for the given name');
$t->is($context1->get('object'), $object, '->get() returns the object associated with the given name');
try
{
  $context1->get('object1');
  $t->fail('->get() throws an sfException if no object is stored for the given name');
}
catch (sfException $e)
{
  $t->pass('->get() throws an sfException if no object is stored for the given name');
}
