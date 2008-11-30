<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
$fixtures = 'fixtures/fixtures.yml';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$b = new sfTestBrowser();
$t = $b->test();

$article = new Article();
$article->title = 'test';
$t->is($article->Translation['en']->title, 'test');

sfContext::getInstance()->getUser()->setCulture('fr');
$article->title = 'fr test';
$t->is($article->Translation['fr']->title, 'fr test');

$t->is($article->getTitle(), $article->title);
$article->setTitle('test');
$t->is($article->getTitle(), 'test');

$b->get('articles/index');