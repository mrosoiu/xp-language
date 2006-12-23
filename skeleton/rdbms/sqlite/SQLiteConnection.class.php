<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'rdbms.DBConnection',
    'rdbms.sqlite.SQLiteResultSet',
    'rdbms.Transaction',
    'rdbms.StatementFormatter'
  );

  /**
   * Connection to SQLite Databases
   *
   * Note: SQLite is typeless. Sometimes, though, it makes sense to 
   * operate with a "real" integer instead of its string representation.
   * Typelessness is a real pain for dates (which, in other database
   * APIs, is returned as an util.Date object). 
   *
   * Therefore, this class offers a cast function which may be used
   * whithin the SQL as following:
   * <pre>
   *   select 
   *     cast(id, "int") id, 
   *     name, 
   *     cast(percentage, "float") percentage,
   *     cast(lastchange, "date") lastchange, 
   *     changedby
   *   from 
   *     test
   * </pre>
   *
   * The resultset array will contain the following:
   * <pre>
   *   key          type
   *   ------------ -------------
   *   id           int
   *   name         string
   *   percentage   float
   *   lastchange   util.Date
   *   changedby    string
   * </pre>
   *
   * @ext      sqlite
   * @see      http://sqlite.org/
   * @see      http://pecl.php.net/package/SQLite
   * @purpose  Database connection
   */
  class SQLiteConnection extends DBConnection {
  
    /**
     * Callback function to cast data
     *
     * @access  protected
     * @param   mixed s
     * @param   mixed type
     * @return  mixed
     */
    public function _cast($s, $type) {
      static $identifiers= array(
        'date'  => "\2",
        'int'   => "\3",
        'float' => "\4",
      );
      
      return is_null($s) ? NULL : $identifiers[strtolower($type)].$s;
    }

    /**
     * Connect
     *
     * @access  public  
     * @param   bool reconnect default FALSE
     * @return  bool success
     * @throws  rdbms.SQLConnectException
     */
    public function connect($reconnect= FALSE) {
      if (is_resource($this->handle)) return TRUE;  // Already connected
      if (!$reconnect && (FALSE === $this->handle)) return FALSE;    // Previously failed connecting

      if ($this->flags & DB_PERSISTENT) {
        $this->handle= sqlite_open(
          $this->dsn->getUser().'.'.$this->dsn->getHost(), 
          0666,
          $err
        );
      } else {
        $this->handle= sqlite_popen(
          $this->dsn->getUser().'.'.$this->dsn->getHost(), 
          0666,
          $err
        );
      }

      if (!is_resource($this->handle)) {
        throw(new SQLConnectException($err, $this->dsn));
      }
      
      $this->_obs && $this->notifyObservers(new DBEvent(__FUNCTION__, $reconnect));

      sqlite_create_function($this->handle, 'cast', array(&$this, '_cast'), 2);
      return TRUE;
    }
    
    /**
     * Disconnect
     *
     * @access  public
     * @return  bool success
     */
    public function close() { 
      if ($this->handle && $r= sqlite_close($this->handle)) {
        $this->handle= NULL;
        return $r;
      }
      return FALSE;
    }
    
    /**
     * Select database
     *
     * @access  public
     * @param   string db name of database to select
     * @return  bool success
     * @throws  rdbms.SQLStatementFailedException
     */
    public function selectdb($db) {
      throw(new SQLStatementFailedException(
        'Cannot select database, not implemented in SQLite'
      ));
    }

    /**
     * Prepare an SQL statement
     *
     * @access  public
     * @param   mixed* args
     * @return  string
     */
    public function prepare() {
      static $formatter= NULL;
      $args= func_get_args();
      
      if (NULL === $formatter) {
        $formatter= new StatementFormatter();
        $formatter->setEscape('"');
        $formatter->setEscapeRules(array("'" => "''"));
        $formatter->setDateFormat('Y-m-d H:i:s');
      }
      return $formatter->format(array_shift($args), $args);
    }
    
    /**
     * Retrieve identity
     *
     * @access  public
     * @return  mixed identity value
     */
    public function identity() { 
      $i= sqlite_last_insert_rowid($this->handle);
      $this->_obs && $this->notifyObservers(new DBEvent(__FUNCTION__, $i));
      return $i;
    }

    /**
     * Execute an insert statement
     *
     * @access  public
     * @param   mixed* args
     * @return  int number of affected rows
     * @throws  rdbms.SQLStatementFailedException
     */
    public function insert() { 
      $args= func_get_args();
      $args[0]= 'insert '.$args[0];
      if (!($r= call_user_func_array(array(&$this, 'query'), $args))) {
        return FALSE;
      }
      
      return sqlite_changes($this->handle);
    }
    
    
    /**
     * Execute an update statement
     *
     * @access  public
     * @param   mixed* args
     * @return  int number of affected rows
     * @throws  rdbms.SQLStatementFailedException
     */
    public function update() {
      $args= func_get_args();
      $args[0]= 'update '.$args[0];
      if (!($r= call_user_func_array(array(&$this, 'query'), $args))) {
        return FALSE;
      }
      
      return sqlite_changes($this->handle);
    }
    
    /**
     * Execute an update statement
     *
     * @access  public
     * @param   mixed* args
     * @return  int number of affected rows
     * @throws  rdbms.SQLStatementFailedException
     */
    public function delete() { 
      $args= func_get_args();
      $args[0]= 'delete '.$args[0];
      if (!($r= call_user_func_array(array(&$this, 'query'), $args))) {
        return FALSE;
      }
      
      return sqlite_changes($this->handle);
    }
    
    /**
     * Execute a select statement and return all rows as an array
     *
     * @access  public
     * @param   mixed* args
     * @return  array rowsets
     * @throws  rdbms.SQLStatementFailedException
     */
    public function select() { 
      $args= func_get_args();
      $args[0]= 'select '.$args[0];
      if (!($r= call_user_func_array(array(&$this, 'query'), $args))) {
        return FALSE;
      }
      
      $rows= array();
      while ($row= $r->next()) $rows[]= $row;
      return $rows;
    }
    
    /**
     * Execute any statement
     *
     * @access  public
     * @param   mixed* args
     * @return  &rdbms.mysql.MySQLResultSet or FALSE to indicate failure
     * @throws  rdbms.SQLException
     */
    public function &query() { 
      $args= func_get_args();
      $sql= call_user_func_array(array($this, 'prepare'), $args);

      if (!is_resource($this->handle)) {
        if (!($this->flags & DB_AUTOCONNECT)) throw(new SQLStateException('Not connected'));
        try {
          $c= $this->connect();
        } catch (SQLException $e) {
          throw($e);
        }
        
        // Check for subsequent connection errors
        if (FALSE === $c) throw(new SQLStateException('Previously failed to connect.'));
      }
      
      $this->_obs && $this->notifyObservers(new DBEvent(__FUNCTION__, $sql));

      if ($this->flags & DB_UNBUFFERED) {
        $result= sqlite_unbuffered_query($sql, $this->handle, $this->flags & DB_STORE_RESULT);
      } else {
        $result= sqlite_query($sql, $this->handle);
      }
      
      if (FALSE === $result) {
        $e= sqlite_last_error($this->handle);
        throw(new SQLStatementFailedException(
          'Statement failed: '.sqlite_error_string($e), 
          $sql, 
          $e
        ));
      }

      if (TRUE === $result) {
        $this->_obs && $this->notifyObservers(new DBEvent('queryend', TRUE));
        return TRUE;
      }
      
      $resultset= new SQLiteResultSet($result);
      $this->_obs && $this->notifyObservers(new DBEvent('queryend', $resultset));

      return $resultset;
    }

    /**
     * Begin a transaction
     *
     * @access  public
     * @param   &rdbms.Transaction transaction
     * @return  &rdbms.Transaction
     */
    public function &begin($transaction) {
      if (FALSE === $this->query('begin transaction xp_%c', $transaction->name)) {
        return FALSE;
      }
      $transaction->db= $this;
      return $transaction;
    }
    
    /**
     * Retrieve transaction state
     *
     * @access  public
     * @param   string name
     * @return  mixed state
     */
    public function transtate($name) { 
      if (FALSE === ($r= $this->query('@@transtate as transtate'))) {
        return FALSE;
      }
      return $r->next('transtate');
    }
    
    /**
     * Rollback a transaction
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    public function rollback($name) { 
      return $this->query('rollback transaction xp_%c', $name);
    }
    
    /**
     * Commit a transaction
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    public function commit($name) { 
      return $this->query('commit transaction xp_%c', $name);
    }
  }
?>
