<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('rdbms.ResultSet');

  /**
   * Result set
   *
   * @ext      sqlite
   * @purpose  Resultset wrapper
   */
  class SQLiteResultSet extends ResultSet {
  
    /**
     * Constructor
     *
     * @access  public
     * @param   resource handle
     */
    function __construct($result) {
      $fields= array();
      if (is_resource($result)) {
        for ($i= 0, $num= sqlite_num_fields($result); $i < $num; $i++) {
          $fields[sqlite_field_name($result, $i)]= FALSE; // Types are unknown
        }
      }
      parent::__construct($result, $fields);
    }

    /**
     * Seek
     *
     * @access  public
     * @param   int offset
     * @return  bool success
     * @throws  rdbms.SQLException
     */
    function seek($offset) { 
      if (!sqlite_seek($this->handle, $offset)) {
        return throw(new SQLException('Cannot seek to offset '.$offset));
      }
      return TRUE;
    }
    
    /**
     * Iterator function. Returns a rowset if called without parameter,
     * the fields contents if a field is specified or FALSE to indicate
     * no more rows are available.
     *
     * @access  public
     * @param   string field default NULL
     * @return  mixed
     */
    function next($field= NULL) {
      if (
        !is_resource($this->handle) ||
        FALSE === ($row= sqlite_fetch_array($this->handle, SQLITE_ASSOC))
      ) {
        return FALSE;
      }
      
      foreach (array_keys($row) as $key) {
        if (NULL === $row[$key] || !isset($this->fields[$key])) continue;
        
        switch ($row[$key]{0}) {
          case "\2":
            $row[$key]= Date::fromString(substr($row[$key], 1));
            break;

          case "\3":
            $row[$key]= intval(substr($row[$key], 1));
            break;

          case "\4":
            $row[$key]= floatval(substr($row[$key], 1));
            break;
        }
      }
      
      if ($field) return $row[$field]; else return $row;
    }
    
    /**
     * Close resultset and free result memory
     *
     * @access  public
     * @return  bool success
     */
    function close() { 
      return sqlite_free_result($this->handle);
    }

  }
?>
