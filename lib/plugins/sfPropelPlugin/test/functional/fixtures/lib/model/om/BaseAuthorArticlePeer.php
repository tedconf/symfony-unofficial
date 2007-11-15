<?php


abstract class BaseAuthorArticlePeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'author_article';

	
	const CLASS_DEFAULT = 'lib.model.AuthorArticle';

	
	const NUM_COLUMNS = 3;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;


	
	const AUTHOR_ID = 'author_article.AUTHOR_ID';

	
	const ARTICLE_ID = 'author_article.ARTICLE_ID';

	
	const ID = 'author_article.ID';

	
	public static $instances = array();

	
	private static $mapBuilder = null;

	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('AuthorId', 'ArticleId', 'Id', ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('authorId', 'articleId', 'id', ),
		BasePeer::TYPE_COLNAME => array (self::AUTHOR_ID, self::ARTICLE_ID, self::ID, ),
		BasePeer::TYPE_FIELDNAME => array ('author_id', 'article_id', 'id', ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('AuthorId' => 0, 'ArticleId' => 1, 'Id' => 2, ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('authorId' => 0, 'articleId' => 1, 'id' => 2, ),
		BasePeer::TYPE_COLNAME => array (self::AUTHOR_ID => 0, self::ARTICLE_ID => 1, self::ID => 2, ),
		BasePeer::TYPE_FIELDNAME => array ('author_id' => 0, 'article_id' => 1, 'id' => 2, ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	public static function getMapBuilder()
	{
		if (self::$mapBuilder === null) {
			self::$mapBuilder = new AuthorArticleMapBuilder();
		}
		return self::$mapBuilder;
	}
	
	static public function translateFieldName($name, $fromType, $toType)
	{
		$toNames = self::getFieldNames($toType);
		$key = isset(self::$fieldKeys[$fromType][$name]) ? self::$fieldKeys[$fromType][$name] : null;
		if ($key === null) {
			throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(self::$fieldKeys[$fromType], true));
		}
		return $toNames[$key];
	}

	

	static public function getFieldNames($type = BasePeer::TYPE_PHPNAME)
	{
		if (!array_key_exists($type, self::$fieldNames)) {
			throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
		}
		return self::$fieldNames[$type];
	}

	
	public static function alias($alias, $column)
	{
		return str_replace(AuthorArticlePeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(AuthorArticlePeer::AUTHOR_ID);

		$criteria->addSelectColumn(AuthorArticlePeer::ARTICLE_ID);

		$criteria->addSelectColumn(AuthorArticlePeer::ID);

	}

	const COUNT = 'COUNT(author_article.ID)';
	const COUNT_DISTINCT = 'COUNT(DISTINCT author_article.ID)';

	
	public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}
	
	public static function doSelectOne(Criteria $criteria, PropelPDO $con = null)
	{
		$critcopy = clone $criteria;
		$critcopy->setLimit(1);
		$objects = AuthorArticlePeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		return AuthorArticlePeer::populateObjects(AuthorArticlePeer::doSelectStmt($criteria, $con));
	}
	
	public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		if (!$criteria->getSelectColumns()) {
			$criteria = clone $criteria;
			AuthorArticlePeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

				return BasePeer::doSelect($criteria, $con);
	}
	
	public static function addInstanceToPool(AuthorArticle $obj, $key = null)
	{
		if (Propel::isInstancePoolingEnabled()) {
			if ($key === null) {
				$key = (string) $obj->getPrimaryKey();
			} 			self::$instances[$key] = $obj;
		}
	}

	
	public static function removeInstanceFromPool($value)
	{
		if (Propel::isInstancePoolingEnabled() && $value !== null) {
			if (is_object($value) && $value instanceof AuthorArticle) {
				$key = (string) $value->getPrimaryKey();
			} elseif (is_scalar($value)) {
								$key = serialize($value);
			} else {
				$e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or AuthorArticle object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
				throw $e;
			}

			unset(self::$instances[$key]);
		}
	} 
	
	public static function getInstanceFromPool($key)
	{
		if (Propel::isInstancePoolingEnabled()) {
			if (isset(self::$instances[$key])) {
				return self::$instances[$key];
			}
		}
		return null; 	}
	
	
	public static function clearInstancePool()
	{
		self::$instances = array();
	}
	
	
	public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
	{
				if ($row[$startcol + 2] === null) {
			return null;
		}

		return (string) $row[$startcol + 2];
	}

	
	public static function populateObjects(PDOStatement $stmt)
	{
		$results = array();
	
				$cls = AuthorArticlePeer::getOMClass();
		$cls = substr('.'.$cls, strrpos('.'.$cls, '.') + 1);
				while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = AuthorArticlePeer::getInstanceFromPool($key))) {
				$obj->hydrate($row, 0, true); 				$results[] = $obj;
			} else {
		
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				AuthorArticlePeer::addInstanceToPool($obj, $key);
			} 		}
		return $results;
	}

	
	public static function doCountJoinAuthor(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$criteria->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}


	
	public static function doCountJoinArticle(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$criteria->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}


	
	public static function doSelectJoinAuthor(Criteria $c, $con = null)
	{
		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		AuthorArticlePeer::addSelectColumns($c);
		$startcol = (AuthorArticlePeer::NUM_COLUMNS - AuthorArticlePeer::NUM_LAZY_LOAD_COLUMNS);
		AuthorPeer::addSelectColumns($c);

		$c->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = AuthorArticlePeer::getInstanceFromPool($key1))) {
				$obj1->hydrate($row, 0, true); 			} else {

				$omClass = AuthorArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				AuthorArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = AuthorPeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = AuthorPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = AuthorPeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
				AuthorPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addAuthorArticle($obj1);

			} 
			$results[] = $obj1;
		}
		return $results;
	}


	
	public static function doSelectJoinArticle(Criteria $c, $con = null)
	{
		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		AuthorArticlePeer::addSelectColumns($c);
		$startcol = (AuthorArticlePeer::NUM_COLUMNS - AuthorArticlePeer::NUM_LAZY_LOAD_COLUMNS);
		ArticlePeer::addSelectColumns($c);

		$c->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = AuthorArticlePeer::getInstanceFromPool($key1))) {
				$obj1->hydrate($row, 0, true); 			} else {

				$omClass = AuthorArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				AuthorArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = ArticlePeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = ArticlePeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = ArticlePeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
				ArticlePeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addAuthorArticle($obj1);

			} 
			$results[] = $obj1;
		}
		return $results;
	}


	
	public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
		$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$criteria->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);

		$criteria->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}


	
	public static function doSelectJoinAll(Criteria $c, $con = null)
	{
		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		AuthorArticlePeer::addSelectColumns($c);
		$startcol2 = (AuthorArticlePeer::NUM_COLUMNS - AuthorArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		AuthorPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + AuthorPeer::NUM_COLUMNS;

		ArticlePeer::addSelectColumns($c);
		$startcol4 = $startcol3 + ArticlePeer::NUM_COLUMNS;

		$c->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);

		$c->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);

		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = AuthorArticlePeer::getInstanceFromPool($key1))) {
				$obj1->hydrate($row, 0, true); 			} else {
				$omClass = AuthorArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				AuthorArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			
			$key2 = AuthorPeer::getPrimaryKeyHashFromRow($row, $startcol2);
			if ($key2 !== null) {
				$obj2 = AuthorPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = AuthorPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					AuthorPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addAuthorArticle($obj1);
			} 
			
			$key3 = ArticlePeer::getPrimaryKeyHashFromRow($row, $startcol3);
			if ($key3 !== null) {
				$obj3 = ArticlePeer::getInstanceFromPool($key3);
				if (!$obj3) {

					$omClass = ArticlePeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj3 = new $cls();
					$obj3->hydrate($row, $startcol3);
					ArticlePeer::addInstanceToPool($obj3, $key3);
				} 
								$obj3->addAuthorArticle($obj1);
			} 
			$results[] = $obj1;
		}
		return $results;
	}


	
	public static function doCountJoinAllExceptAuthor(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$criteria->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}


	
	public static function doCountJoinAllExceptArticle(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(AuthorArticlePeer::COUNT);
		}

				foreach ($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$criteria->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);

		$stmt = AuthorArticlePeer::doSelectStmt($criteria, $con);
		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			return (int) $row[0];
		} else {
						return 0;
		}
	}


	
	public static function doSelectJoinAllExceptAuthor(Criteria $c, $con = null)
	{
		$c = clone $c;

								if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		AuthorArticlePeer::addSelectColumns($c);
		$startcol2 = (AuthorArticlePeer::NUM_COLUMNS - AuthorArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		ArticlePeer::addSelectColumns($c);
		$startcol3 = $startcol2 + ArticlePeer::NUM_COLUMNS;

		$c->addJoin(AuthorArticlePeer::ARTICLE_ID, ArticlePeer::ID, Criteria::LEFT_JOIN);


		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== (AuthorArticlePeer::getInstanceFromPool($key1))) {
				$obj1->hydrate($row, 0, true); 			} else {
				$omClass = AuthorArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				AuthorArticlePeer::addInstanceToPool($obj1, $key1);
			} 
				
				$key2 = ArticlePeer::getPrimaryKeyHashFromRow($row, $startcol2);
				if ($key2 !== null) {
					$obj2 = ArticlePeer::getInstanceFromPool($key2);
					if (!$obj2) {
	
						$omClass = ArticlePeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					ArticlePeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addAuthorArticle($obj1);

			} 
			$results[] = $obj1;
		}
		return $results;
	}


	
	public static function doSelectJoinAllExceptArticle(Criteria $c, $con = null)
	{
		$c = clone $c;

								if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		AuthorArticlePeer::addSelectColumns($c);
		$startcol2 = (AuthorArticlePeer::NUM_COLUMNS - AuthorArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		AuthorPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + AuthorPeer::NUM_COLUMNS;

		$c->addJoin(AuthorArticlePeer::AUTHOR_ID, AuthorPeer::ID, Criteria::LEFT_JOIN);


		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = AuthorArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== (AuthorArticlePeer::getInstanceFromPool($key1))) {
				$obj1->hydrate($row, 0, true); 			} else {
				$omClass = AuthorArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				AuthorArticlePeer::addInstanceToPool($obj1, $key1);
			} 
				
				$key2 = AuthorPeer::getPrimaryKeyHashFromRow($row, $startcol2);
				if ($key2 !== null) {
					$obj2 = AuthorPeer::getInstanceFromPool($key2);
					if (!$obj2) {
	
						$omClass = AuthorPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					AuthorPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addAuthorArticle($obj1);

			} 
			$results[] = $obj1;
		}
		return $results;
	}

	
	public static function getTableMap()
	{
		return Propel::getDatabaseMap(self::DATABASE_NAME)->getTable(self::TABLE_NAME);
	}

	
	public static function getOMClass()
	{
		return AuthorArticlePeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} else {
			$criteria = $values->buildCriteria(); 		}

		$criteria->remove(AuthorArticlePeer::ID); 

				$criteria->setDbName(self::DATABASE_NAME);

		try {
									$con->beginTransaction();
			$pk = BasePeer::doInsert($criteria, $con);
			$con->commit();
		} catch(PropelException $e) {
			$con->rollback();
			throw $e;
		}

		return $pk;
	}

	
	public static function doUpdate($values, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(AuthorArticlePeer::ID);
			$selectCriteria->add(AuthorArticlePeer::ID, $criteria->remove(AuthorArticlePeer::ID), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		return BasePeer::doUpdate($selectCriteria, $criteria, $con);
	}

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}
		$affectedRows = 0; 		try {
									$con->beginTransaction();
			$affectedRows += BasePeer::doDeleteAll(AuthorArticlePeer::TABLE_NAME, $con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	 public static function doDelete($values, PropelPDO $con = null)
	 {
		if ($con === null) {
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME);
		}

		if ($values instanceof Criteria) {
												AuthorArticlePeer::clearInstancePool();

						$criteria = clone $values;
		} elseif ($values instanceof AuthorArticle) {
						AuthorArticlePeer::removeInstanceFromPool($values);
						$criteria = $values->buildPkeyCriteria();
		} else {
			
						AuthorArticlePeer::removeInstanceFromPool($values);

			$criteria = new Criteria(self::DATABASE_NAME);
			$criteria->add(AuthorArticlePeer::ID, (array) $values, Criteria::IN);
		}

				$criteria->setDbName(self::DATABASE_NAME);

		$affectedRows = 0; 
		try {
									$con->beginTransaction();
			
			$affectedRows += BasePeer::doDelete($criteria, $con);

			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public static function doValidate(AuthorArticle $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(AuthorArticlePeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(AuthorArticlePeer::TABLE_NAME);

			if (! is_array($cols)) {
				$cols = array($cols);
			}

			foreach ($cols as $colName) {
				if ($tableMap->containsColumn($colName)) {
					$get = 'get' . $tableMap->getColumn($colName)->getPhpName();
					$columns[$colName] = $obj->$get();
				}
			}
		} else {

		}

		$res =  BasePeer::doValidate(AuthorArticlePeer::DATABASE_NAME, AuthorArticlePeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = AuthorArticlePeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
            $request->setError($col, $failed->getMessage());
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($pk, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$criteria = new Criteria(AuthorArticlePeer::DATABASE_NAME);

		$criteria->add(AuthorArticlePeer::ID, $pk);


		$v = AuthorArticlePeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}

	
	public static function retrieveByPKs($pks, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria();
			$criteria->add(AuthorArticlePeer::ID, $pks, Criteria::IN);
			$objs = AuthorArticlePeer::doSelect($criteria, $con);
		}
		return $objs;
	}

} 

Propel::getDatabaseMap(BaseAuthorArticlePeer::DATABASE_NAME)->addTableBuilder(BaseAuthorArticlePeer::TABLE_NAME, BaseAuthorArticlePeer::getMapBuilder());

