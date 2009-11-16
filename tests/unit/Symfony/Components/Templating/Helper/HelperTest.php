<?php

/*
 * This file is part of the symfony package.
 *
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

use Symfony\Components\Templating\Helper\Helper;
use Symfony\Components\Templating\Helper\HelperSet;

$t = new LimeTest(1);

class ProjectTemplateHelper extends Helper
{
  public function getName()
  {
    return 'foo';
  }
}

// ->getHelperSet() ->setHelperSet()
$t->diag('->getHelperSet() ->setHelperSet()');
$helper = new ProjectTemplateHelper();
$helper->setHelperSet($helperSet = new HelperSet(array($helper)));
$t->ok($helperSet === $helper->getHelperSet(), '->setHelperSet() sets the helper set related to this helper');
