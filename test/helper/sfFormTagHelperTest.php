<?php

require_once 'helper/TagHelper.php';
require_once 'helper/FormHelper.php';

Mock::generate('sfContext');

class sfFormTagHelperTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_form_checkbox_tag()
  {
    $this->assertEqual('<input type="checkbox" name="admin" id="admin" value="1" />',
      checkbox_tag('admin'));
  }

  public function test_form_input_hidden_tag()
  {
    $this->assertEqual('<input type="hidden" name="id" id="id" value="3" />',
      input_hidden_tag('id', 3));

  }

  public function test_form_input_password_tag()
  {
    $this->assertEqual('<input type="password" name="password" id="password" value="" />',
      input_password_tag());
  }

  public function test_form_radiobutton_tag()
  {
    $this->assertEqual('<input type="radio" name="people" value="david" />',
      radiobutton_tag("people", "david"));
  }

  public function test_options_for_select()
  {
    $this->assertEqual("<option value=\"0\" selected=\"selected\">item1</option>\n<option value=\"1\">item2</option>\n",
      options_for_select(array('item1', 'item2'), '0'));
  }

  public function test_form_select_tag()
  {
    $this->assertEqual('<select name="people" id="people"><option>david</option></select>',
      select_tag("people", "<option>david</option>"));
  }

  public function test_form_textarea_tag_size()
  {
    $this->assertEqual('<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>',
      textarea_tag("body", "hello world", array("size" => "20x40")));

    $this->assertEqual('<textarea name="body" id="body" rows="40" cols="20">hello world</textarea>',
      textarea_tag("body", "hello world", "size=20x40"));
  }

  public function test_form_input_tag()
  {
    $this->assertEqual('<input type="text" name="title" id="title" value="Hello!" />',
      input_tag("title", "Hello!"));
  }

  public function test_form_input_tag_class_string()
  {
    $this->assertEqual('<input type="text" name="title" id="title" value="Hello!" class="admin" />',
      input_tag('title', 'Hello!', array('class' => 'admin')));

    $this->assertEqual('<input type="text" name="title" id="title" value="Hello!" class="admin" />',
      input_tag('title', 'Hello!', 'class=admin'));
  }

/*
  public function test_form_tag()
  {
    $this->assertEqual('<form action="http://www.example.com" method="post">', tag());
  }

  public function test_form_tag_multipart()
  {
    $this->assertEqual('<form action="http://www.example.com" enctype="multipart/form-data" method="post">',
      form_tag(null, array('multipart' => true )));

  }
*/

  public function test_boolean_optios()
  {
    $this->assertEqual('<input type="checkbox" name="admin" id="admin" value="1" disabled="disabled" readonly="readonly" checked="checked" />',
      checkbox_tag('admin', 1, true, array('disabled' => true, 'readonly' => 'yes')));

    $this->assertEqual('<input type="checkbox" name="admin" id="admin" value="1" checked="checked" />',
      checkbox_tag('admin', 1, true, array('disabled' => false, 'readonly' => null)));

    $this->assertEqual('<select name="people[]" id="people" multiple="multiple"><option>david</option></select>',
      select_tag('people', "<option>david</option>", array('multiple' => true)));

    $this->assertEqual('<select name="people" id="people"><option>david</option></select>',
      select_tag('people', "<option>david</option>", array('multiple' => null)));
  }

  public function test_stringify_symbol_keys()
  {
    $this->assertEqual('<input type="text" name="title" id="admin" value="Hello!" />',
      input_tag('title', 'Hello!', array('id'=> 'admin')));
  }

}

?>