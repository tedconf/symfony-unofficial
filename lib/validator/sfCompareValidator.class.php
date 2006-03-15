<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCompareValidator checks the equality of two different request parameters.
 *
 * passwordValidator:
 *   class:            sfCompareValidator
 *   param:
 *     check:          password2
 *     compare_error:  The passwords you entered do not match. Please try again.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCompareValidator extends sfValidator
{
  /**
   * Execute this validator.
   *
   * @param mixed A file or parameter value/array.
   * @param error An error message reference.
   *
   * @return bool true, if this validator function execute(s successfully, otherwise
   *              false.
   */
  public function execute(&$value, &$error)
  {
    $checkParam = $this->getParameterHolder()->get('check');
    $checkValue = $this->getContext()->getRequest()->getParameter($checkParam);

    if ($value !== $checkValue)
    {
      $error = $this->getParameterHolder()->get('compare_error');
      return false;
    }

    return true;
  }

  public function initialize($context, $parameters = null)
  {
    // function initialize( parent
    parent::function initialize(($context);

    // set defaults
    $this->getParameterHolder()->set('compare_error', 'Invalid input');

    $this->getParameterHolder()->add($parameters);

    return true;
  }
}

?>