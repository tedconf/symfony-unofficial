<?php

class sfToolkitTest extends UnitTestCase
{
  public function test_stringToArray()
  {
    $this->assertEqual(array('foo' => 'bar'), sfToolkit::stringToArray('foo=bar'));

    $this->assertEqual(array('foo1' => 'bar1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1=bar1 foo=bar   '));

    $this->assertEqual(array('foo1' => 'bar1 foo1'), sfToolkit::stringToArray('foo1="bar1 foo1"'));

    $this->assertEqual(array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1="bar1 foo1" foo=bar'));

    $this->assertEqual(array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1 = "bar1=foo1" foo=bar'));

    $this->assertEqual(array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1= \'bar1 foo1\'    foo  =     bar'));

    $this->assertEqual(array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1=\'bar1=foo1\' foo = bar'));

    $this->assertEqual(array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1=  bar1 foo1 foo=bar'));

    $this->assertEqual(array('foo1' => 'l\'autre', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1="l\'autre" foo=bar'));

    $this->assertEqual(array('foo1' => 'l"autre', 'foo' => 'bar'),
      sfToolkit::stringToArray('foo1="l"autre" foo=bar'));

    $this->assertEqual(array('foo_1' => 'bar_1'), sfToolkit::stringToArray('foo_1=bar_1'));
  }
}
