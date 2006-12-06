<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  // Mode constants for open() method
  define('FILE_MODE_READ',      'rb');          // Read
  define('FILE_MODE_READWRITE', 'rb+');         // Read/Write
  define('FILE_MODE_WRITE',     'wb');          // Write
  define('FILE_MODE_REWRITE',   'wb+');         // Read/Write, truncate on open
  define('FILE_MODE_APPEND',    'ab');          // Append (Read-only)
  define('FILE_MODE_READAPPEND','ab+');         // Append (Read/Write)
  
  uses(
    'io.Stream',
    'io.IOException',
    'io.FileNotFoundException'
  );
    
  /**
   * Instances of the file class serve as an opaque handle to the underlying machine-
   * specific structure representing an open file.
   * 
   * @purpose  Represent a file
   */
  class File extends Stream {
    var 
      $uri=         '', 
      $filename=    '',
      $path=        '',
      $extension=   '',
      $mode=        FILE_MODE_READ;
    
    var 
      $_fd= NULL;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   mixed file either a filename or a resource (as returned from fopen)
     */
    function __construct($file) {
      if (is_resource($file)) {
        $this->uri= NULL;
        $this->_fd= $file;
      } else {
        $this->setURI($file);
      }
    }
    
    /**
     * Retrieve internal file handle
     *
     * @access  public
     * @return  resource
     */
    function getHandle() {
      return $this->_fd;
    }
    
    /**
     * Returns the URI of the file
     *
     * @access public
     * @return string uri
     */
    function getURI() {
      return $this->uri;
    }
    
    /**
     * Returns the filename of the file
     *
     * @access public
     * @return string filename
     */
    function getFileName() {
      return $this->filename;
    }

    /**
     * Get Path
     *
     * @access  public
     * @return  string
     */
    function getPath() {
      return $this->path;
    }

    /**
     * Get Extension
     *
     * @access  public
     * @return  string
     */
    function getExtension() {
      return $this->extension;
    }

    /**
     * Set this file's URI
     *
     * @access  private
     * @param   string uri
     */
    function setURI($uri) {
    
      // PHP-Scheme
      if (0 == strncmp('php://', $uri, 6)) {
        $this->path= NULL;
        $this->extension= NULL;
        $this->filename= $this->uri= $uri;
        return;
      }
      
      $this->uri= realpath($uri);
      
      // Bug in real_path when file does not exist
      if ('' == $this->uri && $uri != $this->uri) $this->uri= $uri;
      
      with ($pathinfo= pathinfo($uri)); {
        $this->path= $pathinfo['dirname'];
        $this->filename= $pathinfo['basename'];
        $this->extension= isset($pathinfo['extension']) ? $pathinfo['extension'] : NULL;
      }
    }

    /**
     * Open the file
     *
     * @access  public
     * @param   string mode one of the FILE_MODE_* constants
     * @return  bool TRUE if file could be opened
     * @throws  io.FileNotFoundException in case the file is not found
     * @throws  io.IOException in case the file cannot be opened (e.g., lacking permissions)
     */
    function open($mode= FILE_MODE_READ) {
      $this->mode= $mode;
      if (
        0 != strncmp('php://', $this->uri, 6) &&
        (FILE_MODE_READ == $mode) && 
        (!$this->exists())
      ) return throw(new FileNotFoundException($this->uri));
      
      $this->_fd= fopen($this->uri, $this->mode);
      if (!$this->_fd) return throw(new IOException('Cannot open '.$this->uri.' mode '.$this->mode));
      
      return TRUE;
    }
    
    /**
     * Returns whether this file is open
     *
     * @access  public
     * @return  bool TRUE, if the file is open
     */
    function isOpen() {
      return $this->_fd;
    }
    
    /**
     * Returns whether this file eixtss
     *
     * @access  public
     * @return  bool TRUE in case the file exists
     */
    function exists() {
      return file_exists($this->uri);
    }
    
    /**
     * Retrieve the file's size in bytes
     *
     * @access  public
     * @return  int size filesize in bytes
     * @throws  io.IOException in case of an error
     */
    function size() {
      $size= filesize($this->uri);
      if (FALSE === $size) return throw(new IOException('Cannot get filesize for '.$this->uri));
      return $size;
    }
    
    /**
     * Truncate the file to the specified length
     *
     * @access  public
     * @param   bool TRUE if method succeeded
     * @throws  io.IOException in case of an error
     */
    function truncate($size= 0) {
      $return= ftruncate($this->_fd, $size);
      if (FALSE === $return) return throw(new IOException('Cannot truncate file '.$this->uri));
      return $return;
    }

    /**
     * Retrieve last access time
     *
     * Note: 
     * The atime of a file is supposed to change whenever the data blocks of a file 
     * are being read. This can be costly performancewise when an application 
     * regularly accesses a very large number of files or directories. Some Unix 
     * filesystems can be mounted with atime updates disabled to increase the 
     * performance of such applications; USENET news spools are a common example. 
     * On such filesystems this function will be useless. 
     *
     * @access  public
     * @return  int The date the file was last accessed as a unix-timestamp
     * @throws  io.IOException in case of an error
     */
    function lastAccessed() {
      $atime= fileatime($this->uri);
      if (FALSE === $atime) return throw(new IOException('Cannot get atime for '.$this->uri));
      return $atime;
    }
    
    /**
     * Retrieve last modification time
     *
     * @access  public
     * @return  int The date the file was last modified as a unix-timestamp
     * @throws  io.IOException in case of an error
     */
    function lastModified() {
      $mtime= filemtime($this->uri);
      if (FALSE === $mtime) return throw(new IOException('Cannot get mtime for '.$this->uri));
      return $mtime;
    }
    
    /**
     * Set last modification time
     *
     * @access  public
     * @param   int time default -1 Unix-timestamp
     * @return  bool success
     * @throws  io.IOException in case of an error
     */
    function touch($time= -1) {
      if (-1 == $time) $time= time();
      if (FALSE === touch($this->uri, $time)) {
        return throw(new IOException('Cannot set mtime for '.$this->uri));
      }
      return TRUE;
    }

    /**
     * Retrieve when the file was created
     *
     * @access  public
     * @return  int The date the file was created as a unix-timestamp
     * @throws  io.IOException in case of an error
     */
    function createdAt() {
      if (FALSE === ($mtime= filectime($this->uri))) {
        return throw(new IOException('Cannot get mtime for '.$this->uri));
      }
      return $mtime;
    }

    /**
     * Read one line and chop off trailing CR and LF characters
     *
     * Returns a string of up to length - 1 bytes read from the file. 
     * Reading ends when length - 1 bytes have been read, on a newline (which is 
     * included in the return value), or on EOF (whichever comes first). 
     *
     * @access  public
     * @param   int bytes default 4096 Max. amount of bytes to be read
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
      if (FALSE === ($result= fgetc($this->_fd)) && !feof($this->_fd)) {
        return throw(new IOException('Cannot read 1 byte from '.$this->uri));
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
     * @param   int bytes default 4096 Max. amount of bytes to read
     * @return  string Data read
     * @throws  io.IOException in case of an error
     */
    function gets($bytes= 4096) {
      if (FALSE === ($result= fgets($this->_fd, $bytes)) && !feof($this->_fd)) {
        return throw(new IOException('Cannot read '.$bytes.' bytes from '.$this->uri));
      }
      return $result;
    }

    /**
     * Read (binary-safe)
     *
     * @access  public
     * @param   int bytes default 4096 Max. amount of bytes to read
     * @return  string Data read
     * @throws  io.IOException in case of an error
     */
    function read($bytes= 4096) {
      if (FALSE === ($result= fread($this->_fd, $bytes)) && !feof($this->_fd)) {
        return throw(new IOException('Cannot read '.$bytes.' bytes from '.$this->uri));
      }
      return $result;
    }

    /**
     * Write
     *
     * @access  public
     * @param   string string data to write
     * @return  int number of bytes written
     * @throws  io.IOException in case of an error
     */
    function write($string) {
      if (FALSE === ($result= fwrite($this->_fd, $string))) {
        return throw(new IOException('Cannot write '.strlen($string).' bytes to '.$this->uri));
      }
      return $result;
    }

    /**
     * Write a line and append a LF (\n) character
     *
     * @access  public
     * @param   string string data default '' to write
     * @return  int number of bytes written
     * @throws  io.IOException in case of an error
     */
    function writeLine($string= '') {
      if (FALSE === ($result= fwrite($this->_fd, $string."\n"))) {
        return throw(new IOException('Cannot write '.(strlen($string)+ 1).' bytes to '.$this->uri));
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
      $result= feof($this->_fd);
      if (xp::errorAt(__FILE__, __LINE__ - 1)) {
        return throw(new IOException('Cannot determine eof of '.$this->uri));
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
     * @return  bool TRUE if rewind suceeded
     * @throws  io.IOException in case of an error
     */
    function rewind() {
      if (FALSE === ($result= rewind($this->_fd))) {
        return throw(new IOException('Cannot rewind file pointer'));
      }
      return TRUE;
    }
    
    /**
     * Move file pointer to a new position
     *
     * @access  public
     * @param   int position default 0 The new position
     * @param   int mode default SEEK_SET 
     * @see     php://fseek
     * @throws  io.IOException in case of an error
     * @return  bool success
     */
    function seek($position= 0, $mode= SEEK_SET) {
      if (0 != ($result= fseek($this->_fd, $position, $mode))) {
        return throw(new IOException('Seek error, position '.$position.' in mode '.$mode));
      }
      return TRUE;
    }
    
    /**
     * Retrieve file pointer position
     *
     * @access  public
     * @return  int position
     * @throws  io.IOException in case of an error
     */
    function tell() {
      $result= ftell($this->_fd);
      if (FALSE === $result) return throw(new IOException('Cannot retrieve file pointer\'s position'));
      return $result;
    }

    /**
     * Private wrapper function for locking
     *
     * Warning:
     * flock() will not work on NFS and many other networked file systems. Check your 
     * operating system documentation for more details. On some operating systems flock() 
     * is implemented at the process level. When using a multithreaded server API like 
     * ISAPI you may not be able to rely on flock() to protect files against other PHP 
     * scripts running in parallel threads of the same server instance! flock() is not 
     * supported on antiquated filesystems like FAT and its derivates and will therefore 
     * always return FALSE under this environments (this is especially true for Windows 98 
     * users). 
     *
     * The optional second argument is set to TRUE if the lock would block (EWOULDBLOCK 
     * errno condition).
     *
     * @access  private
     * @param   int op operation (one of the predefined LOCK_* constants)
     * @throws  io.IOException in case of an error
     * @return  bool success
     * @see     php://flock
     */
    function _lock($mode) {
      if (FALSE === flock($this->_fd, $mode)) {
        $os= '';
        foreach (array(
          LOCK_NB   => 'LOCK_NB',
          LOCK_UN   => 'LOCK_UN', 
          LOCK_EX   => 'LOCK_EX', 
          LOCK_SH   => 'LOCK_SH' 
        ) as $o => $s) {
          if ($mode >= $o) { 
            $os.= ' | '.$s;
            $mode-= $o;
          }
        }
        return throw(new IOException('Cannot lock file '.$this->uri.' w/ '.substr($os, 3)));
      }
      
      return TRUE;
    }
    
    /**
     * Acquire a shared lock (reader)
     *
     * @access  public
     * @param   bool block default FALSE
     * @see     xp://io.File#_lock
     * @return  bool success
     */
    function lockShared($block= FALSE) {
      return $this->_lock(LOCK_SH + ($block ? 0 : LOCK_NB));
    }
    
    /**
     * Acquire an exclusive lock (writer)
     *
     * @access  public
     * @param   bool block default FALSE
     * @see     xp://io.File#_lock
     * @return  bool success
     */
    function lockExclusive($block= FALSE) {
      return $this->_lock(LOCK_EX + ($block ? 0 : LOCK_NB));
    }
    
    /**
     * Release a lock (shared or exclusive)
     *
     * @access  public
     * @see     xp://io.File#_lock
     * @return  bool success
     */
    function unLock() {
      return $this->_lock(LOCK_UN);
    }

    /**
     * Close this file
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException if close fails
     */
    function close() {
      if (FALSE === fclose($this->_fd)) {
        return throw(new IOException('Cannot close file '.$this->uri));
      }
      
      $this->_fd= NULL;
      return TRUE;
    }
    
    /**
     * Delete this file
     *
     * Warning: Open files cannot be deleted. Use the close() method to
     * close the file first
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException in case of an error (e.g., lack of permissions)
     * @throws  lang.IllegalStateException in case the file is still open
     */
    function unlink() {
      if (is_resource($this->_fd)) {
        return throw(new IllegalStateException('File still open'));
      }
      
      if (FALSE === unlink($this->uri)) {
        return throw(new IOException('Cannot delete file '.$this->uri));
      }
      return TRUE;
    }
    
    /**
     * Move this file
     *
     * Warning: Open files cannot be moved. Use the close() method to
     * close the file first
     *
     * @access  public
     * @param   string target where to move the file to
     * @return  bool success
     * @throws  io.IOException in case of an error (e.g., lack of permissions)
     * @throws  lang.IllegalStateException in case the file is still open
     */
    function move($target) {
      if (is_resource($this->_fd)) {
        return throw(new IllegalStateException('File still open'));
      }
      
      if (FALSE === rename($this->uri, $target)) {
        return throw(new IOException('Cannot move file '.$this->uri.' to '.$target));
      }
      
      $this->setURI($target);
      return TRUE;
    }
    
    /**
     * Copy this file
     *
     * Warning: Open files cannot be copied. Use the close() method to
     * close the file first
     *
     * @access  public
     * @param   string target where to copy the file to
     * @return  bool success
     * @throws  io.IOException in case of an error (e.g., lack of permissions)
     * @throws  lang.IllegalStateException in case the file is still open
     */
    function copy($target) {
      if (is_resource($this->_fd)) {
        return throw(new IllegalStateException('File still open'));
      }
      
      if (FALSE === copy($this->uri, $target)) {
        return throw(new IOException('Cannot copy file '.$this->uri.' to '.$target));
      }
      return TRUE;
    }
    
    /**
     * Change permissions for the file
     *
     * @see     php://chmod
     * @access  public
     * @param   mixed mode
     * @return  bool success
     */
    function setPermissions($mode) {
      return chmod($this->uri, $mode);
    }
    
    /**
     * Get permission mask of the file
     *
     * @see     php://stat
     * @access  public
     * @return  int
     */
    function getPermissions() {
      $stat= stat($this->uri);
      return $stat['mode'];
    }

    /**
     * Returns a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        '%s(uri= %s, mode= %s)',
        $this->getClassName(),
        $this->uri,
        $this->mode
      );
    }
  }
?>
