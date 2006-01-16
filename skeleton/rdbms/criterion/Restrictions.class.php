<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'rdbms.criterion.LogicalExpression',
    'rdbms.criterion.BetweenExpression',
    'rdbms.criterion.NegationExpression',
    'rdbms.criterion.SimpleExpression'
  );

  /**
   * Factory for criterion types
   *
   * @test     xp://net.xp_framework.unittest.rdbms.CriteriaTest
   * @purpose  Factory
   */
  class Restrictions extends Object {

    /**
     * Apply an "in" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed[] values
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &in($field, $values) {
      return new SimpleExpression($field, $values, IN);
    }

    /**
     * Apply an "not in" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed[] values
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &notIn($field, $values) {
      return new SimpleExpression($field, $values, NOT_IN);
    }

    /**
     * Apply a "like" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &like($field, $value) {
      return new SimpleExpression($field, $value, LIKE);
    }

    /**
     * Apply a case-insensitive "like" constraint to the named property
     *
     * @see     php://sql_regcase
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &ilike($field, $value) {
      return new SimpleExpression($field, sql_regcase($value), LIKE);
    }
        
    /**
     * Apply an "equal" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &equal($field, $value) {
      return new SimpleExpression($field, $value, EQUAL);
    }

    /**
     * Apply a "not equal" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &notEqual($field, $value) {
      return new SimpleExpression($field, $value, NOT_EQUAL);
    }

    /**
     * Apply a "less than" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &lessThan($field, $value) {
      return new SimpleExpression($field, $value, LESS_THAN);
    }

    /**
     * Apply a "greater than" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &greaterThan($field, $value) {
      return new SimpleExpression($field, $value, GREATER_THAN);
    }

    /**
     * Apply a "less than or equal to" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &lessThanOrEqualTo($field, $value) {
      return new SimpleExpression($field, $value, LESS_EQUAL);
    }

    /**
     * Apply a "greater than or equal to" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed value
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &greaterThanOrEqualTo($field, $value) {
      return new SimpleExpression($field, $value, GREATER_EQUAL);
    }

    /**
     * Apply a "between" constraint to the named property
     *
     * @model   static
     * @access  public
     * @param   string field
     * @param   mixed lo
     * @param   mixed hi
     * @return  &rdbms.criterion.SimpleExpression
     */
    function &between($field, $lo, $hi) {
      return new BetweenExpression($field, $lo, $hi);
    }

    /**
     * Return the disjuction of two expressions
     *
     * @model   static
     * @access  public
     * @param   &rdbms.criterion.Criterion first
     * @param   &rdbms.criterion.Criterion second
     * @param   &rdbms.criterion.Criterion*
     * @return  &rdbms.criterion.LogicalExpression
     */
    function &anyOf(&$first, &$second) {
      $args= array(&$first, &$second);
      for ($i= 2, $n= func_num_args(); $i < $n; $i++) {
        $args[]= func_get_arg($i);
      }
      return new LogicalExpression($args, LOGICAL_OR);
    }
    
    /**
     * Return the conjuction of two expressions
     *
     * @model   static
     * @access  public
     * @param   &rdbms.criterion.Criterion first
     * @param   &rdbms.criterion.Criterion second
     * @param   &rdbms.criterion.Criterion*
     * @return  &rdbms.criterion.LogicalExpression
     */
    function &allOf(&$first, &$second) {
      $args= array(&$first, &$second);
      for ($i= 2, $n= func_num_args(); $i < $n; $i++) {
        $args[]= func_get_arg($i);
      }
      return new LogicalExpression($args, LOGICAL_AND);
    }

    /**
     * Return the negation of an expression
     *
     * @model   static
     * @access  public
     * @param   &rdbms.criterion.Criterion expression
     * @return  &rdbms.criterion.NegationExpression
     */
    function &not(&$expression) {
      return new NegationExpression($expression);
    }
  }
?>
