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
 
require_once 'creole/common/PdoPreparedStatementCommon.php';

/**
 *  subclass for prepared statements.
 * 
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.7 $
 * @package   creole.drivers.sqlite
 */
 
//TODO: use PDO prepared statements
class PdoSQLitePreparedStatement extends PdoPreparedStatementCommon {
    
    public function executeQuery($p1 = null, $fetchmode = null)
	{    
        require_once 'creole/drivers/pdosqlite/PdoSQLiteResultSet.php';
        return parent::executeQuery( $p1, $fetchmode, 'PdoSQLiteResultSet');
    }
}
