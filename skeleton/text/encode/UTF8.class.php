<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Encodes/decodes iso-8859-1 to utf-8
   *
   * <code>
   *   $b= UTF8::encode($str);
   *   $str= UTF8::decode($b);
   * </code>
   *
   * @model    static
   * @see      rfc://2045#6.8
   * @purpose  UTF encoder / decoder
   */
  class UTF8 extends Object {
  
    /**
     * Encode string
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  string
     */
    function encode($str) { 
      return utf8_encode($str);
    }
    
    /**
     * Decode utf8 encoded data
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  string
     */
    function decode($str) { 
      return utf8_decode($str);
    }
  }
?>
