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

require_once __DIR__.'/../../../../../lib/Symfony/Foundation/ClassLoader.php';
$loader = new Symfony\Foundation\ClassLoader('Symfony', __DIR__.'/../../../../../lib');
$loader->register();

use Symfony\Components\DependencyInjection\Parameter;

$t = new LimeTest(1);

// __construct() ->__toString()
$t->diag('__construct() ->__toString()');

$ref = new Parameter('foo');
$t->is((string) $ref, 'foo', '__construct() sets the id of the parameter, which is used for the __toString() method');
