<?php
/*
 *  $Id: SQLitePreparedStatement.php,v 1.7 2004/03/20 04:16:50 hlellelid Exp $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */
 
require_once 'creole/PreparedStatement.php';
require_once 'creole/common/PreparedStatementCommon.php';

/**
 * MySQL subclass for prepared statements.
 * 
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.7 $
 * @package   creole.drivers.pdosqlite
 */
 
//TODO: use PDO prepared statements
class PdoPreparedStatementCommon extends PreparedStatementCommon implements PreparedStatement {
    
    private $pdo_stmt = null;
    
    
    private $bind_values = array();
    /**
     * Create new prepared statement instance.
     * 
     * @param object $conn Connection object
     * @param string $sql The SQL to work with.
     * @param array $positions The positions in SQL of ?'s.
     * @param restult $stmt If the driver supports prepared queries, then $stmt will contain the statement to use.
     */ 
    public function __construct(Connection $conn, $sql)
    {
        $this->conn = $conn;
        $this->sql = $sql;
        
        
    }
    
    /**
     * Save the parameters so that we can apply them right before we execute
     * the SQL statement.  We can't apply the parameters directly with bindValue
     * because that would require the SQL statement to be re-prepared if setLimit
     * or setOffset was called after the parameters were set.                    
     * 
     * @param mixed $index String or Int of the parameter index
     * @param mixed $value The value to assign this parameter
     * @param int $type An integer value corresponding to one of the PDO::PARAM_* constants
     */
    public function saveBindValue( $index, $value, $type = null ) {
        $this->bind_values[] = array( $index, $value, $type );
    }
    
    /**
     * Not used with PDO
     * 
     * This is for emulated prepared statements.
     * 
     * * @throws SQLException - if called
     */
    public function replaceParams()
    {
        $sql = $this->sql;
        
        // apply limits and offset if necessary
        if ($this->limit > 0 || $this->offset > 0) {
            //TODO: what do we do for drivers that need to emulate
            //limit and offset?
            //NOTE: the connection accepts the sql value by reference and alters
            // it.  
            $this->conn->applyLimit($sql, $this->offset, $this->limit);
        }
        
        try {
            $this->pdo_stmt = $this->conn->getResource()->prepare( $sql );
        } catch( PDOException $e ) {
            throw new SQLException("Unable to prepare statement", $e->getMessage(), $sql);
        }
        
        foreach( $this->bind_values as &$parameter ) {
            // not sure if we need this check, PDO might accept the NULL value 
            // without any adverse affects.  However, I didn't want to chance it
            // or take the time to test it :)
            if( $parameter[2] != null ) {
                $this->pdo_stmt->bindValue( $parameter[0], $parameter[1], $parameter[2] );
            } else {
                $this->pdo_stmt->bindValue( $parameter[0], $parameter[1] );
            }
        }
    }
    
    /**
     * Executes the SQL query in this PreparedStatement object and returns the resultset generated by the query.
     * We support two signatures for this method:
     * - $stmt->executeQuery(ResultSet::FETCHMODE_NUM);
     * - $stmt->executeQuery(array($param1, $param2), ResultSet::FETCHMODE_NUM);
     * @param mixed $p1 Either (array) Parameters that will be set using PreparedStatement::set() before query is executed or (int) fetchmode.
     * @param int $fetchmode The mode to use when fetching the results (e.g. ResultSet::FETCHMODE_NUM, ResultSet::FETCHMODE_ASSOC).
     * @return ResultSet
     * @throws SQLException if a database access error occurs.
     */
	public function executeQuery($p1 = null, $fetchmode = null, $rs_class = null)
	{    
        // $rs_class can not be null, but inheritance requires our class definition
        // to be compatible with PreparedStatementCommon::executeQuery()
        if( empty( $rs_class ) ) {
            throw new SQLException('PdoPreparedStatementCommon::executeQuery: $rs_class can not be empty');
        }
        
	    $params = null;
		if ($fetchmode !== null) {
			$params = $p1;
		} elseif ($p1 !== null) {
			if (is_array($p1)) $params = $p1;
			else $fetchmode = $p1;
		}
	    
    	foreach ( (array) $params as $i=>$param ) {
    		$this->set ( $i + 1, $param );
    		unset ( $i, $param );
		}
		unset ( $params );
        
        $this->updateCount = null; // reset   
        
        // replace the parameters and apply limit/offset
        $this->replaceParams();
        
        // unset our current result set to cause the destruct method to fire
        // otherwise, on the assignment 5 lines below, the desctruct method is fired  
        // and the destructor ends up closing the cursor on our most recent resultset
        unset( $this->resultSet );
        
        // make sure the connection does not have any open result sets
        if( $this->conn->openResultSet() ) {
            $this->conn->handleOpenResultSet( );
        }
        
        $this->pdo_stmt->execute();
        $this->resultSet = new $rs_class($this->conn, $this->pdo_stmt, $fetchmode);
        
        return $this->resultSet;
    }
    
