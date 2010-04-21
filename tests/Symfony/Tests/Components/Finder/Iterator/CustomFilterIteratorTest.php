<?php

/*
 * This file is part of the symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Components\Finder\Iterator;

use Symfony\Components\Finder\Iterator\CustomFilterIterator;

require_once __DIR__.'/IteratorTestCase.php';

class CustomFilterIteratorTest extends IteratorTestCase
{
  /**
   * @dataProvider getAcceptData
   */
  public function testAccept($filters, $expected)
  {
    $inner = new Iterator(array('test.php', 'test.py', 'foo.php'));

    $iterator = new CustomFilterIterator($inner, $filters);

    $this->assertIterator($expected, $iterator);
  }

  public function getAcceptData()
  {
    return array(
      array(array(function (\SplFileInfo $fileinfo) { return false; }), array()),
      array(array(function (\SplFileInfo $fileinfo) { return preg_match('/^test/', $fileinfo) > 0; }), array('test.php', 'test.py')),
    );
  }
}
