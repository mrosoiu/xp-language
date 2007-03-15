<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * SOAP Header interface
   *
   * @see      xp://webservices.soap.SOAPHeaderElement
   * @purpose  Interface
   */
  interface XPSOAPHeader {

    /**
     * Retrieve XML representation of this header for use in a SOAP
     * message.
     *
     * @param   array<string, string> ns list of namespaces
     * @return  &xml.Node
     */
    public function getNode($ns);
  }
?>
