<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.xml.workflow.checkers.ParamChecker');

  /**
   * Checks given values for a valid selection
   *
   * Error codes returned are:
   * <ul>
   *   <li>invalidoption - if the given value is invalid</li>
   * </ul>
   *
   * @purpose  Checker
   */
  class OptionChecker extends ParamChecker {
    var
      $validOptions = array();
    
    /**
     * Construct
     *
     * @access  public
     * @param   array validOptions
     */
    function __construct($validOptions) {
      $this->validOptions= $validOptions;
    }
    
    /**
     * Check a given value
     *
     * @access  public
     * @param   array value
     * @return  string error or NULL on success
     */
    function check($value) {
      foreach ($value as $v) {
        if (!in_array($v, $this->validOptions)) return 'invalidoption';
      }    
    }
  }
?>
