<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Represents a terminal
   *
   */
  class TelephonyTerminal extends Object {
    var 
      $address= NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &util.telephony.TelephonyAddress address
     */
    function __construct(&$address) {
      $this->address= &$address;
      parent::__construct();
    }
    
    /**
     * Retrieve the terminal's phone number
     *
     * @access  public
     * @return  string number
     */
    function getAttachedNumber() {
      return $this->address->getExt();
    }
  }
?>
