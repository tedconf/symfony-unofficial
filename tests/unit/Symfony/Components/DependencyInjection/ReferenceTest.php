<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../../../lib/vendor/lime/LimeAutoloader.php';
LimeAutoloader::register();

require_once __DIR__.'/../../../../lib/SymfonyTests/ClassLoader.php';
$loader = new Symfony\Tests\ClassLoader('Symfony', __DIR__.'/../../../../../lib');
$loader->register();

use Symfony\Components\DependencyInjection\Reference;

$t = new LimeTest(1);

// __construct() ->__toString()
$t->diag('__construct() ->__toString()');

$ref = new Reference('foo');
$t->is((string) $ref, 'foo', '__construct() sets the id of the reference, which is used for the __toString() method');
