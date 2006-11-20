<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Indicates a SOAP fault occured
   *
   * @purpose  Exception
   */
  class SOAPFaultException extends Exception {
    var 
      $fault= NULL; 

    /**
     * Constructor
     *
     * @access  public
     * @param   &webservices.soap.SOAPFault fault
     */
    function __construct(&$fault) {
      parent::__construct($fault->faultstring);
      $this->fault= &$fault;
    }

    /**
     * Get Fault
     *
     * @access  public
     * @return  &webservices.soap.SOAPFault
     */
    function &getFault() {
      return $this->fault;
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
        "  fault.faultcode   = %s\n".
        "  fault.faultactor  = '%s'\n".
        "  fault.detail      = %s\n".
        "}\n",
        $this->getClassName(),
        $this->message,
        $this->fault->faultcode,
        $this->fault->faultactor,
        xp::stringOf($this->fault->detail)
      );
    }
  }
?>
