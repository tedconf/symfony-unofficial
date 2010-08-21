<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * ProjectServiceContainer
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class ProjectServiceContainer extends Container
{
    protected $shared = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(new ParameterBag($this->getDefaultParameters()));
    }

    /**
     * Gets the 'foo' service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getFooService()
    {
        require_once '%path%foo.php';

        $instance = call_user_func(array('FooClass', 'getInstance'), 'foo', $this->getFoo_BazService(), array($this->getParameter('foo') => 'foo is '.$this->getParameter('foo'), 'bar' => $this->getParameter('foo')), true, $this);
        $instance->setBar('bar');
        $instance->initialize();
        sc_configure($instance);

        return $instance;
    }

    /**
     * Gets the 'bar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getBarService()
    {
        if (isset($this->shared['bar'])) return $this->shared['bar'];

        $instance = new FooClass('foo', $this->getFoo_BazService(), $this->getParameter('foo_bar'));
        $this->shared['bar'] = $instance;
        $this->getFoo_BazService()->configure($instance);

        return $instance;
    }

    /**
     * Gets the 'foo.baz' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %baz_class% instance.
     */
    protected function getFoo_BazService()
    {
        if (isset($this->shared['foo.baz'])) return $this->shared['foo.baz'];

        $instance = call_user_func(array($this->getParameter('baz_class'), 'getInstance'));
        $this->shared['foo.baz'] = $instance;
        call_user_func(array($this->getParameter('baz_class'), 'configureStatic1'), $instance);

        return $instance;
    }

    /**
     * Gets the 'foo_bar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object A %foo_class% instance.
     */
    protected function getFooBarService()
    {
        if (isset($this->shared['foo_bar'])) return $this->shared['foo_bar'];

        $class = $this->getParameter('foo_class');
        $instance = new $class();
        $this->shared['foo_bar'] = $instance;

        return $instance;
    }

    /**
     * Gets the 'method_call1' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return FooClass A FooClass instance.
     */
    protected function getMethodCall1Service()
    {
        if (isset($this->shared['method_call1'])) return $this->shared['method_call1'];

        $instance = new FooClass();
        $this->shared['method_call1'] = $instance;
        $instance->setBar($this->getFooService());
        $instance->setBar($this->get('foo', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        if ($this->has('foo')) {
            $instance->setBar($this->get('foo', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }
        if ($this->has('foobaz')) {
            $instance->setBar($this->get('foobaz', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }

        return $instance;
    }

    /**
     * Gets the 'factory_service' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return Object An instance returned by foo.baz::getInstance().
     */
    protected function getFactoryServiceService()
    {
        if (isset($this->shared['factory_service'])) return $this->shared['factory_service'];

        $instance = $this->getFoo_BazService()->getInstance();
        $this->shared['factory_service'] = $instance;

        return $instance;
    }

    /**
     * Gets the alias_for_foo service alias.
     *
     * @return FooClass An instance of the foo service
     */
    protected function getAliasForFooService()
    {
        return $this->getFooService();
    }

    /**
     * Returns service ids for a given tag.
     *
     * @param string $name The tag name
     *
     * @return array An array of tags
     */
    public function findTaggedServiceIds($name)
    {
        static $tags = array (
  'foo' => 
  array (
    'foo' => 
    array (
      0 => 
      array (
        'foo' => 'foo',
      ),
      1 => 
      array (
        'bar' => 'bar',
      ),
    ),
  ),
);

        return isset($tags[$name]) ? $tags[$name] : array();
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'baz_class' => 'BazClass',
            'foo_class' => 'FooClass',
            'foo' => 'bar',
        );
    }
}
