<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * CryptoException
   *
   * @see      xp://security.crypto.CryptoKey
   * @purpose  This exception indicates one of a variety of public/private key problems.
   */
  class CryptoException extends XPException {
    public
      $errors = array();
      
    /**
     * Constructor
     *
     * @param   string message
     * @param   string[] errors default array()
     */
    public function __construct($message, $errors= array()) {
      parent::__construct($message);
      $this->errors= $errors;
    }
  
    /**
     * Returns errors
     *
     * @return  string[] errors
     */
    public function getErrors() {
      return $this->errors;
    }
    
    /**
     * Return compound message of this exception.
     *
     * @return  string
     */
    public function compoundMessage() {
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
