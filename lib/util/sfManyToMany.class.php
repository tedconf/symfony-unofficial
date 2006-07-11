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
 * @author     Nick Lane <nick.lane@internode.on.net>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfManyToMany
{
  /**
   * Gets objects related by a many-to-many relationship, with a middle table.
   * 
   * @todo  Does this need to handle multiple primary keys?
   * @todo  Look to remove $extraCond
   * 
   * @param  $object        The object to get related objects for.
   * @param  $relatedClass  The related class to get objects from.
   * @param $middleClass   The middle class used for the many-to-many relationship.
   * @param $assoc         Determines if associated, unassociated, or all objects are retrieved (for all use null).
   * @param $sort          The field in the object table to sort by.
   * @param $relatedColumn The field in the middle table used to identify the related object.
   * @param $thisColumn    The field in the middle table used to identify the given object.
   * @param $primaryKey    The primary key field in the object table.
   * @param $extraCond     Extra SQL conditions, hack.
   */
  public static function getRelatedObjects($object, $relatedClass, $middleClass, $assoc = true, $sort = '', $relatedColumn = '', $thisColumn = '', $primaryKey = '', $extraCond = '')
  {
    $class = get_class($object);
    $relatedPeer = $relatedClass.'Peer';

    // if assoc is null, return all objects
    if ($assoc === null && $relatedClass)
    {
      return call_user_func(array($relatedPeer, 'doSelect'), new Criteria());
    }

    // need to know related and middle classes
    if (!($relatedClass && $middleClass))
    {
      return array();
    }

    self::getRelatedVars($class, $relatedClass, $middleClass, $relatedColumn, $thisColumn, $primaryKey, $sort, $relatedTable, $middleTable, $thisTable);

    $con = Propel::getConnection();
    $query = '
      SELECT %s
      FROM %s
      WHERE %s %s %s IN
      (
        SELECT %s
        FROM %s
        WHERE %s = ?
      )
      %s
    ';

    $query = sprintf($query,
      implode(',', call_user_func(array($relatedPeer, 'getFieldNames'), BasePeer::TYPE_FIELDNAME)),
      $relatedTable,
      ($extraCond ? $extraCond.' AND ' : ''),
      $primaryKey,
      ($assoc ? '' : 'NOT'),
      $relatedColumn,
      $middleTable,
      $thisColumn,
      ($sort ? 'ORDER BY '.$sort : '')
    );

    $stmt = $con->prepareStatement($query);
    $stmt->setInt(1, $object->getPrimaryKey());
    $rs = $stmt->executeQuery(ResultSet::FETCHMODE_NUM);

    return call_user_func(array($relatedPeer, 'populateObjects'), $rs);
  }

  /**
   * Gets an array of unique IDs of objects associated by a many-to-many relationship.
   * 
   * @param  $object         The object to get associated IDs for.
   * @param  $relatedClass  The related class to get the unique IDs from.
   * @param $middleClass   The middle class used for the many-to-many relationship.
   * @param $relatedColumn The field in the middle table used to identify the related object.
   * @param $thisColumn    The field in the middle table used to identify the given object.
   */
  public static function getAssociatedIds($object, $relatedClass, $middleClass, $relatedColumn = '', $thisColumn = '')
  {
    $class = get_class($object);
    self::getRelatedVars($class, $relatedClass, $middleClass, $relatedColumn, $thisColumn, $primaryKey, $sort, $relatedTable, $middleTable, $thisTable);

    $con = Propel::getConnection();
    $query = '
      SELECT %s
      FROM %s
      WHERE %s = ?
      ORDER BY %s
    ';

    $query = sprintf($query,
      $relatedColumn,
      $middleTable,
      $thisColumn,
      $relatedColumn
    );

    $stmt = $con->prepareStatement($query);
    $stmt->setInt(1, $object->getPrimaryKey());
    $rs = $stmt->executeQuery();

    // get the column-name-only portion of relatedColumn
    $columnIndex = strrpos($relatedColumn, '.');
    if ($columnIndex !== false)
    {
      $columnIndex = substr($relatedColumn, $columnIndex + 1);
    }
    else
    {
      $columnIndex = $relatedColumn;
    }

    // build an array of IDs using the related column
    $ids = array();
    while ($rs->next())
    {
      $ids[] = $rs->getInt($columnIndex);
    }
    return $ids;
  }

  /**
   * Works out variables need to retrieve related records, based on the given
   * class, related class and middle class.
   */
  private static function getRelatedVars($class, $relatedClass, $middleClass,
    &$relatedColumn, &$thisColumn, &$primaryKey, &$sort,
    &$relatedTable, &$middleTable, &$thisTable)
  {
    $relatedTable = constant($relatedClass.'Peer::TABLE_NAME');
    $middleTable  = constant($middleClass.'Peer::TABLE_NAME');
    $thisTable    = constant($class.'Peer::TABLE_NAME');

    if (!$relatedColumn) $relatedColumn = $relatedTable.'_ID';
    if (!$thisColumn)    $thisColumn    = $thisTable.'_ID';
    if (!$primaryKey)    $primaryKey    = 'ID';

    $relatedColumn   = constant($middleClass.'Peer::'.strtoupper($relatedColumn));
    $thisColumn      = constant($middleClass.'Peer::'.strtoupper($thisColumn));
    $primaryKey      = constant($relatedClass.'Peer::'.strtoupper($primaryKey));
    if ($sort) $sort = constant($relatedClass.'Peer::'.strtoupper($sort));
  }
}
