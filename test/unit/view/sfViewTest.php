<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(14, new lime_output_color());

class myView extends sfView
{
  function execute () {}
  function configure () {}
  function getEngine () {}
  function render () {}
}

class sfViewParameterHolder extends sfParameterHolder
{
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;
    $this->add($parameters);
  }

  public function toArray()
  {
    return $this->getAll();
  }
}

$context = sfContext::getInstance();
$view = new myView($context, '', '', '');

// ->isDecorator() ->setDecorator()
$t->diag('->isDecorator() ->setDecorator()');
$t->is($view->isDecorator(), false, '->isDecorator() returns true if the current view have to be decorated');
$view->setDecorator(true);
$t->is($view->isDecorator(), true, '->setDecorator() sets the decorator status for the view');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($view, 'parameter');

// new methods via sfEventDispatcher
require_once($_test_dir.'/unit/sfEventDispatcherTest.class.php');
$dispatcherTest = new sfEventDispatcherTest($t);
$dispatcherTest->launchTests($context->getEventDispatcher(), $view, 'view');
