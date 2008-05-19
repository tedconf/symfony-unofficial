<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfComponents.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfComponents extends sfComponent
{
  /**
 	 * Dispatches to the component defined by the 'component' parameter of the sfRequest object.
 	 *
 	 * This method tries to execute the executeXXX() method of the current object where XXX is the
 	 * defined component name.
 	 *
 	 * @param  sfRequest $request The current sfRequest object
 	 * @return string    A string containing the view name associated with this component
 	 *
 	 * @throws sfInitializationException
 	 *
 	 * @see sfComponent
 	 *
 	 */
  public function execute($request)
  {
    throw new sfInitializationException('sfComponents initialization failed.');
  }
}
