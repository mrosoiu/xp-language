<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.xml.workflow.checkers.ParamChecker');

  /**
   * Checks given values if they are numeric
   *
   * Error codes returned are:
   * <ul>
   *   <li>notnumeric - if the given value is not numeric</li>
   * </ul>
   *
   * @see      php://is_numeric
   * @purpose  Checker
   */
  class NumericChecker extends ParamChecker {
    
    /**
     * Check a given value
     *
     * @access  public
     * @param   array value
     * @return  string error or NULL on success
     */
    function check($value) {
      foreach ($value as $v) {
        if (!is_numeric($v)) return 'notnumeric';
      }    
    }
  }
?>
