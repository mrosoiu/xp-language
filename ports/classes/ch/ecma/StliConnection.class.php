<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.telephony.TelephonyProvider');

  // Response codes
  define('STLI_INIT_RESPONSE',      'error_ind SUCCESS STLI Version "%d"');
  define('STLI_BYE_RESPONSE',       'error_ind SUCCESS BYE');
  define('STLI_MON_START_RESPONSE', 'error_ind SUCCESS MonitorStart');
  define('STLI_MON_STOP_RESPONSE',  'error_ind SUCCESS MonitorStop');
  define('STLI_MAKECALL_RESPONSE',  'error_ind SUCCESS MakeCall');
  define('STLI_MON_CALL_INITIATED', 'Initiated %d makeCall %s %s');
  define('STLI_MON_CALL_DEVICEINFO','DeviceInformation %d %d (%s)');
  
  // Supported protocol versions
  define('STLI_VERSION_2',      2);

  /**
   * STLI Client
   *
   * <quote>
   * STLI stands for "Simple Telephony Interface". The TeamCall Server and the client 
   * application can communicate by using this protocol, similar to the communication 
   * between a webserver and a client. We designed STLI to provide basic, but easy to 
   * implement CTI functionalities. STLI is a time saving and cost effective opportunity 
   * to implement CTI functions in every TCP/IP application, including scripting 
   * languages like Perl or Python. A detailed documentation about the STLI interface is
   * part of every ilink TeamCall Server distribution package. 
   * </quote>
   *
   * @purpose  Provide an interface to STLI server
   * @see      http://www.ilink.de/home/de/cti/products/TeamCallServer/
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-180.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-179.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-217.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-218.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-269.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ECMA-285.HTM
   * @see      http://www.ecma.ch/ecma1/STAND/ecma-323.htm
   */
  class StliConnection extends TelephonyProvider {
    var
      $sock       = NULL,
      $version    = 0;

    /**
     * Constructor. 
     * Takes a peer.Socket object as argument, use as follows:
     * <code>
     *   // [...]
     *   $c= &new StliClient(new Socket($stliServer, $stliPort));
     *   // [...]
     * </code>
     *
     * @access  public
     * @param   &peer.Socket sock
     * @param   int version default STLI_VERSION_2
     */
    function __construct(&$sock, $version= STLI_VERSION_2) {
      $this->sock= &$sock;
      $this->version= $version;
    }
    
    /**
     * Writes data to the socket.
     *
     * @access  protected
     * @param   string buf
     */
    function _write($buf) {
      $this->trace('>>>', $buf);
      $this->sock->write($buf."\n");
    }
    
    /**
     * Reads data from the socket
     *
     * @access  protected
     * @return  string
     */
    function _read() {
      $read= chop($this->sock->read());
      $this->trace('<<<', $read);
      return $read;
    }
    
    /**
     * Set the protocol version. This can only be done *prior* to connecting to
     * the server!
     *
     * @access  public
     * @param   int version
     * @throws  lang.IllegalStateException in case already having connected
     */
    function setVersion($version) { 
      if ($this->sock->isConnected()) return throw(new IllegalStateException(
        'Cannot set version after already having connected'
      ));    
      $this->version= $version;
    }

    /**
     * Private helper function
     *
     * @access  private
     */
    function _sockcmd() {
      $args= func_get_args();
      $write= vsprintf($args[0], array_slice($args, 1));
      
      // Write command
      $this->_write($write);
      
      // Read response
      return $this->_read();
    }
    
    /**
     * Private helper function
     *
     * @access  private
     */
    function _expect($expect, $have) {
      if ($expect !== $have) {
        throw(new TelephonyException(sprintf(
          'Protocol error: Expecting "%s", have "%s"', $expect, $have
        )));
        return FALSE;
      }
      
      return $have;
    }

    /**
     * Private helper function
     *
     * @access  private
     */
    function _expectf($expect, $have) {
      $res= sscanf($have, $expect);

      foreach ($res as $val) {
        if (is_null($val)) {
          throw(new TelephonyException(sprintf(
            'Protocol error: Expecting "%s", have "%s"', $expect, $have
          )));
          return FALSE;
        }
      }
      
      return $have;
    }

    /**
     * Connect and initiate the communication
     *
     * @access  public
     * @return  mixed the return code of the socket's connect method
     * @throws  util.telephony.TelephonyException in case a protocol error occurs
     */
    function connect() {
      if (FALSE === ($ret= $this->sock->connect())) return FALSE;
      
      // Send initialization string and check response
      return $this->_expect(
        sprintf(STLI_INIT_RESPONSE, $this->version),
        $this->_sockcmd('STLI;Version=%d', $this->version)
      );
    }

    /**
     * Close connection and end the communication
     *
     * @access  public
     * @return  mixed the return code of the socket's close method
     * @throws  util.telephony.TelephonyException in case a protocol error occurs
     */
    function close() {
      if (FALSE === $this->_expect(
        STLI_BYE_RESPONSE,
        $this->_sockcmd('BYE')
      )) return FALSE;
      
      return $this->sock->close();
    }
    
    /**
     * Create a call
     *
     * @access  public
     * @param   &util.telephony.TelephonyTerminal terminal
     * @param   &util.telephony.TelephonyAddress destination
     * @return  &util.telephony.TelephonyCall a call object
     */
    function &createCall(&$terminal, &$destination) {
      if (FALSE === $this->_expect(
        STLI_MAKECALL_RESPONSE,
        $this->_sockcmd('MakeCall %s %s', 
          $terminal->getAttachedNumber(), 
          $destination->getNumber()
      ))) return NULL;

      if ($terminal->isObserved()) {
        if (
          !$this->_expectf(STLI_MON_CALL_INITIATED, $this->_read()) ||
          !$this->_expectf(STLI_MON_CALL_DEVICEINFO, $this->_read())
        ) return NULL;
      }
      return new TelephonyCall($terminal->address, $destination);
    }
    
    /**
     * Get terminal
     *
     * @access  public
     * @param   &util.telephony.TelephonyAddress address
     * @return  &util.telephony.TelephonyTerminal
     */
    function &getTerminal(&$address) {
      return new TelephonyTerminal($address);
    }

    /**
     * Observe a terminal
     *
     * @access  public
     * @param   &util.telephony.TelephonyTerminal terminal
     * @param   bool status TRUE to start observing, FALSE top stop
     * @return  bool success
     */
    function observeTerminal(&$terminal, $status) {
      if ($status) {
        $success= $this->_expect(
          STLI_MON_START_RESPONSE,
          $this->_sockcmd('MonitorStart %s', $terminal->getAttachedNumber())
        );
        $success && $terminal->setObserved(TRUE);
      } else {
        $success= $this->_expect(
          STLI_MON_STOP_RESPONSE,
          $this->_sockcmd('MonitorStop %s', $terminal->getAttachedNumber())
        );
        $success && $terminal->setObserved(FALSE);
      }
      return $success;
    }

    /**
     * Release terminal
     *
     * @access  public
     * @param   &util.telephony.TelephonyTerminal terminal
     * @return  bool success
     */
    function releaseTerminal(&$terminal) {
      return TRUE;
    }
  }
?>
