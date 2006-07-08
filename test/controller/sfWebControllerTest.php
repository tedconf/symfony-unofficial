<?php

Mock::generate('sfContext');

class sfWebControllerTest extends UnitTestCase
{
  private $context;
  private $controller;

  public function SetUp()
  {
    sfConfig::set('sf_max_forwards', 10);
    $this->context = new MockSfContext($this);
    $this->controller = sfController::newInstance('sfFrontWebController');
    $this->controller->initialize($this->context, null);
  }

  public function test_simple()
  {
    $c = $this->controller;

    $this->assertEqual(array('',
        array(
          'module' => 'module',
          'action' => 'action'
        )
      ),
      $c->convertUrlStringToParameters('module/action'));

    $this->assertEqual(array('',
        array(
          'module' => 'module',
          'action' => 'action',
          'id'     => 12
        )
      ),
      $c->convertUrlStringToParameters('module/action?id=12'));

    $this->assertEqual(array('',
        array(
          'module' => 'module',
          'action' => 'action',
          'id'     => 12,
          'test'   => 4,
          'toto'   => 9
        )
      ),
      $c->convertUrlStringToParameters('module/action?id=12&test=4&toto=9'));

    $this->assertEqual(array('test', array('test' => 4)),
      $c->convertUrlStringToParameters('@test?test=4'));

    $this->assertEqual(array('test', array()), $c->convertUrlStringToParameters('@test'));

    $this->assertEqual(array('test',
        array(
          'id' => 12,
          'foo' => 'bar'
        )
      ),
      $c->convertUrlStringToParameters('@test?id=12&foo=bar'));
  }
}
