<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'peer.ConnectException',
    'peer.SocketException'
  );

  /**
   * Socket class
   *
   * @see      php://network
   * @purpose  Basic TCP/IP socket
   */
  class Socket extends Object {
    public
      $host     = '',
      $port     = 0;
      
    protected
      $_sock    = NULL,
      $_prefix  = '';
    
    /**
     * Constructor
     *
     * Note: When specifying a numerical IPv6 address (e.g. fe80::1) 
     * as value for the parameter "host", you must enclose the IP in 
     * square brackets.
     *
     * @access  public
     * @param   string host hostname or IP address
     * @param   int port
     * @param   resource socket default NULL
     */
    public function __construct($host, $port, $socket= NULL) {
      $this->host= $host;
      $this->port= $port;
      $this->_sock= $socket;
    }
    
    /**
     * Get last error. A very inaccurate way of going about error messages since
     * any PHP error/warning is returned - but since there's no function like
     * flasterror() we must rely on this
     *
     * @access  public
     * @return  string error
     */  
    public function getLastError() {
      $e= xp::registry('errors');
      return isset($e[__FILE__]) ? $e[__FILE__][sizeof($e[__FILE__])] : 'unknown error';
    }
    
    /**
     * Returns whether a connection has been established
     *
     * @access  public
     * @return  bool connected
     */
    public function isConnected() {
      return isset($this->_sock) && is_resource($this->_sock);
    }
    
    /**
     * Connect
     *
     * @access  public
     * @param   float timeout default 2.0
     * @see     php://fsockopen
     * @return  bool success
     * @throws  peer.ConnectException
     */
    public function connect($timeout= 2.0) {
      if (self::isConnected()) return 1;
      
      if (!$this->_sock= fsockopen(
        $this->_prefix.$this->host, 
        $this->port, 
        $errno, 
        $errstr, 
        $timeout
      )) {
        throw (new ConnectException(sprintf(
          'Failed connecting to %s:%s within %s seconds [%d: %s]',
          $this->host,
          $this->port,
          $timeout,
          $errno,
          $errstr
        )));
      }
      return 1;
    }

    /**
     * Close socket
     *
     * @access  public
     * @return  bool success
     */
    public function close() {    
      $res= fclose($this->_sock);
      $this->_sock= NULL;
      return $res;
    }

    /**
     * Set socket blocking
     *
     * @access  public
     * @param   bool blocking
     * @return  bool success TRUE to indicate the call succeeded
     * @throws  peer.SocketException
     * @see     php://socket_set_blocking
     */
    public function setBlocking($blockMode) {
      if (FALSE === socket_set_blocking($this->_sock, $blockMode)) {
        throw (new SocketException('Set blocking call failed: '.self::getLastError()));
      }
      
      return TRUE;
    }
    
    /**
     * Returns whether there is data that can be read
     *
     * @access  public
     * @param   float timeout default NULL Timeout value in seconds (e.g. 0.5)
     * @return  bool there is data that can be read
     * @throws  peer.SocketException in case of failure
     */
    public function canRead($timeout= NULL) {
      if (NULL === $timeout) {
        $tv_sec= $tv_usec= NULL;
      } else {
        $tv_sec= intval(floor($timeout));
        $tv_usec= intval(($timeout - floor($timeout)) * 1000000);
      }

      if (FALSE === socket_set_timeout($this->_sock, $tv_sec, $tv_usec)) {
        throw (new SocketException('Select failed: '.self::getLastError()));
      }
      
      if (!is_array($status= socket_get_status($this->_sock))) {
        throw (new SocketException('Get status failed: '.self::getLastError()));
      }

      if (FALSE === socket_set_timeout($this->_sock, 60)) {
        throw (new SocketException('Select failed: '.self::getLastError()));
      }

      return $status['unread_bytes'] > 0;
    }
    
    /**
     * Read data from a socket
     *
     * @access  public
     * @param   int maxLen maximum bytes to read
     * @return  string data
     * @throws  peer.SocketException
     */
    public function read($maxLen= 4096) {
      if (FALSE === ($r= fgets($this->_sock, $maxLen))) {
      
        // fgets returns FALSE on eof, this is particularily dumb when 
        // looping, so check for eof() and make it "no error"
        if (self::eof()) return NULL;
        
        throw (new SocketException('Read of '.$maxLen.' bytes failed: '.self::getLastError()));
      }
      
      return $r;
    }

    /**
     * Read line from a socket
     *
     * @access  public
     * @param   int maxLen maximum bytes to read
     * @return  string data
     * @throws  peer.SocketException
     */
    public function readLine($maxLen= 4096) {
      if (FALSE === ($r= fgets($this->_sock, $maxLen))) {
      
        // fgets returns FALSE on eof, this is particularily dumb when 
        // looping, so check for eof() and make it "no error"
        if (self::eof()) return NULL;
        
        throw (new SocketException('Read of '.$maxLen.' bytes failed: '.self::getLastError()));
      }
      
      return chop($r);
    }

    /**
     * Read data from a socket (binary-safe)
     *
     * @access  public
     * @param   int maxLen maximum bytes to read
     * @return  string data
     * @throws  peer.SocketException
     */
    public function readBinary($maxLen= 4096) {
      if (FALSE === ($r= fread($this->_sock, $maxLen))) {
        throw (new SocketException('Read of '.$maxLen.' bytes failed: '.self::getLastError()));
      }
      
      return $r;
    }
    
    /**
     * Checks if EOF was reached
     *
     * @access  public
     * @return  bool EOF erhalten
     */
    public function eof() {
      return feof($this->_sock);
    }
    
    /**
     * Write a string to the socket
     *
     * @access  public
     * @param   string str
     * @return  int bytes written
     * @throws  peer.SocketException in case of an error
     */
    public function write($str) {
      if (FALSE === ($bytesWritten= fputs($this->_sock, $str, $len= strlen($str)))) {
        throw (new SocketException('Write of '.strlen($len).' bytes to socket failed: '.self::getLastError()));
      }
      
      return $bytesWritten;
    }
    
    /**
     * Destructor
     *
     * @access  public
     */
    public function __destruct() {
      if (self::isConnected()) self::close();
      
    }
  }
?>
