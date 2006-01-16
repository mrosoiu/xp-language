<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  define('LOGICAL_AND', 'and');
  define('LOGICAL_OR',  'or');

  /**
   * Logical expression
   *
   * @purpose  Criterion
   */
  class LogicalExpression extends Object {
    var
      $criterions = array(),
      $op         = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   &rdbms.criterion.Criterion[] criterions
     * @param   string op one of the LOGICAL_* constants
     */
    function __construct($criterions, $op) {
      $this->criterions= $criterions;
      $this->op= $op;
    }
  
    /**
     * Returns the fragment SQL
     *
     * @access  public
     * @param   &rdbms.DBConnection conn
     * @param   array types
     * @return  string
     * @throws  rdbms.SQLStateException
     */
    function asSql(&$conn, $types) { 
      $sql= '';
      for ($i= 0, $s= sizeof($this->criterions); $i < $s; $i++) {
        $sql.= $this->criterions[$i]->asSql($conn, $types).' '.$this->op.' ';
      }
      return '('.substr($sql, 0, (-1 * strlen($this->op)) - 2).')';
    }

  } implements(__FILE__, 'rdbms.criterion.Criterion');
?>
