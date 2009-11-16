<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../lib/vendor/lime/lime.php');

$h = new lime_harness(new lime_output(isset($argv) && in_array('--color', $argv)));
$h->base_dir = realpath(__DIR__.'/..');

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../unit'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/Test\.php$/', $file))
  {
    $h->register($file->getRealPath());
  }
}

$c = new lime_coverage($h);
$c->extension = '.php';
$c->verbose = true;
$c->base_dir = realpath(__DIR__.'/../../lib');

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../lib'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/\.php$/', $file))
  {
    $c->register($file->getRealPath());
  }
}

$c->run();
