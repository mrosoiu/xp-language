<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.Date', 'xml.soap.types.SoapType');
  
  /**
   * SOAP DateTime
   *
   * @see      xp://xml.soap.types.SoapType
   * @see      http://www.w3.org/TR/xmlschema-2/#ISO8601 
   * @see      http://www.w3.org/TR/xmlschema-2/#dateTime
   * @purpose  DateTime type
   */
  class SOAPDateTime extends SoapType {
    var
      $value= NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   mixed arg
     */
    function __construct($arg) {
      $this->value= &new Date($arg);
      parent::__construct();
    }
    
    /**
     * Return a string representation for use in SOAP
     *
     * @access  public
     * @return  string ISO 8601 conform date (1977-12-14T11:55:0)
     */
    function toString() {
      return $this->value->toString('Y-m-d\TH:i:s');
    }
    
    /**
     * Returns this type's name
     *
     * @access  public
     * @return  string
     */
    function getType() {
      return 'xsd:dateTime';
    }
  }
?>
