<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('webservices.soap.types.SoapType');
  
  /**
   * Represents a long value. This class can be used to circumvent
   * the problem that some strong typed languages cannot cast ints
   * into a long (as PHP does automagically).
   *
   */
  class SOAPLong extends SoapType {
    var
      $long;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   int long
     */  
    function __construct($long) {
      $this->long= number_format($long, 0, FALSE, FALSE);
    }
    
    /**
     * Return a string representation for use in SOAP
     *
     * @access  public
     * @return  string 
     */    
    function toString() {
      return (string)$this->long;
    }
    
    /**
     * Returns this type's name
     *
     * @access  public
     * @return  string
     */
    function getType() {
      return 'xsd:long';
    }
  }
?>
