<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for tasks that depends on a sfCommandApplication object.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfCommandApplicationTask extends sfTask
{
  protected
    $commandApplication = null;

  /**
   * Sets command application for task.
   *
   * @param sfCommandApplication $commandApplication The command application
   *
   * @return void
   */
  public function setCommandApplication(sfCommandApplication $commandApplication = null)
  {
    $this->commandApplication = $commandApplication;
  }

  /**
   * Returns the command application for task.
   *
   * @return sfCommandApplication the command application for the task, otherwise null
   */
  public function getCommandApplication()
  {
    return $this->commandApplication;
  }

  /**
   * Check if task is part of a command application.
   *
   * @return bool true if task is part of command application, otherwise false
   */
  public function hasCommandApplication()
  {
    return is_null($this->commandApplication) ? false : true;
  }

  /**
   * @see sfTask
   */
  public function log($messages)
  {
    if (is_null($this->commandApplication) || (!is_null($this->commandApplication) && $this->commandApplication->isVerbose()))
    {
      parent::log($messages);
    }
  }

  /**
   * @see sfTask
   */
  public function logSection($section, $message, $size = null)
  {
    if (is_null($this->commandApplication) || (!is_null($this->commandApplication) && $this->commandApplication->isVerbose()))
    {
      parent::logSection($section, $message, $size);
    }
  }
}
