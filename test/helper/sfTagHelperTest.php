<?php

require_once 'helper/TagHelper.php';

Mock::generate('sfContext');

class sfTagHelperTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_tag()
  {
    $this->assertEqual('', tag(''));
    $this->assertEqual('<br />', tag('br'));
    $this->assertEqual('<p>', tag('p', null, true));
    $this->assertEqual('<br class="foo" />', tag('br', array('class' => 'foo'), false));
    $this->assertEqual('<br class="foo" />', tag('br', 'class=foo', false));
    $this->assertEqual('<p class="foo" id="bar">',
      tag('p', array('class' => 'foo', 'id' => 'bar'), true));
    //$this->assertEqual('<br class="&quot;foo&quot;" />', tag('br', array('class' => '"foo"')));
  }

  public function test_content_tag()
  {
    $this->assertEqual('', content_tag(''));
    $this->assertEqual('', content_tag('', ''));
    $this->assertEqual('<p>Toto</p>', content_tag('p', 'Toto'));
    $this->assertEqual('<p></p>', content_tag('p', ''));
  }

  public function test_cdata_section()
  {
    $this->assertEqual('<![CDATA[]]>', cdata_section(''));
    $this->assertEqual('<![CDATA[foobar]]>', cdata_section('foobar'));
  }

  public function test_escape_javascript()
  {
    $this->assertEqual('alert(\\\'foo\\\');\\nalert(\\"bar\\");',
      escape_javascript("alert('foo');\nalert(\"bar\");"));
  }
}
