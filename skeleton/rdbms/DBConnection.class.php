<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('rdbms.SQLException', 'rdbms.DSN', 'rdbms.ResultSet');
  
  define('DB_STORE_RESULT',     0x0001);
  define('DB_BUFFER_RESULTS',   0x0002);
  define('DB_AUTOCONNECT',      0x0004);
  define('DB_PERSISTENT',       0x0008);
  
  /**
   * Provide an interface from which all other database connection
   * classes extend.
   *
   * @purpose  Base class for database connections
   */
  class DBConnection extends Object {
    var 
      $handle  = NULL,
      $dsn     = NULL,
      $flags   = 0;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   &rdbms.DSN dsn
     */
    function __construct(&$dsn) { 
      $this->dsn= &$dsn;
      $this->flags= $dsn->getFlags();
      parent::__construct();
    }
    
    /**
     * Set a flag
     *
     * @access  public
     * @param   int flag
     */
    function setFlag($flag) { 
      $this->flags |= $flag;
    }
    
    /**
     * Connect
     *
     * @access  public  
     * @return  bool success
     */
    function connect() { 
      if ($db= $this->dsn->getDatabase()) {
        return $this->selectdb($db);
      }
      
      return TRUE;
    }
    
    /**
     * Disconnect
     *
     * @access  public
     * @return  bool success
     */
    function close() { }
    
    /**
     * Select database
     *
     * @access  public
     * @param   string db name of database to select
     * @return  bool success
     */
    function selectdb($db) { }

    /**
     * Prepare an SQL statement
     *
     * @access  public
     * @param   mixed* args
     * @return  string
     */
    function prepare() { }
    
    /**
     * Execute an insert statement
     *
     * @access  public
     * @param   mixed *args
     * @return  bool success
     */
    function insert() { }
    
    /**
     * Retreive identity
     *
     * @access  public
     * @return  mixed identity value
     */
    function identity() { }
    
    /**
     * Execute an update statement
     *
     * @access  public
     * @param   mixed* args
     * @return  bool success
     */
    function update() { }
    
    /**
     * Execute an update statement
     *
     * @access  public
     * @param   mixed* args
     * @return  bool success
     */
    function delete() { }
    
    /**
     * Execute a select statement
     *
     * @access  public
     * @param   mixed* args
     * @return  array rowsets
     */
    function select() { }
    
    /**
     * Execute any statement
     *
     * @access  public
     * @param   mixed* args
     * @return  resource set
     */
    function query() { }
    
    /**
     * Begin a transaction
     *
     * @access  public
     * @param   &rdbms.DBTransaction transaction
     * @return  &rdbms.DBTransaction
     */
    function &begin(&$transaction) { }
    
    /**
     * Retreive transaction state
     *
     * @access  public
     * @param   string name
     * @return  mixed state
     */
    function transtate($name) { }
    
    /**
     * Rollback a transaction
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    function rollback($name) { }
    
    /**
     * Commit a transaction
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    function commit($name) { }
  }
?>
