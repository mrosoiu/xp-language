<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('rdbms.ResultSet');

  /**
   * Result set
   *
   * @ext      mysql
   * @purpose  Resultset wrapper
   */
  class MySQLResultSet extends ResultSet {
  
    /**
     * Constructor
     *
     * @access  public
     * @param   resource handle
     */
    function __construct($result) {
      $fields= array();
      if (is_resource($result)) {
        for ($i= 0, $num= mysql_num_fields($result); $i < $num; $i++) {
          $field= mysql_fetch_field($result, $i);
          $fields[$field->name]= $field->type;
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
      if (!mysql_data_seek($this->handle, $offset)) {
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
        FALSE === ($row= mysql_fetch_assoc($this->handle))
      ) {
        return FALSE;
      }
      
      foreach (array_keys($row) as $key) {
        if (NULL === $row[$key] || !isset($this->fields[$key])) continue;
        switch ($this->fields[$key]) {
          case 'timestamp':
            if (strlen($row[$key]) == 14) {
              $time= sscanf ((string)$row[$key], '%04s%02s%02s%02s%02s%02s');
              $row[$key]= &new Date(mktime(
                $time[3],
                $time[4],
                $time[5],
                $time[1],
                $time[2],
                $time[0]
              ));
              
              break;
            }
              
          case 'datetime':
          case 'date':          
            $row[$key]= &Date::fromString($row[$key]);
            break;
            
          case 'int':
            if ($row[$key] <= LONG_MAX && $row[$key] >= LONG_MIN) {
              settype($row[$key], 'integer');
            } else {
              settype($row[$key], 'double');
            }
            break;

          case 'bit':
            settype($row[$key], 'integer');
            break;
            
          case 'real':
            settype($row[$key], 'double'); 
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
      return mysql_free_result($this->handle);
    }

  }
?>
