<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('text.format.IFormat');
  
  /**
   * Array formatter
   *
   * @purpose  Provide a Format wrapper for arrays
   * @see      xp://text.format.IFormat
   */
  class ArrayFormat extends IFormat {

    /**
     * Get an instance
     *
     * @access  public
     * @return  &text.format.ArrayFormat
     */
    function &getInstance() {
      return parent::getInstance('ArrayFormat');
    }  
  
    /**
     * Apply format to argument
     *
     * @access  public
     * @param   mixed fmt
     * @param   &mixed argument
     * @return  string
     */
    function apply($fmt, &$argument) {
      if (!is_array($argument)) {
        return throw(new FormatException('Argument with type '.gettype($argument).' is not an array'));
      }
      
      return implode($fmt, $argument);
    }
  }
?>
