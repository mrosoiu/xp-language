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
      parent::__construct($message);
      $this->detail= $detail;
    }

    /**
     * Return compound message of this exception.
     *
     * @access  public
     * @return  string
     */
    function compoundMessage() {
      return sprintf(
        'Exception %s (%s, %s)',
        $this->getClassName(),
        $this->message,
        $this->detail
      );
    }
  }
?>
