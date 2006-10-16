<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * ParserMessage
   *
   * @purpose  Value object
   */
  class ParserMessage extends Object {
    var
      $code    = 0,
      $message = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   int code
     * @param   string message
     */
    function __construct($code, $message) {
      $this->code= $code;
      $this->message= $message;
    }
  
    /**
     * Creates a string representation of this object
     * 
     * @access  public
     * @return  string
     */
    function toString() {
      return $this->getClassName().'('.$this->code.') {"'.$this->message.'"}';
    }
  }
?>
