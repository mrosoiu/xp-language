<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('text.format.IFormat');
  
  /**
   * Printf formatter
   *
   * @purpose  Provide a Format wrapper for numbers
   * @see      php://number_format
   * @see      php://localeconv
   * @see      xp://text.format.IFormat
   */
  class NumberFormat extends IFormat {

    /**
     * Get an instance
     *
     * @access  public
     * @return  &text.format.NumberFormat
     */
    function &getInstance() {
      return parent::getInstance('NumberFormat');
    }  
  
    /**
     * Apply format to argument
     *
     * @access  public
     * @param   mixed fmt
     * @param   &mixed argument
     * @return  string
     * @throws  lang.FormatException
     */
    function apply($fmt, &$argument) {
      if (!is_numeric($argument)) {
        return throw(new FormatException('Argument '.$argument.' of type "'.gettype($argument).'" is not a number'));
      }
      
      list($decimals, $dec_point, $thousands_sep)= explode('#', $fmt);
      return number_format(
        floatval($argument), 
        $decimals, 
        $dec_point,
        $thousands_sep
      );
    }
  }
?>
