<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  // Mode constants for open() method
  define('STREAM_MODE_READ',      'r');          // Read
  define('STREAM_MODE_READWRITE', 'r+');         // Read/Write
  define('STREAM_MODE_WRITE',     'w');          // Write
  define('STREAM_MODE_REWRITE',   'w+');         // Read/Write, truncate on open
  define('STREAM_MODE_APPEND',    'a');          // Append (Read-only)
  define('STREAM_MODE_READAPPEND','a+');         // Append (Read/Write)
  
  define('STREAM_READ',  0x0001);
  define('STREAM_WRITE', 0x0002);

  /**
   * Stream
   * 
   * @purpose  Represent a generic stream
   */
  class Stream extends Object {
    var
      $buffer   = '',
      $flags    = 0,
      $offset   = 0;
      
    /**
     * Open the stream
     *
     * @access  public
     * @param   string mode default STREAM_MODE_READ one of the STREAM_MODE_* constants
     */
    function open($mode= STREAM_MODE_READ) {
      switch ($mode) {
        case STREAM_MODE_READWRITE:
          $this->flags= STREAM_WRITE;
          // break missing intentionally
          
        case STREAM_MODE_READ:
          $this->flags|= STREAM_READ;
          break;

        case STREAM_MODE_REWRITE:
          $this->buffer= '';
          // break missing intentionally
          
        case STREAM_MODE_WRITE:
          $this->flags= STREAM_WRITE;
          break;

        case STREAM_MODE_READAPPEND:
          $this->flags= STREAM_READ;
          // break missing intentionally
          
        case STREAM_MODE_APPEND:
          $this->flags|= STREAM_WRITE;
          $this->offset= strlen($this->buffer);
          break;
          
      }
    }
    
    /**
     * Returns whether this stream is open
     *
     * @access  public
     * @return  bool TRUE, when the stream is open
     */
    function isOpen() {
      return $this->flags != 0;
    }
    
    /**
     * Retrieve the stream's size in bytes
     *
     * @access  public
     * @return  int size streamsize in bytes
     * @throws  IOException in case of an error
     */
    function size() {
      return strlen($this->buffer);
    }
    
    /**
     * Truncate the stream to the specified length
     *
     * @access  public
     * @param   int size default 0 New size in bytes
     * @throws  IOException in case of an error
     */
    function truncate($size= 0) {
      $this->buffer= substr($this->buffer, 0, $size);
    }

    /**
     * Read one line and chop off trailing CR and LF characters
     *
     * Returns a string of up to length - 1 bytes read from the stream. 
     * Reading ends when length - 1 bytes have been read, on a newline (which is 
     * included in the return value), or on EOF (whichever comes first). 
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     * @throws  IOException in case of an error
     */
    function readLine($bytes= 4096) {
      return chop($this->gets($bytes));
    }
    
    /**
     * Read one char
     *
     * @access  public
     * @return  char the character read
     * @throws  IOException in case of an error
     */
    function readChar() {
      return substr($this->buffer, $this->offset++, 1);
    }

    /**
     * Read a line
     *
     * This function is identical to readLine except that trailing CR and LF characters
     * will be included in its return value
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     * @throws  IOException in case of an error
     */
    function gets($bytes= 4096) {
      $str= substr($this->buffer, $this->offset, $bytes);
      if (FALSE === ($p= strpos($this->buffer, "\n"))) $p= $bytes;
      $bytes= min($p, $bytes);
      $this->offset+= $bytes;
      return substr($str, 0, $bytes);
    }

    /**
     * Read (binary-safe)
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     * @throws  IOException in case of an error
     */
    function read($bytes= 4096) {
      $data= substr($this->buffer, $this->offset, $bytes);
      $this->offset+= $bytes;
      return $data;
    }

    /**
     * Write
     *
     * @access  public
     * @param   string string data to write
     * @return  bool success
     * @throws  IOException in case of an error
     */
    function write($string) {
      $this->buffer= (
        substr($this->buffer, 0, $this->offset).
        $string.
        substr($this->buffer, $this->offset+ strlen($string))
      );
      $this->offset+= strlen ($string);
    }

    /**
     * Write a line and append a LF (\n) character
     *
     * @access  public
     * @param   string string data to write
     * @return  bool success
     * @throws  IOException in case of an error
     */
    function writeLine($string= '') {
      $this->write($string."\n");
    }
    
    /**
     * Returns whether the stream pointer is at the end of the stream
     *
     * Hint:
     * Use isOpen() to check if the stream is open
     *
     * @access  public
     * @return  bool TRUE when the end of the stream is reached
     * @throws  IOException in case of an error (e.g., the stream's not been opened)
     */
    function eof() {
      return $this->offset >= strlen($this->buffer);
    }
    
    /**
     * Sets the stream position indicator for fp to the beginning of the 
     * stream stream. 
     * 
     * This function is identical to a call of $f->seek(0, SEEK_SET)
     *
     * @access  public
     * @throws  IOException in case of an error
     */
    function rewind() {
      $this->offset= 0;
    }
    
    /**
     * Move stream pointer to a new position
     *
     * @access  public
     * @param   int position default 0 The new position
     * @param   int mode default SEEK_SET 
     * @see     php://fseek
     * @throws  IOException in case of an error
     * @return  bool success
     */
    function seek($position= 0, $mode= SEEK_SET) {
      switch ($mode) {
        case SEEK_SET: $this->offset= $position; break;
        case SEEK_CUR: $this->offset+= $position; break;
        case SEEK_END: $this->offset= strlen($this->buffer)+ $position; break;
      }
    }
    
    /**
     * Retrieve stream pointer position
     *
     * @access  public
     * @throws  IOException in case of an error
     * @return  int position
     */
    function tell() {
      return $this->offset;
    }


    /**
     * Close this stream
     *
     * @access  public
     * @return  bool success
     */
    function close() {
      $this->flags= 0;
    }
  }
?>
