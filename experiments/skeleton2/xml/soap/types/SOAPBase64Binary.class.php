<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('xml.soap.types.SoapType');

  /**
   * SOAP Base64 binary
   *
   * @see      xp://xml.soap.SOAPNode
   * @purpose  Transport base64 encoded data
   */
  class SOAPBase64Binary extends SoapType {
    public
      $string,
      $encoded;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string string
     * @param   bool encoded default FALSE
     */
    public function __construct($string, $encoded= FALSE) {
      if ($encoded) {
        $this->string= base64_decode($string);
        $this->encoded= $string;
      } else {
        $this->string= $string;
        $this->encoded= base64_encode($string);
      }
    }
    
    /**
     * Return a string representation for use in SOAP
     *
     * @access  public
     * @return  string 
     */
    public function toString() {
      return $this->encoded;
    }
    
    /**
     * Returns this type's name
     *
     * @access  public
     * @return  string
     */
    public function getType() {
      return 'xsd:base64Binary';
    }
  }
?>
