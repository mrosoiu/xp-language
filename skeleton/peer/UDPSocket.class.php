<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.Socket');

  /**
   * UDP (Universal Datagram Protocol) socket
   *
   * @purpose  Specialized socket
   */
  class UDPSocket extends Socket {

    /**
     * Constructor
     *
     * @access  public
     * @param   string host hostname or IP address
     * @param   int port
     * @param   resource socket default NULL
     */
    function __construct($host, $port, $socket= NULL) {
      parent::__construct($host, $port, $socket);
      $this->_prefix= 'udp://';
    }
  }
?>
