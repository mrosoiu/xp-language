<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.IOException', 'io.dba.DBAIterator');

  // Open modes
  define('DBO_READ',    'r');
  define('DBO_WRITE',   'w');
  define('DBO_CREATE',  'c');
  define('DBO_TRUNC',   'n');
  
  // Handlers
  define('DBH_GDBM',     'gdbm');
  define('DBH_NDBM',     'ndbm');
  define('DBH_DBM',      'dbm');
  define('DBH_DB2',      'db2');
  define('DBH_DB3',      'db3');
  define('DBH_DB4',      'db4');
  define('DBH_CDB',      'cdb');
  define('DBH_CDBMAKE',  'cdb_make');
  define('DBH_FLATFILE', 'flatfile');
  define('DBH_INIFILE',  'inifile');

  /**
   * DBA File - abstraction layer of Berkeley DB style databases. Wraps
   * GDBM, NDBM, DBM, DB2, ... into one API.
   *
   * Usage example (dumping the contents of a GDBM database):
   * <code>
   *   uses('io.dba.DBAFile');
   *   
   *   $db= &new DBAFile('test.gdbm', DBH_GDBM);
   *   $db->open(DBO_READ);
   *   for ($i= &$db->iterator(); $i->hasNext(); ) {
   *     $key= $i->next();
   *     printf("%-30s => %s\n", $key, $db->fetch($key));
   *   }
   *   
   *   $db->close();
   * </code>
   *
   * Usage example (storing a value in a CDB database):
   * <code>
   *   uses('io.dba.DBAFile');
   *   
   *   $db= &new DBAFile('test.cdb', DBH_CDB);
   *   $db->open(DBO_CREATE);
   *   $db->store('path', ini_get('include_path'));
   *   $db->save($optimize= TRUE);
   *   $db->close();
   * </code>
   *
   * @ext      dba
   * @see      php://dba
   * @purpose  Access Berkeley DB style databases.
   */
  class DBAFile extends Object {
    var
      $filename = '',
      $handler  = '';

    var
      $_fd      = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string filename
     * @param   string handler one of DBH_* handler constants
     * @see     php://dba#dba.requirements Handler decriptions
     */
    function __construct($filename, $handler) {
      $this->filename= $filename;
      $this->handler= $handler;
    }

    /**
     * Get Filename
     *
     * @access  public
     * @return  string
     */
    function getFilename() {
      return $this->filename;
    }

    /**
     * Get Handler
     *
     * @access  public
     * @return  string
     */
    function getHandler() {
      return $this->handler;
    }
  
    /**
     * Open this DBA file
     *
     * @access  public
     * @param   string mode default DBO_CREATE
     * @return  bool
     * @throws  io.IOException in case opening the file fails
     */
    function open($mode= DBO_CREATE) {
      if (!is_resource($this->_fd= dba_open(
        $this->filename, 
        $mode, 
        $this->handler
      ))) {
        $this->_fd= -1;
        return throw(new IOException(
          'Could not open '.$this->handler.'://'.$this->filename.' mode "'.$mode.'"'
        ));
      }
      return TRUE;
    }
    
    /**
     * Returns an iterator over the keys of this DBA file
     *
     * @access  public
     * @return  &io.dba.DBAIterator
     * @see     xp://io.dba.DBAIterator
     */
    function &iterator() {
      return new DBAIterator($this->_fd);
    }
    
    /**
     * Returns an array of keys
     *
     * Note: Do not use this for databases containing large amounts 
     * of keys, use the iterator() method instead.
     *
     * @access  public
     * @return  string[] keys
     * @throws  io.IOException in case fetching the keys fails
     * @see     xp://io.dba.DBAFile#iterator
     */
    function keys() {
      $keys= array();
      if (NULL === ($k= dba_firstkey($this->_fd))) {
        return throw(new IOException('Could not fetch first key'));
      }
      while (is_string($k)) {
        $keys[]= $k;
        $k= dba_nextkey($this->_fd);
      }
      return $keys;
    }
    
    /**
     * Inserts the entry described with key and value into the 
     * database. Fails if an entry with the same key already 
     * exists. 
     *
     * @access  public
     * @param   string key
     * @param   string value
     * @return  bool TRUE if the key was inserted, FALSE otherwise
     * @throws  io.IOException in case writing failed
     * @see     xp://io.dba.DBAFile#store
     */
    function insert($key, $value) {
      if (!dba_insert($key, $value, $this->_fd)) {
      
        // dba_insert() failed due to the fact key already existed
        if (dba_exists($key, $this->_fd)) return FALSE;
        
        // dba_insert() failed to any other reason
        return throw(new IOException('Could not insert key "'.$key.'"'));
      }
      return TRUE;
    }

    /**
     * Replaces or inserts the entry described with key and value 
     * into the database.
     *
     * @access  public
     * @param   string key
     * @param   string value
     * @return  bool success
     * @throws  io.IOException in case writing failed
     * @see     xp://io.dba.DBAFile#insert
     */
    function store($key, $value) {
      if (!dba_replace($key, $value, $this->_fd)) {
        return throw(new IOException('Could not replace key "'.$key.'"'));
      }
      return TRUE;
    }
    
    /**
     * Removes a specified key from this database
     *
     * @access  public
     * @param   string key
     * @return  bool success
     * @throws  io.IOException in case writing failed
     */
    function delete($key) {
      if (!dba_delete($key, $this->_fd)) {
        return throw(new IOException('Could not delete key "'.$key.'"'));
      }
      return TRUE;
    }
    
    /**
     * Checks for existance of a key
     *
     * @access  public
     * @param   string key
     * @return  bool TRUE if the specified key exists
     */
    function lookup($key) {
      return dba_exists($key, $this->_fd);
    }
    
    /**
     * Fetches the value associated with a specified key from this 
     * database. Returns FALSE in case the key cannot be found.
     *
     * @access  public
     * @param   string key
     * @return  bool success
     * @throws  io.IOException in case reading failed
     */
    function fetch($key) {
      $r= dba_fetch($key, $this->_fd);
      if (NULL === $r) {
        return throw(new IOException('Could not fetch key "'.$key.'"'));
      }
      return $r;
    }
    
    /**
     * Synchronizes the database specified by handle. This will 
     * probably trigger a physical write to disk, if supported.
     *
     * @access  public
     * @param   bool optimize default FALSE whether to optimize
     * @return  bool success
     * @throws  io.IOException in case saving and/or optimizing failed
     */    
    function save($optimize= FALSE) {
      if ($optimize) if (!dba_optimize($this->_fd)) {
        return throw(new IOException('Could not optimize database'));
      }
      if (!dba_sync($this->_fd)) {
        return throw(new IOException('Could not save database'));
      }
      return TRUE;
    }
  
    /**
     * Close this database
     *
     * @access  public
     * @return  bool
     */
    function close() {
      $r= dba_close($this->_fd);
      $this->_fd= NULL;
      return $r;
    }
  }
?>
