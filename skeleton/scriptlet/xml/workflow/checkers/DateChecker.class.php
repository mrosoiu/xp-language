<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('text.parser.DateParser');

  /**
   * Checks given date on validity
   *
   * Error codes returned are:
   * <ul>
   *   <li>invalid - if the given value is no valid date</li>
   * </ul>
   *
   * @purpose  Checker
   */
  class DateChecker extends Object {

    /**
     * Check a given value
     *
     * @access  public
     * @param   array value
     * @return  string error or NULL on success
     */
    function check($value) { 
      foreach ($value as $v) {
        try(); {
          DateParser::parse($v);
        } if (catch('FormatException', $e)) {
          return 'invalid';
        }
      }    
    }
  }
?>
