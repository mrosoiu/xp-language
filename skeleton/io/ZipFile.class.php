<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('io.File');

  /**
   * Represents a zip file
   *
   * @see     xp://io.File
   * @see     php://zlib
   * @purpose Provide the ability to work with zip-compressed files
   * @ext     zlib
   */
  class ZipFile extends File {
  
    /**
     * Open the file
     *
     * @access  public
     * @param   string mode one of the FILE_MODE_* constants
     * @throws  io.FileNotFoundException in case the file is not found
     * @throws  io.IOException in case the file cannot be opened (e.g., lacking permissions)
     */
    function open($mode= FILE_MODE_READ, $compression) {
      $this->mode= $mode;
      if (
        ('php://' != substr($this->uri, 0, 6)) &&
        (FILE_MODE_READ == $mode) && 
        (!$this->exists())
      ) return throw(new FileNotFoundException($this->uri));
      
      $this->_fd= gzopen($this->uri, $this->mode.$compression);
      if (!$this->_fd) return throw(new IOException('cannot open '.$this->uri.' mode '.$this->mode));
      return TRUE;
    }
    
    /**
     * Read one line and chop off trailing CR and LF characters
     *
     * Returns a string of up to length - 1 bytes read from the file. 
     * Reading ends when length - 1 bytes have been read, on a newline (which is 
     * included in the return value), or on EOF (whichever comes first). 
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     * @throws  io.IOException in case of an error
     */
    function readLine($bytes= 4096) {
      return chop($this->gets($bytes));
    }
    
    /**
     * Read one char
     *
     * @access  public
     * @return  char the character read
     * @throws  io.IOException in case of an error
     */
    function readChar() {
      if (FALSE === ($result= gzgetc($this->_fd))) {
        return throw(new IOException('readChar() cannot read '.$bytes.' bytes from '.$this->uri));
      }
      return $result;
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
     * @throws  io.IOException in case of an error
     */
    function gets($bytes= 4096) {
      if (FALSE === ($result= gzgets($this->_fd, $bytes))) {
        return throw(new IOException('gets() cannot read '.$bytes.' bytes from '.$this->uri));
      }
      return $result;
    }

    /**
     * Read (binary-safe)
     *
     * @access  public
     * @param   int bytes default 4096 Max. ammount of bytes to be read
     * @return  string Data read
     * @throws  io.IOException in case of an error
     */
    function read($bytes= 4096) {
      if (FALSE === ($result= gzread($this->_fd, $bytes))) {
        return throw(new IOException('read() cannot read '.$bytes.' bytes from '.$this->uri));
      }
      return $result;
    }

    /**
     * Write
     *
     * @access  public
     * @param   string string data to write
     * @return  bool success
     * @throws  io.IOException in case of an error
     */
    function write($string) {
      if (FALSE === ($result= gzwrite($this->_fd, $string))) {
        throw(new IOException('cannot write '.strlen($string).' bytes to '.$this->uri));
      }
      return $result;
    }

    /**
     * Write a line and append a LF (\n) character
     *
     * @access  public
     * @param   string string data to write
     * @return  bool success
     * @throws  io.IOException in case of an error
     */
    function writeLine($string) {
      if (FALSE === ($result= gzputs($this->_fd, $string."\n"))) {
        throw(new IOException('cannot write '.(strlen($string)+ 1).' bytes to '.$this->uri));
      }
      return $result;
    }
    
    /**
     * Returns whether the file pointer is at the end of the file
     *
     * Hint:
     * Use isOpen() to check if the file is open
     *
     * @see     php://feof
     * @access  public
     * @return  bool TRUE when the end of the file is reached
     * @throws  io.IOException in case of an error (e.g., the file's not been opened)
     */
    function eof() {
      $result= gzeof($this->_fd);
      if (xp::errorAt(__FILE__, __LINE__ - 1)) {
        return throw(new IOException('cannot determine eof of '.$this->uri));
      }
      return $result;
    }

    /**
     * Sets the file position indicator for fp to the beginning of the 
     * file stream. 
     * 
     * This function is identical to a call of $f->seek(0, SEEK_SET)
     *
     * @access  public
     * @throws  io.IOException in case of an error
     */
    function rewind() {
      if (FALSE === ($result= gzrewind($this->_fd))) {
        return throw(new IOException('cannot rewind file pointer'));
      }
      return TRUE;
    }
    
    /**
     * Move file pointer to a new position
     *
     * @access  public
     * @param   int position default 0 The new position
     * @param   int mode default SEEK_SET 
     * @see     php://gzseek
     * @throws  io.IOException in case of an error
     * @return  bool success
     */
    function seek($position= 0, $mode= SEEK_SET) {
      if (0 != ($result= gzseek($this->_fd, $position, $mode))) {
        return throw(new IOException('seek error, position '.$position.' in mode '.$mode));
      }
      return TRUE;
    }
    
    /**
     * Retrieve file pointer position
     *
     * @access  public
     * @throws  io.IOException in case of an error
     * @return  int position
     */
    function tell($position= 0, $mode= SEEK_SET) {
      $result= gztell($this->_fd);
      if ((FALSE === $result) && xp::errorAt(__FILE__, __LINE__ - 1)) {
        return throw(new IOException('retrieve file pointer\'s position '.$this->uri));
      }
      return $result;
    }

    /**
     * Close this file
     *
     * @access  public
     * @return  bool success
     */
    function close() {
      $result= gzclose($this->_fd);
      unset($this->_fd);
      return $result;
    }
  }
?>
