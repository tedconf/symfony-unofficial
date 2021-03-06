<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormTextarea represents a textarea HTML tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormTextarea extends sfWidgetForm
{
  /**
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->setAttribute('rows', 4);
    $this->setAttribute('cols', 30);
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return $this->renderContentTag('textarea', self::escapeOnce($value), array_merge(array('name' => $name), $attributes));
  }
}
