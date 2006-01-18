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
    var
      $host     = '',
      $port     = 0;
      
    var
      $_sock    = NULL,
      $_prefix  = '',
      $_timeout = 60;
    
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
    function __construct($host, $port, $socket= NULL) {
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
    function getLastError() {
      $e= xp::registry('errors');
      return isset($e[__FILE__]) ? key(end($e[__FILE__])) : 'unknown error';
    }
    
    /**
     * Returns whether a connection has been established
     *
     * @access  public
     * @return  bool connected
     */
    function isConnected() {
      return is_resource($this->_sock);
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
    function connect($timeout= 2.0) {
      if ($this->isConnected()) return 1;
      
      if (!$this->_sock= fsockopen(
        $this->_prefix.$this->host, 
        $this->port, 
        $errno, 
        $errstr, 
        $timeout
      )) {
        return throw(new ConnectException(sprintf(
          'Failed connecting to %s:%s within %s seconds [%d: %s]',
          $this->host,
          $this->port,
          $timeout,
          $errno,
          $errstr
        )));
      }
      
      socket_set_timeout($this->_sock, $this->_timeout);
      return 1;
    }

    /**
     * Close socket
     *
     * @access  public
     * @return  bool success
     */
    function close() {    
      $res= fclose($this->_sock);
      $this->_sock= NULL;
      return $res;
    }

    /**
     * Set timeout
     *
     * @access  public
     * @param   mixed _timeout
     */
    function setTimeout($timeout) {
      $this->_timeout= $timeout;
      
      // Apply changes to already opened connection
      if (is_resource($this->_sock)) {
        socket_set_timeout($this->_sock, $this->_timeout);
      }
    }

    /**
     * Get timeout
     *
     * @access  public
     * @return  mixed
     */
    function getTimeout() {
      return $this->_timeout;
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
    function setBlocking($blockMode) {
      if (FALSE === socket_set_blocking($this->_sock, $blockMode)) {
        return throw(new SocketException('Set blocking call failed: '.$this->getLastError()));
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
    function canRead($timeout= NULL) {
      if (NULL === $timeout) {
        $tv_sec= $tv_usec= NULL;
      } else {
        $tv_sec= intval(floor($timeout));
        $tv_usec= intval(($timeout - floor($timeout)) * 1000000);
      }

      if (FALSE === socket_set_timeout($this->_sock, $tv_sec, $tv_usec)) {
        return throw(new SocketException('Select failed: '.$this->getLastError()));
      }
      
      if (!is_array($status= socket_get_status($this->_sock))) {
        return throw(new SocketException('Get status failed: '.$this->getLastError()));
      }

      if (FALSE === socket_set_timeout($this->_sock, $this->_timeout)) {
        return throw(new SocketException('Select failed: '.$this->getLastError()));
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
    function read($maxLen= 4096) {
      if (FALSE === ($r= fgets($this->_sock, $maxLen))) {
      
        // fgets returns FALSE on eof, this is particularily dumb when 
        // looping, so check for eof() and make it "no error"
        if ($this->eof()) return NULL;
        
        return throw(new SocketException('Read of '.$maxLen.' bytes failed: '.$this->getLastError()));
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
    function readLine($maxLen= 4096) {
      if (FALSE === ($r= fgets($this->_sock, $maxLen))) {
      
        // fgets returns FALSE on eof, this is particularily dumb when 
        // looping, so check for eof() and make it "no error"
        if ($this->eof()) return NULL;
        
        return throw(new SocketException('Read of '.$maxLen.' bytes failed: '.$this->getLastError()));
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
    function readBinary($maxLen= 4096) {
      if (FALSE === ($r= fread($this->_sock, $maxLen))) {
        return throw(new SocketException('Read of '.$maxLen.' bytes failed: '.$this->getLastError()));
      }
      
      return $r;
    }
    
    /**
     * Checks if EOF was reached
     *
     * @access  public
     * @return  bool EOF erhalten
     */
    function eof() {
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
    function write($str) {
      if (FALSE === ($bytesWritten= fputs($this->_sock, $str, $len= strlen($str)))) {
        return throw(new SocketException('Write of '.strlen($len).' bytes to socket failed: '.$this->getLastError()));
      }
      
      return $bytesWritten;
    }

    /**
     * Retrieve socket handle
     *
     * @access  public
     * @return  resource
     */
    function getHandle() {
      return $this->_sock;
    }
    
    /**
     * Destructor
     *
     * @access  public
     */
    function __destruct() {
      if ($this->isConnected()) $this->close();
    }
  }
?>
