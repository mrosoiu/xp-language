<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('text.format.IFormat');
  
  /**
   * Hash formatter
   *
   * @purpose  Provide a Format wrapper for Hashs
   * @see      xp://text.format.IFormat
   */
  class HashFormat extends IFormat {

    /**
     * Get an instance
     *
     * @access  public
     * @return  &text.format.HashFormat
     */
    function &getInstance() {
      return parent::getInstance('HashFormat');
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
      if (is_scalar($argument)) {
        return throw(new FormatException('Argument with type '.gettype($argument).' is not an array or object'));
      }
      if (is_a($argument, 'Hashmap')) {
        $hash= $argument->_hash;
      } else if (is_object($argument)) {
        $hash= get_object_vars($argument);
      } else {
        $hash= $argument;
      }
      
      $ret= '';
      $fmt= strtr($fmt, array(
        '\r'    => "\r",
        '\n'    => "\n",
        '\t'    => "\t"
      ));
      foreach (array_keys($argument) as $key) {
        $ret.= sprintf($fmt, $key, $argument[$key]);
      }
      return $ret;
    }
  }
?>
