<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfInflector
{
  /**
   * Returns a camelized string from a lower case and underscored string by replaceing slash with
   * double-colol and upper-casing each letter preceded by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  public static function camelize($lowerCaseAndUnderscoredWord)
  {
    $tmp = $lowerCaseAndUnderscoredWord;
    $tmp = sfToolkit::pregtr($tmp, array('#/(.?)#e'    => "'::'.strtoupper('\\1')",
                                         '/(^|_)(.)/e' => "strtoupper('\\2')"));

    return $tmp;
  }

  /**
   * Returns an underscore-syntaxed version or the CamelCased string.
   *
   * @param string String to underscore.
   *
   * @return string Underscored string.
   */
  public static function underscore($camelCasedWord)
  {
    $tmp = $camelCasedWord;
    $tmp = str_replace('::', '/', $tmp);
    $tmp = sfToolkit::pregtr($tmp, array('/([A-Z]+)([A-Z][a-z])/' => '\\1_\\2',
                                         '/([a-z\d])([A-Z])/'     => '\\1_\\2'));

    return strtolower($tmp);
  }

  /**
   * Returns classname::module with classname:: stripped off.
   *
   * @param string Classname and module pair.
   *
   * @return string Module name.
   */
  public static function demodulize($classNameInModule)
  {
    return preg_replace('/^.*::/', '', $classNameInModule);
  }

  /**
   * Returns classname in underscored form, with "_id" tacked on at the end.
   * This is for use in dealing with foreign keys in the database.
   *
   * @param string Class name.
   * @param boolean Seperate with underscore.
   *
   * @return strong Foreign key
   */
  public static function foreign_key($className, $separateClassNameAndIdWithUnderscore = true)
  {
    return sfInflector::underscore(sfInflector::demodulize($className)).($separateClassNameAndIdWithUnderscore ? "_id" : "id");
  }

  /**
   * Returns corresponding table name for given classname.
   *
   * @param string Name of class to get database table name for.
   *
   * @return string Name of the databse table for given class.
   */
  public static function tableize($className)
  {
    return sfInflector::underscore($className);
  }

  /**
   * Returns model class name for given database table.
   *
   * @param string Table name.
   *
   * @return string Classified table name.
   */
  public static function classify($tableName)
  {
    return sfInflector::camelize($tableName);
  }

  /**
   * Returns a human-readable string from a lower case and underscored word by replacing underscores
   * with a space, and by upper-casing the initial characters.
   *
   * @param string String to make more readable.
   *
   * @return string Human-readable string.
   */
  public static function humanize($lowerCaseAndUnderscoredWord)
  {
    if (substr($lowerCaseAndUnderscoredWord, -3) === '_id')
    {
      $lowerCaseAndUnderscoredWord = substr($lowerCaseAndUnderscoredWord, 0, -3);
    }

    return ucfirst(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
  }
}

?>
