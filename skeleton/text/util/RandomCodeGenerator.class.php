<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.StringUtil');
  
  /**
   * Generates random codes that can be used for coupons etc.
   * The codes are not guaranteed to be unique although they usually
   * will:)
   *
   * @purpose  Generator
   */
  class RandomCodeGenerator extends Object {
    var
      $length   = 0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   int length default 16
     */
    function __construct($length= 16) {
      $this->length= $length;
      
    }
    
    /**
     * Generate
     *
     * @access  public
     * @return  string
     */
    function generate() {
      $uniq= str_shuffle(strtr(uniqid(microtime(), TRUE), ' .', 'gh'));
      while (strlen($uniq) > $this->length) {
        StringUtil::delete($uniq, rand(0, strlen($uniq)));
      }
      
      return $uniq;
    }
  }
?>
