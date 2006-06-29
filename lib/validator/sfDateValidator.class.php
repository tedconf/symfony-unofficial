<?php

/*
* This file is part of the symfony package.
* (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
* (c) 2004-2006 Sean Kerr.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

/**
 * sfDateValidator verifies a parameter is of a date format.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @author     Nick Lane <nick.lane@internode.on.net>
 * @version    SVN: $Id$
 */
class sfDateValidator extends sfValidator
{
  /**
   * Execute this validator.
   *
   * @param mixed A file or parameter value/array.
   * @param error An error message reference.
   *
   * @return bool true, if this validator executes successfully, otherwise false.
   */
  public function execute (&$value, &$error)
  {
    $culture = $this->getContext()->getUser()->getCulture();

    // Validate the given date
    $value1 = $this->_getValidDate($value, $culture);
    if (!$value1)
    {
      $error = $this->getParameter('date_error');
      return false;
    }

    // Is there a compare to do?
    $compareDateParam = $this->getParameter('compare');
    $compareDate = $this->getContext()->getRequest()->getParameter($compareDateParam);

    // If the compare date is given
    if ($compareDate)
    {
      $operator = $this->getParameter('operator', '==');  // default ==
      $operator = trim($operator, '\'" ');
      $value2 = $this->_getValidDate($compareDate, $culture);

      // If the check date is valid, compare it. Otherwise ignore the comparison
      if ($value2)
      {
        $valid = false;
        switch ($operator)
        {
          case '>':
            $valid = $value1 >  $value2;
            break;
          case '>=':
            $valid = $value1 >= $value2;
            break;
          case '==':
            $valid = $value1 == $value2;
            break;
          case '<=':
            $valid = $value1 <= $value2;
            break;
          case '<':
            $valid = $value1 <  $value2;
            break;

          default:
            throw new sfValidatorException("Invalid date comparison operator \"$operator\"");
        }

        if ($valid)
        return true;

        $error = $this->getParameter('compare_error');
        return false;
      }
    }

    return true;
  }

  /**
  * Converts the given date into a Unix timestamp. If the date is invalid,
  * _getValidDate returns null.
  *
  * @param	$value		Date to convert.
  * @param	$culture	Language culture to use.
  */
  private function _getValidDate($value, $culture)
  {
    // Use the language culture date format
    $result = sfI18N::getDateForCulture($value, $culture);
    list($d, $m, $y) = $result;

    // Make sure the date is a valid gregorian calendar date also
    if ($result === null || !checkdate($m, $d, $y))
    return null;

    return strtotime("$y-$m-$d 00:00");
  }

  /**
  * Initializes the validator.
  */
  public function initialize($context, $parameters = null)
  {
    // Initialize parent
    parent::initialize($context, $parameters);

    // Set defaults
    $this->getParameterHolder()->set('date_error', 'Invalid date');
    $this->getParameterHolder()->set('compare_error', 'Compare failed');

    $this->getParameterHolder()->add($parameters);

    return true;
  }
}
