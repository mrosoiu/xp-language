<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * CertificateException
   *
   * @see      xp://security.cert.Certificate
   * @purpose  This exception indicates one of a variety of certificate problems.
   */
  class CertificateException extends Exception {
    var
      $errors = array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   string[] errors default array()
     */
    function __construct($message, $errors= array()) {
      parent::__construct($message);
      $this->errors= $errors;
    }
  
    /**
     * Returns errors
     *
     * @access  public
     * @return  string[] errors
     */
    function getErrors() {
      return $this->errors;
    }

    /**
     * Return compound message of this exception.
     *
     * @access  public
     * @return  string
     */
    function compoundMessage() {
      return sprintf(
        "Exception %s (%s) {\n".
        "  %s\n".
        "}\n",
        $this->getClassName(),
        $this->message,
        implode("\n  @", $this->errors)
      );
    }
  }
?>
