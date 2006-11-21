<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.xml.workflow.checkers.ParamChecker');

  /**
   * Checks given values for string length
   *
   * Error codes returned are:
   * <ul>
   *   <li>tooshort - if the given value's length is smaller than allowed</li>
   *   <li>toolong - if the given value's length is greater than allowed</li>
   * </ul>
   *
   * @purpose  Checker
   */
  class LengthChecker extends ParamChecker {
    var
      $minLength  = 0,
      $maxLength  = 0;
    
    /**
     * Construct
     *
     * @access  public
     * @param   int min
     * @param   int max default -1
     */
    function __construct($min, $max= -1) {
      $this->minLength= $min;
      $this->maxLength= $max;
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
        if (strlen($v) < $this->minLength) {
          return 'tooshort';
        } else if (($this->maxLength > 0) && (strlen($v) > $this->maxLength)) {
          return 'toolong';
        }
      }    
    }
  }
?>
