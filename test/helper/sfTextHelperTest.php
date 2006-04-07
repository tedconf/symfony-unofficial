<?php

require_once 'helper/TextHelper.php';

Mock::generate('sfContext');

class sfTextHelperTest extends UnitTestCase
{
  private $context;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

  public function test_text_truncate()
  {
    $this->assertEqual('Test', truncate_text('Test'));
    $this->assertEqual(str_repeat('A', 27).'...', truncate_text(str_repeat('A', 35)));
    $this->assertEqual(str_repeat('A', 22).'...', truncate_text(str_repeat('A', 35), 25));
    $this->assertEqual(str_repeat('A', 21).'BBBB', truncate_text(str_repeat('A', 35), 25, 'BBBB'));
  }

  public function test_text_highlighter()
  {
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful</strong> morning",
      highlight_text("This is a beautiful morning", "beautiful"));

    $this->assertEqual("This is a <strong class=\"highlight\">beautiful</strong> morning, but also a <strong class=\"highlight\">beautiful</strong> day",
      highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful"));

    $this->assertEqual("This is a <b>beautiful</b> morning, but also a <b>beautiful</b> day",
      highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful", '<b>\\1</b>'));

    $this->assertEqual('', highlight_text('', 'beautiful'));
    $this->assertEqual('', highlight_text('', ''));
    $this->assertEqual('foobar', highlight_text('foobar', 'beautiful'));
    $this->assertEqual('foobar', highlight_text('foobar', ''));
  }

  public function test_text_highlighter_with_regexp()
  {
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful!</strong> morning",
      highlight_text("This is a beautiful! morning", "beautiful!"));

    $this->assertEqual("This is a <strong class=\"highlight\">beautiful! morning</strong>",
      highlight_text("This is a beautiful! morning", "beautiful! morning"));

    $this->assertEqual("This is a <strong class=\"highlight\">beautiful? morning</strong>",
      highlight_text("This is a beautiful? morning", "beautiful? morning"));
  }

  public function test_text_excerpt()
  {
    $this->assertEqual("...is a beautiful morn...",
      excerpt_text("This is a beautiful morning", "beautiful", 5));
    $this->assertEqual("This is a...", excerpt_text("This is a beautiful morning", "this", 5));
    $this->assertEqual("...iful morning", excerpt_text("This is a beautiful morning", "morning", 5));
    $this->assertEqual("...iful morning", excerpt_text("This is a beautiful morning", "morning", 5));
    $this->assertEqual('', excerpt_text("This is a beautiful morning", "day"));
  }

  public function test_text_simple_format()
  {
    $this->assertEqual("<p>crazy\n<br /> cross\n<br /> platform linebreaks</p>",
      simple_format_text("crazy\r\n cross\r platform linebreaks"));

    $this->assertEqual("<p>A paragraph</p>\n\n<p>and another one!</p>",
      simple_format_text("A paragraph\n\nand another one!"));

    $this->assertEqual("<p>A paragraph\n<br /> With a newline</p>",
      simple_format_text("A paragraph\n With a newline"));
  }

  public function test_text_strip_links()
  {
    $this->assertEqual("on my mind", strip_links_text("<a href='almost'>on my mind</a>"));
  }

  public function test_auto_linking()
  {
    $emailRaw = 'fabien.potencier@symfony-project.com.com';
    $emailResult = '<a href="mailto:'.$emailRaw.'">'.$emailRaw.'</a>';
    $linkRaw = 'http://www.google.com';
    $linkResult = '<a href="'.$linkRaw.'">'.$linkRaw.'</a>';
    $link2Raw = 'www.google.com';
    $link2Result = '<a href="http://'.$link2Raw.'">'.$link2Raw.'</a>';

    $this->assertEqual('hello '.$emailResult, auto_link_text('hello '.$emailRaw, 'email_addresses'));
    $this->assertEqual('Go to '.$linkResult, auto_link_text('Go to '.$linkRaw, 'urls'));
    $this->assertEqual('Go to '.$linkRaw, auto_link_text('Go to '.$linkRaw, 'email_addresses'));
    $this->assertEqual('Go to '.$linkResult.' and say hello to '.$emailResult,
      auto_link_text('Go to '.$linkRaw.' and say hello to '.$emailRaw));
    $this->assertEqual('<p>Link '.$linkResult.'</p>', auto_link_text('<p>Link '.$linkRaw.'</p>'));
    $this->assertEqual('<p>'.$linkResult.' Link</p>', auto_link_text('<p>'.$linkRaw.' Link</p>'));
    $this->assertEqual('Go to '.$link2Result, auto_link_text('Go to '.$link2Raw, 'urls'));
    $this->assertEqual('Go to '.$link2Raw, auto_link_text('Go to '.$link2Raw, 'email_addresses'));
    $this->assertEqual('<p>Link '.$link2Result.'</p>', auto_link_text('<p>Link '.$link2Raw.'</p>'));
    $this->assertEqual('<p>'.$link2Result.' Link</p>', auto_link_text('<p>'.$link2Raw.' Link</p>'));
  }
}

?>