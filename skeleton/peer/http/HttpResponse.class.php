<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  /**
   * HTTP response
   *
   * @see      xp://peer.http.HttpConnection
   * @purpose  Represents a HTTP response
   */
  class HttpResponse extends Object {
    var
      $statuscode   = 0,
      $message      = '',
      $version      = '',
      $headers      = array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &lang.Object stream
     */
    function __construct(&$stream) {
      $this->stream= &$stream;
      parent::__construct();
    }
    
    /**
     * Read head if necessary
     *
     * @access  private
     * @return  bool success
     */
    function _readhead() {
      if (0 != $this->statuscode) return TRUE;
      
      // Read status line
      $s= chop($this->stream->read());
      if (4 != sscanf(
        $s, 
        'HTTP/%d.%d %3d %s', 
        $major, 
        $minor, 
        $this->statuscode,
        $this->message
      )) return throw(new FormatException('"'.$s.'" is not a valid HTTP response'));
      
      $this->version= $major.'.'.$minor;
      
      // Read rest of headers
      while (!$this->stream->eof()) {
        $l= chop($this->stream->read());
        if ('' == $l) break;
        
        list($k, $v)= explode(': ', $l, 2);
        $this->headers[$k]= $v;
      }
      
      return TRUE;
    }
    
    /**
     * Read data
     *
     * @access  public
     * @param   int size default 8192
     * @param   bool binary default FALSE
     * @return  string buf or FALSE to indicate EOF
     */
    function readData($size= 8192, $binary= FALSE) {
      static $chunked;
      
      if (!$this->_readhead()) return FALSE;        // Read head if not done before
      if ($this->stream->eof()) return FALSE;       // EOF, return FALSE to indicate end
      if (!isset($chunked)) {                       // Check for "chunked"
        $chunked= stristr($this->getHeader('Transfer-Encoding'), 'chunked');
      }
      
      $func= $binary ? 'readBinary' : 'read';
      if (FALSE === ($buf= $this->stream->$func($size))) return FALSE;
      
      // Handle chunked
      if (
        $chunked &&
        !$binary && 
        preg_match('/^([0-9a-fA-F]+)(( ;.*)| )?\r\n$/', $buf, $regs)
      ) {
        return $this->readData($size, $binary);
      }
      
      return $buf;
    }

    /**
     * Get HTTP statuscode
     *
     * @access  public
     * @return  int status code
     */
    function getStatusCode() {
      return $this->_readhead() ? $this->statuscode : FALSE;
    }
    
    /**
     * Get response headers as an associative array
     *
     * @access  public
     * @return  array headers
     */
    function getHeaders() {
      return $this->_readhead() ? $this->headers : FALSE;
    }

    /**
     * Get response header by name
     * Note: The lookup is performed case-insensitive
     *
     * @access  public
     * @return  string value or NULL if this header does not exist
     */
    function getHeader($name) {
      static $h;
      
      if (!$this->_readhead()) return FALSE;
      if (!isset($h)) $h= array_change_key_case($this->headers, CASE_LOWER);
      $name= strtolower($name);
      return isset($h[$name]) ? $h[$name] : NULL;
    }
  
  }
?>
