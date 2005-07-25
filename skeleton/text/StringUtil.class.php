<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * String utility functions
   *
   * @model    static
   * @purpose  purpose
   */
  class StringUtil extends Object {
  
    /**
     * Delete a specified amount of characters from a string as
     * of a specified position. The resulting string is copied to the 
     * parameter "string" and also returned as result.
     *
     * @model   static
     * @access  public
     * @param   &string string
     * @param   int pos
     * @param   int len default 1
     * @return  string
     */
    function delete(&$string, $pos, $len= 1) {
      $string= substr($string, 0, $pos).substr($string, $pos+ 1);
      return $string;
    }
    
    /**
     * Insert a character into a string at a specified position. The 
     * resulting string is copied to the parameter "string" and also 
     * returned as result.
     *
     * @model   static
     * @access  public
     * @param   &string string
     * @param   int pos
     * @param   char char
     * @return  string
     */
    function insert(&$string, $pos, $char) {
      $string= substr($string, 0, $pos).$char.substr($string, $pos);
      return $string;
    }
    
    /**
     * Split a string into an array of blocks of equal length. Throws an
     * exception in a situation in which a length of less than or equal zero
     * was supplied.
     *
     * @model   static
     * @access  public
     * @param   string string
     * @param   int length
     * @return  array parts
     * @throws  lang.IllegalArgumentException
     */
    function blocksplit($string, $length) {

      // Catch bordercase in which this would result in and endless loop
      if ($length <= 0) {
        return throw(new IllegalArgumentException(sprintf(
          'Paramater length (%s) must be greater than zero',
          var_export($length, 1)
        )));
      }

      $r= array();
      do {
        $r[]= substr($string, 0, $length);
        $string= substr($string, $length);
      } while (strlen($string) > 0);

      return $r;
    }
  }
?>
