<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConsoleResponse provides methods for manipulating client response in cli environment.
 *
 * @package    symfony
 * @subpackage response
 * @author     Tristan Rivoallan <trivoallan@clever-age.com>
 * @version    SVN: $Id$
 */
class sfConsoleResponse extends sfResponse
{

  /**
   * Serializes the current instance.
   *
   * @return array Objects instance
   */
  public function serialize()
  {
    return serialize(array($this->content, $this->parameterHolder));
  }

  /**
   * Unserializes a sfConsoleResponse instance.
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this->initialize(sfContext::hasInstance() ? sfContext::getInstance()->getEventDispatcher() : new sfEventDispatcher());

    $this->content         = $data[0];
    $this->parameterHolder = $data[1];
  }
}
