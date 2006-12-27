<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('rdbms.DBConnection', 'net.xp_framework.unittest.rdbms.mock.MockResultSet');

  /**
   * Mock database connection
   *
   * @see      xp://rdbms.DBConnection
   * @purpose  Mock object
   */
  class MockConnection extends DBConnection {
    public
      $affectedRows   = 1,
      $identityValue  = 1,
      $resultSet      = NULL,
      $queryError     = array(),
      $connectError   = NULL;

    public
      $_connected     = FALSE;

    /**
     * Constructor
     *
     * @param   &rdbms.DSN dsn
     */
    public function __construct($dsn) { 
      parent::__construct($dsn);
      $this->resultSet= new MockResultSet();
    }

    /**
     * Mock: Set ResultSet
     *
     * @param   &net.xp_framework.unittest.rdbms.mock.MockResultSet resultSet
     */
    public function setResultSet($resultSet) {
      $this->queryError= array();
      $this->resultSet= $resultSet;
    }

    /**
     * Mock: Get ResultSet
     *
     * @return  &net.xp_framework.unittest.rdbms.mock.MockResultSet
     */
    public function getResultSet() {
      return $this->resultSet;
    }

    /**
     * Mock: Make next query fail
     *
     * @param   int errNo
     * @param   int errMsg
     */
    public function makeQueryFail($errNo, $errMsg) {
      $this->queryError= array($errNo, $errMsg);
    }

    /**
     * Mock: Let server disconnect. This will make query() thrown
     *
     */
    public function letServerDisconnect() {
      $this->queryError= array(2013);
    }

    /**
     * Mock: Make connect fail
     *
     * @param   int errMsg
     */
    public function makeConnectFail($errMsg) {
      $this->connectError= $errMsg;
    }

    /**
     * Mock: Set IdentityValue
     *
     * @param   mixed identityValue
     */
    public function setIdentityValue($identityValue) {
      $this->identityValue= $identityValue;
    }

    /**
     * Mock: Get IdentityValue
     *
     * @return  mixed
     */
    public function getIdentityValue() {
      return $this->identityValue;
    }

    /**
     * Mock: Set AffectedRows
     *
     * @param   int affectedRows
     */
    public function setAffectedRows($affectedRows) {
      $this->affectedRows= $affectedRows;
    }

    /**
     * Mock: Get AffectedRows
     *
     * @return  int
     */
    public function getAffectedRows() {
      return $this->affectedRows;
    }

    /**
     * Connect
     *
     * @param   bool reconnect default FALSE
     * @return  bool success
     * @throws  rdbms.SQLConnectException
     */
    public function connect($reconnect= FALSE) {
      if ($this->_connected && !$reconnect) return TRUE;
      
      if ($this->connectError) {
        $this->_connected= FALSE;
        throw(new SQLConnectException($this->connectError, $this->dsn));
      }
      $this->_connected= TRUE;
      return TRUE;
    }

    /**
     * Disconnect
     *
     * @return  bool success
     */
    public function close() {
      $this->_connected= FALSE;
      return TRUE;
    }

    /**
     * Select database
     *
     * @param   string db name of database to select
     * @return  bool success
     */
    public function selectdb($db) { 
      return TRUE;
    }

    /**
     * Protected helper methid
     *
     * @param   array args
     * @return  string
     */
    public function _prepare($args) {
      $sql= $args[0];
      if (sizeof($args) <= 1) return $sql;

      $i= 0;
      
      // This fixes strtok for cases where '%' is the first character
      $sql= $tok= strtok(' '.$sql, '%');
      while (++$i && $tok= strtok('%')) {
      
        // Support %1$s syntax
        if (is_numeric($tok{0})) {
          sscanf($tok, '%d$', $ofs);
          $mod= strlen($ofs) + 1;
        } else {
          $ofs= $i;
          $mod= 0;
        }

        // Type-based conversion
        if (is('Date', $args[$ofs])) {
          $tok{$mod}= 's';
          $a= array($args[$ofs]->toString('Y-m-d H:i:s'));
        } elseif (is('Generic', $args[$ofs])) {
          $a= array($args[$ofs]->toString());
        } elseif (is_array($args[$ofs])) {
          $a= $args[$ofs];
        } else {
          $a= array($args[$ofs]);
        }
        
        foreach ($a as $arg) {
          switch ($tok{0 + $mod}) {
            case 'd': $r= is_null($arg) ? 'NULL' : sprintf('%.0f', $arg); break;
            case 'f': $r= is_null($arg) ? 'NULL' : floatval($arg); break;
            case 'c': $r= is_null($arg) ? 'NULL' : $arg; break;
            case 's': $r= is_null($arg) ? 'NULL' : '"'.strtr($arg, array('"' => '\"', '\\' => '\\\\')).'"'; break;
            case 'u': $r= is_null($arg) ? 'NULL' : '"'.date('Y-m-d H:i:s', $arg).'"'; break;
            default: $r= '%'; $mod= -1; $i--; continue;
          }
          $sql.= $r.', ';
        }
        $sql= rtrim($sql, ', ').substr($tok, 1 + $mod);
      }
      return substr($sql, 1);
    }

    /**
     * Prepare an SQL statement
     *
     * @param   mixed* args
     * @return  string
     */
    public function prepare() { 
      $args= func_get_args();
      return $this->_prepare($args);    
    }
    
    /**
     * Execute an insert statement
     *
     * @param   mixed* args
     * @return  bool success
     */
    public function insert() { 
      $args= func_get_args();
      $args[0]= 'insert '.$args[0];
      if (!($r= call_user_func_array(array($this, 'query'), $args))) {
        return FALSE;
      }
      
      return $this->affectedRows;
    }
    
    /**
     * Retrieve identity
     *
     * @return  mixed identity value
     */
    public function identity() { 
      return $this->identityValue;
    }
    
    /**
     * Execute an update statement
     *
     * @param   mixed* args
     * @return  int number of affected rows
     */
    public function update() {
      $args= func_get_args();
      $args[0]= 'update '.$args[0];
      if (!($r= call_user_func_array(array($this, 'query'), $args))) {
        return FALSE;
      }
      
      return $this->affectedRows;
    }
    
    /**
     * Execute an update statement
     *
     * @param   mixed* args
     * @return  int number of affected rows
     */
    public function delete() {
      $args= func_get_args();
      $args[0]= 'delete '.$args[0];
      if (!($r= call_user_func_array(array($this, 'query'), $args))) {
        return FALSE;
      }
      
      return $this->affectedRows;
    }
    
    /**
     * Execute a select statement
     *
     * @param   mixed* args
     * @return  array rowsets
     */
    public function select() { 
      $args= func_get_args();
      $args[0]= 'select '.$args[0];
      if (!($r= call_user_func_array(array($this, 'query'), $args))) {
        return FALSE;
      }
      
      $rows= array();
      while ($row= $r->next()) $rows[]= $row;
      return $rows;
    }
    
    /**
     * Execute any statement
     *
     * @param   mixed* args
     * @return  &rdbms.ResultSet
     */
    public function query() { 
      $args= func_get_args();
      $sql= $this->_prepare($args);

      if (!$this->_connected) {
        if (!($this->flags & DB_AUTOCONNECT)) throw(new SQLStateException('Not connected'));
        try {
          $c= $this->connect();
        } catch (SQLException $e) {
          throw ($e);
        }
        
        // Check for subsequent connection errors
        if (FALSE === $c) throw(new SQLStateException('Previously failed to connect.'));
      }

      switch (sizeof($this->queryError)) {
        case 0: {
          return $this->resultSet;
        }

        case 1: {   // letServerDisconnect() sets this
          $this->queryError= array();
          $this->_connected= FALSE;
          throw(new SQLConnectionClosedException(
            'Statement failed: Read from server failed',
            $sql
          ));
        }
        
        case 2: {   // makeQueryFail() sets this
          $error= $this->queryError;
          $this->queryError= array();       // Reset so next query succeeds again
          throw(new SQLStatementFailedException(
            'Statement failed: '.$error[1],
            $sql, 
            $error[0]
          ));
        }
      }
      
      return $this->resultSet;
    }
    
    /**
     * Begin a transaction
     *
     * @param   &rdbms.DBTransaction transaction
     * @return  &rdbms.DBTransaction
     */
    public function begin($transaction) {
      $transaction->db= $this;
      return $transaction;
    }
    
    /**
     * Retrieve transaction state
     *
     * @param   string name
     * @return  mixed state
     */
    public function transtate($name) { }
    
    /**
     * Rollback a transaction
     *
     * @param   string name
     * @return  bool success
     */
    public function rollback($name) { }
    
    /**
     * Commit a transaction
     *
     * @param   string name
     * @return  bool success
     */
    public function commit($name) { }
  
  }
?>
