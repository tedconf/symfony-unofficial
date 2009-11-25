<?php

use Symfony\Components\DependencyInjection\Container;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\Parameter;

class ProjectServiceContainer extends Container
{
  protected $shared = array();

  public function __construct()
  {
    parent::__construct($this->getDefaultParameters());
  }

  protected function getFooService()
  {
    require_once '%path%/foo.php';

    $instance = call_user_func(array('FooClass', 'getInstance'), 'foo', $this->getService('foo.baz'), array($this->getParameter('foo') => 'foo is '.$this->getParameter('foo')), true, $this);
    $instance->setBar('bar');
    $instance->initialize();
    sc_configure($instance);

    return $instance;
  }

  protected function getBarService()
  {
    if (isset($this->shared['bar'])) return $this->shared['bar'];

    $instance = new FooClass('foo', $this->getService('foo.baz'), $this->getParameter('foo_bar'));
    $this->getService('foo.baz')->configure($instance);

    return $this->shared['bar'] = $instance;
  }

  protected function getFoo_BazService()
  {
    if (isset($this->shared['foo.baz'])) return $this->shared['foo.baz'];

    $instance = call_user_func(array($this->getParameter('baz_class'), 'getInstance'));
    call_user_func(array($this->getParameter('baz_class'), 'configureStatic1'), $instance);

    return $this->shared['foo.baz'] = $instance;
  }

  protected function getFooBarService()
  {
    if (isset($this->shared['foo_bar'])) return $this->shared['foo_bar'];

    $instance = new FooClass();

    return $this->shared['foo_bar'] = $instance;
  }

  protected function getMethodCall1Service()
  {
    if (isset($this->shared['method_call1'])) return $this->shared['method_call1'];

    $instance = new FooClass();
    $instance->setBar($this->getService('foo'));
    $instance->setBar($this->getService('foo', Container::NULL_ON_INVALID_REFERENCE));
    if ($this->hasService('foo'))
    {
      $instance->setBar($this->getService('foo', Container::NULL_ON_INVALID_REFERENCE));
    }

    return $this->shared['method_call1'] = $instance;
  }

  protected function getAliasForFooService()
  {
    return $this->getService('foo');
  }

  protected function getDefaultParameters()
  {
    return array(
      'baz_class' => 'BazClass',
      'foo' => 'bar',
      'foo_bar' => new Reference('foo_bar'),
    );
  }
}
