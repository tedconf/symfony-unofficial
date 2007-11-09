<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10, new lime_output_color());

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;

// ->render()
$t->diag('->render()');
$w = new sfWidgetFormSelect(array('choices' => array('foo' => 'bar', 'foobar' => 'foo')));
$dom->loadHTML($w->render('foo', 'foobar'));
$css = new sfDomCssSelector($dom);

$t->is($css->matchSingle('#foo option[value="foobar"][selected="selected"]')->getValue(), 'foo', '->render() renders a select tag with the value selected');
$t->is(count($css->matchAll('#foo option')->getNodes()), 2, '->render() renders all choices as option tags');

// multiple select
$t->diag('multiple select');
$w = new sfWidgetFormSelect(array('multiple' => true, 'choices' => array('foo' => 'bar', 'foobar' => 'foo')));
$dom->loadHTML($w->render('foo', array('foo', 'foobar')));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('select[multiple="multiple"]')->getNodes()), 1, '->render() automatically adds a multiple HTML attributes if multiple is true');
$t->is(count($css->matchAll('select[name="foo[]"]')->getNodes()), 1, '->render() automatically adds a [] at the end of the name if multiple is true');
$t->is($css->matchSingle('#foo option[value="foobar"][selected="selected"]')->getValue(), 'foo', '->render() renders a select tag with the value selected');
$t->is($css->matchSingle('#foo option[value="foo"][selected="selected"]')->getValue(), 'bar', '->render() renders a select tag with the value selected');

$dom->loadHTML($w->render('foo[]', array('foo', 'foobar')));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('select[name="foo[]"]')->getNodes()), 1, '->render() automatically does not add a [] at the end of the name if multiple is true and the name already has one');

// optgroup support
$t->diag('optgroup support');
$w = new sfWidgetFormSelect(array('choices' => array('foo' => array('foo' => 'bar', 'bar' => 'foo'), 'foobar' => 'foo')));

$dom->loadHTML($w->render('foo', array('foo', 'foobar')));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo optgroup[label="foo"] option')->getNodes()), 2, '->render() has support for optgroups tags');

try
{
  $w = new sfWidgetFormSelect();
  $t->fail('__construct() throws an sfException if you don\'t pass a choices option');
}
catch (sfException $e)
{
  $t->pass('__construct() throws an sfException if you don\'t pass a choices option');
}

// choices as a callable
$t->diag('choices as a callable');

function choice_callable()
{
  return array(1, 2, 3);
}
$w = new sfWidgetFormSelect(array('choices' => new sfCallable('choice_callable')));
$dom->loadHTML($w->render('foo'));
$css = new sfDomCssSelector($dom);
$t->is(count($css->matchAll('#foo option')->getNodes()), 3, '->render() accepts a sfCallable as a choices option');
