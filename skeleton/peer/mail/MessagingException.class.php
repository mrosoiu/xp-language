<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * MessagingException
   *
   * @purpose  Indicate a general messaging error has occured
   */
  class MessagingException extends Exception {
    var
      $detail = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   string detail
     */
    function __construct($message, $detail) {
      $this->detail= $detail;
      parent::__construct($message);
    }
  
    /**
     * Return a formatted representation of the "stracktrace"
     *
     * @access  public
     * @return  string
     */
    function getStackTrace() {
      return parent::getStackTrace().'  ['.$this->detail."]\n";
    }
  }
?>