    /**
     * Executes the SQL INSERT, UPDATE, or DELETE statement in this PreparedStatement object.
     * 
     * @param array $params Parameters that will be set using PreparedStatement::set() before query is executed.
     * @return int Number of affected rows (or 0 for drivers that return nothing).
     * @throws SQLException if a database access error occurs.
     */
    public function executeUpdate($params = null) 
    {
        if($this->resultSet) $this->resultSet->close();
        $this->resultSet = null; // reset                
              
        // replace the parameters
        $this->replaceParams();
        
         // make sure the connection does not have any open result sets
        if( $this->conn->openResultSet() ) {
            $this->conn->handleOpenResultSet( );
        }
        
        try { 
            if( empty( $params ) ) {
                $this->pdo_stmt->execute();
            } else {
                $this->pdo_stmt->execute($params);
            }
            
            $this->updateCount = $this->pdo_stmt->rowCount();
        } catch( PDOException $e ) {
            throw new SQLException("Unable to execute prepared statement", $e->getMessage());
        }
        return $this->updateCount;
    }    
    
    /**
     * Quotes string
     * @see ResultSetCommon::escape()
     */
    protected function escape($str)
    {
        return $this->conn->quote($str);
    }
    
    /**
     * Sets Blob
     * @see PreparedStatement::setBlob()
     * @see ResultSet::getBlob()
     */
    function setBlob($paramIndex, $blob) 
    {    
        if ($blob === null) {
            $this->setNull($paramIndex);
        } else {
            // they took magic __toString() out of PHP5.0.0; this sucks
            if (is_object($blob)) {
                $blob = $blob->__toString();
            }
            $this->saveBindValue( $paramIndex, $blob, PDO::PARAM_LOB );
        }
    }
    
    /**
     * Sets a boolean value.
     * Default behavior is true = 1, false = 0.
     * @param int $paramIndex
     * @param boolean $value
     * @return void
     */
    function setBoolean($paramIndex, $value) 
    {                
	    if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            $this->saveBindValue( $paramIndex, $value, PDO::PARAM_BOOL );
        }
    }
    
    /**
     * Sets Blob
     * @see PreparedStatement::setBlob()
     * @see ResultSet::getBlob()
     */
    function setClob($paramIndex, $clob) 
    {    
        if ($clob === null) {
            $this->setNull($paramIndex);
        } else {
            // they took magic __toString() out of PHP5.0.0; this sucks
            if (is_object($clob)) {
                $clob = $clob->__toString();
            }
            $this->saveBindValue( $paramIndex, $clob, PDO::PARAM_LOB );
        }
    }
    
    /**
     * @param int $paramIndex
     * @param double $value
     * @return void
     */
    function setDecimal($paramIndex, $value) 
    {
	    if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            // PDO doesn't seem to have an excplict PDO::PARAM 
            // type for float
            $this->saveBindValue( $paramIndex, (float) $value );
        }
    }             

    /**
     * @param int $paramIndex
     * @param double $value
     * @return void
     */
    function setDouble($paramIndex, $value) 
    {
	    if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            // PDO doesn't seem to have an excplict PDO::PARAM 
            // type for float
            $this->saveBindValue( $paramIndex, (double) $value );
        }
    }
    
    function setFloat($paramIndex, $value) 
    {
	    if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            // PDO doesn't seem to have an excplict PDO::PARAM 
            // type for float
            $this->saveBindValue( $paramIndex, (float) $value );
        }
    }
    
    /**
     * @param int $paramIndex
     * @param int $value
     * @return void
     */
    function setInt($paramIndex, $value) 
    {
	    if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            $this->saveBindValue( $paramIndex, $value, PDO::PARAM_INT );
        }
    } 
    
    /**
     * @param int $paramIndex
     * @return void
     */
    function setNull($paramIndex) 
    {
    	$this->saveBindValue( $paramIndex, null, PDO::PARAM_NULL );
        
    }
    
    function setString($paramIndex, $value) 
    {
        if ($value === null) {
            $this->setNull($paramIndex);
        } else {
            // it's ok to have a fatal error here, IMO, if object doesn't have
            // __toString() and is being passed to this method.
    	    if ( is_object ( $value ) ) {
                	$value = $value->__toString();
    	    }
    	    $this->saveBindValue( $paramIndex, $value, PDO::PARAM_STR );
        }
    } 
    
}
