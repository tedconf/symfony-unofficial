<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../../../../lib/vendor/lime/LimeAutoloader.php';
LimeAutoloader::register();

require_once __DIR__.'/../../../../../../lib/Symfony/Foundation/ClassLoader.php';
$loader = new Symfony\Foundation\ClassLoader('Symfony', __DIR__.'/../../../../../../lib');
$loader->register();

use Symfony\Components\DependencyInjection\Builder;
use Symfony\Components\DependencyInjection\Loader\FileLoader;

$t = new LimeTest(12);

class ProjectLoader extends FileLoader
{
  public $container, $paths;

  public function doLoad($resource)
  {
    return $resource;
  }

  public function getAbsolutePath($file, $currentPath = null)
  {
    return parent::getAbsolutePath($file, $currentPath);
  }
}

// __construct()
$t->diag('__construct()');
$loader = new ProjectLoader($container = new Builder());
$t->is($loader->container, $container, '__construct() takes a container builder instance as its first argument');

$loader = new ProjectLoader(null, __DIR__);
$t->is($loader->paths, array(__DIR__), '__construct() takes a path as its second argument');

$loader = new ProjectLoader(null, array(__DIR__, __DIR__));
$t->is($loader->paths, array(__DIR__, __DIR__), '__construct() takes an array of paths as its second argument');

// ->getAbsolutePath()
$t->diag('->getAbsolutePath()');
$loader = new ProjectLoader(null, array(__DIR__.'/../../../../../bin'));
$t->is($loader->getAbsolutePath('/foo.xml'), '/foo.xml', '->getAbsolutePath() return the path unmodified if it is already an absolute path');
$t->is($loader->getAbsolutePath('c:\\\\foo.xml'), 'c:\\\\foo.xml', '->getAbsolutePath() return the path unmodified if it is already an absolute path');
$t->is($loader->getAbsolutePath('c:/foo.xml'), 'c:/foo.xml', '->getAbsolutePath() return the path unmodified if it is already an absolute path');
$t->is($loader->getAbsolutePath('\\server\\foo.xml'), '\\server\\foo.xml', '->getAbsolutePath() return the path unmodified if it is already an absolute path');

$t->is($loader->getAbsolutePath('FileLoaderTest.php', __DIR__), __DIR__.'/FileLoaderTest.php', '->getAbsolutePath() returns an absolute filename if the file exists in the current path');

$t->is($loader->getAbsolutePath('prove.php', __DIR__), __DIR__.'/../../../../../bin/prove.php', '->getAbsolutePath() returns an absolute filename if the file exists in one of the paths given in the constructor');

$t->is($loader->getAbsolutePath('foo.xml', __DIR__), 'foo.xml', '->getAbsolutePath() returns the path unmodified if it is unable to find it in the given paths');

class ProjectLoader1 extends FileLoader
{
  public $resource;

  public function doLoad($resource)
  {
    $this->resource = $resource;

    return array(array(), array());
  }
}

// ->load()
$t->diag('->load()');
$loader = new ProjectLoader1(new Builder());
$loader->load('foo.txt');
$t->is($loader->resource, array('foo.txt'), '->load() converts the resource to an array of paths');
$loader->load(array('foo.txt'));
$t->is($loader->resource, array('foo.txt'), '->load() keeps arrays as is');
