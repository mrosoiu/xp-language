<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.xml.workflow.checkers.ParamChecker');

  /**
   * Checks whether given values are within an integer range
   *
   * Error codes returned are:
   * <ul>
   *   <li>toosmall - if the given value exceeds the lower boundary</li>
   *   <li>toolarge - if the given value exceeds the upper boundary</li>
   * </ul>
   *
   * @purpose  Checker
   */
  class IntegerRangeChecker extends ParamChecker {
    var
      $minValue  = 0,
      $maxValue  = 0;
    
    /**
     * Construct
     *
     * @access  public
     * @param   int min
     * @param   int max
     */
    function __construct($min, $max) {
      $this->minValue= $min;
      $this->maxValue= $max;
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
        if ($v < $this->minValue) {
          return 'toosmall';
        } else if ($v > $this->maxValue) {
          return 'toolarge';
        }
      }    
    }
  }
?>
