<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  define('IN',              'in (?)');
  define('NOT_IN',          'not in (?)');
  define('IS',              'is ?');
  define('IS_NOT',          'is not ?');
  define('LIKE',            'like ?');
  define('EQUAL',           '= ?');
  define('NOT_EQUAL',       '!= ?');
  define('LESS_THAN',       '< ?');
  define('GREATER_THAN',    '> ?');
  define('LESS_EQUAL',      '<= ?');
  define('GREATER_EQUAL',   '>= ?');
  define('BIT_AND',         ' & ? = ?');

  /**
   * Simple expression
   *
   * @purpose  Criterion
   */
  class SimpleExpression extends Object {
    var
      $field  = '',
      $value  = NULL,
      $op     = '';

    /**
     * Constructor
     *
     * The operation may be one of:
     * <ul>
     *   <li>IN</li>
     *   <li>NOT_IN</li>
     *   <li>LIKE</li>
     *   <li>EQUAL</li>
     *   <li>NOT_EQUAL</li>
     *   <li>LESS_THAN</li>
     *   <li>GREATER_THAN</li>
     *   <li>LESS_EQUAL</li>
     *   <li>GREATER_EQUAL</li>
     * </ul>
     *
     * @access  public
     * @param   string field
     * @param   mixed value
     * @param   string op default EQUAL
     */
    function __construct($field, $value, $op= EQUAL) {
      static $nullMapping= array(
        EQUAL     => IS,
        NOT_EQUAL => IS_NOT
      );

      $this->field= $field;
      $this->value= &$value;

      // Automatically convert '= NULL' to 'is NULL', former is not valid ANSI-SQL
      if (NULL === $value && isset($nullMapping[$op])) {
        $op= $nullMapping[$op];
      }
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
      if (!isset($types[$this->field])) {
        return throw(new SQLStateException('Field "'.$this->field.'" unknown'));
      }

      return $this->field.' '.$conn->prepare(
        str_replace('?', $types[$this->field], $this->op), 
        $this->value
      );      
    }

  } implements(__FILE__, 'rdbms.criterion.Criterion');
?>
