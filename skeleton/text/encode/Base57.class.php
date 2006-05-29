<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  define('BASE57_CHARTABLE',  'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789');

  /**
   * Encodes/decodes data with base57
   *
   * <code>
   *   $b= Base57::encode($number);
   *   $number= Base57::decode($b);
   * </code>
   *
   * @ext      bcmath
   * @purpose  Base 57 encoder/decoder
   */
  class Base57 extends Object {
  
    /**
     * Encode number
     *
     * @model   static
     * @access  public
     * @param   int number
     * @return  string
     */
    function encode($number) {
      static $chars= BASE57_CHARTABLE;

      $length= ceil(log($number, exp(1)) / log(57, exp(1)));
      for ($out= '', $i= 0; $i < $length; $i++) {
        $out= $chars{bcmod($number, 57)}.$out;
        $number= bcdiv($number, 57, 0);
      }
      
      return $out;      
    }
    
    /**
     * Decode base57 encoded data
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  int
     */
    function decode($str) { 
      static $chars= BASE57_CHARTABLE;
      
      $number= 0;
      for ($i= 0, $s= strlen($str); $i < $s; $i++) {
        $number= bcadd(bcmul($number, 57), strpos($chars, $str{$i}));
      }
      return $number;
    }
  }
?>
