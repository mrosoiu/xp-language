<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('xml.soap.types.SoapType');

  /**
   * Represents a long value. This class can be used to circumvent
   * the problem that some strong typed languages cannot cast ints
   * into a long (as PHP does automagically).
   *
   */
  class SOAPLong extends SoapType {
    public
      $long;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   int long
     */  
    public function __construct($long) {
      parent::__construct();
      $this->long= number_format($long, 0, NULL, NULL);
    }
    
    /**
     * Return a string representation for use in SOAP
     *
     * @access  public
     * @return  string 
     */    
    public function toString() {
      return (string)$this->long;
    }
    
    /**
     * Returns this type's name
     *
     * @access  public
     * @return  string
     */
    public function getType() {
      return 'xsd:long';
    }
  }
?>
