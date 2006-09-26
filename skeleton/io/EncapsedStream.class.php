<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.Stream');

  /**
   * Encapsulated / embedded stream
   *
   * @test      xp://net.xp_framework.unittest.io.EncapsedStreamTest
   * @see       xp://io.Stream
   * @purpose   Encapsulated stream
   */
  class EncapsedStream extends Stream {
    var
      $_super     = NULL,
      $_offset    = 0,
      $_size      = 0;

    /**
     * Constructor
     *
     * @access  public
     * @param   &io.Stream super parent stream
     * @param   int offset offset where encapsed stream starts in parent stream
     * @param   int size
     * @throws  lang.IllegalStateException when stream is not yet opened
     */
    function __construct(&$super, $offset, $size) {
      if (!$super->isOpen()) return throw(new IllegalStateException(
        'Super-stream must be opened in EncapsedStream'
      ));
      
      $this->_super= &$super;
      $this->_offset= $offset;
      $this->_size= $size;
    }
    
    /**
     * Prepares the stream for the next operation (eg. moves the
     * pointer to the correct position).
     *
     * @access  protected
     */
    function _prepare() {
      $this->_super->seek($this->_offset + $this->offset);
    }
    
    /**
     * Keep track of moved stream pointers in the parent
     * stream.
     *
     * Should be used internally to correctly calculate the offset
     * for subsequent reads.
     *
     * @access  protected
     * @param   mixed arg
     * @return  mixed arg
     */
    function _track($arg) {
      $this->offset+= ($this->_super->tell()- ($this->_offset+ $this->offset));
      return $arg;
    }
    
    /**
     * Open the stream. For EncapsedStream only reading is supported
     *
     * @access  public
     * @param   string mode default STREAM_MODE_READ one of the STREAM_MODE_* constants
     */
    function open($mode= STREAM_MODE_READ) {
      if (STREAM_MODE_READ !== $mode) return throw(new IllegalAccessException(
        'EncapsedStream only supports reading but writing operation requested.'
      ));
    }
    
    /**
     * Returns whether this stream is open
     *
     * @access  public
     * @return  bool TRUE, when the stream is open
     */
    function isOpen() {
      return $this->_super->isOpen();
    }
    
    /**
     * Retrieve the stream's size in bytes
     *
     * @access  public
     * @return  int size streamsize in bytes
     */
    function size() {
      return $this->_size;
    }
    
    /**
     * Truncate the stream to the specified length
     *
     * @access  public
     * @param   int size default 0
     * @return  bool
     */
    function truncate() {
      return throw(new MethodNotImplementedException(
        'Truncation not supported.'
      ));
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
     */
    function readLine($bytes= 4096) {
      $this->_prepare();
      return $this->_track($this->_super->readLine(min($bytes, $this->_size- $this->offset)));
    }
    
    /**
     * Read one char
     *
     * @access  public
     * @return  char the character read
     */
    function readChar() {
      $this->_prepare();
      return $this->_track($this->_super->readChar());
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
     */
    function gets($bytes= 4096) {
      $this->_prepare();
      return $this->_track($this->_super->gets(min($bytes, $this->_size- $this->offset)));
    }
    
    /**
     * Read (binary-safe)
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     */
    function read($bytes= 4096) {
      $this->_prepare();
      return $this->_track($this->_super->read(min($bytes, $this->_size- $this->offset)));
    }
    
    /**
     * Write. No supported in EncapsedStream
     *
     * @access  public
     * @param   string string data to write
     * @return  int number of bytes written
     */
    function write($string) {
      return throw(new MethodNotImplementedException('Writing not supported.'));
    }    

    /**
     * Write a line and append a LF (\n) character. Not supported in EncapsedStream
     *
     * @access  public
     * @param   string string default '' data to write
     * @return  int number of bytes written
     */
    function writeLine($string) {
      return throw(new MethodNotImplementedException('Writing not supported.'));
    }
    
    /**
     * Returns whether the stream pointer is at the end of the stream
     *
     * @access  public
     * @return  bool TRUE when the end of the stream is reached
     */
    function eof() {
      return $this->offset >= $this->_size;
    }
    
    /**
     * Move stream pointer to a new position. If the pointer exceeds the
     * actual buffer size, it is reset to the end of the buffer. This case
     * is not considered an error.
     *
     * @see     php://fseek
     * @access  public
     * @param   int position default 0 The new position
     * @param   int mode default SEEK_SET 
     * @return  bool success
     */
    function seek($position, $mode= SEEK_SET) {
      switch ($mode) {
        case SEEK_SET: $this->offset= min($this->_size, $position); break;
        case SEEK_CUR: $this->offset= min($this->_size, $this->offset+ $position); break;
        case SEEK_END: $this->offset= $this->_size; break;
      }
      
      return TRUE;
    }

    /**
     * Retrieve stream pointer position
     *
     * @access  public
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
      return TRUE;
    }
  }
?>
