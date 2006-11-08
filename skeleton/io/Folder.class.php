<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  uses('io.IOException');
    
  /**
   * Represents a Folder
   *
   * Usage:
   * <code>
   *   try(); {
   *     $d= new Folder('/etc/');
   *     while (FALSE !== ($entry= $d->getEntry())) {
   *       printf("%s/%s\n", $d->uri, $entry);
   *     }
   *     $d->close();
   *   } if (catch('IOException', $e)) {
   *     $e->printStackTrace();
   *   }
   * </code>
   */
  class Folder extends Object {
    var 
      $uri= '',
      $dirname= '',
      $path= '';
    
    var
      $_hdir= FALSE;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string dirname the directory's name
     */
    function __construct($dirname= NULL) {
      if (NULL != $dirname) $this->setURI($dirname);
      
    }
    
    /**
     * Destructor
     *
     * @access  public
     */
    function __destruct() {
      $this->close();
    }
    
    /**
     * Close directory
     *
     * @access  public
     */
    function close() {
      if (FALSE != $this->_hdir) $this->_hdir->close();
      $this->_hdir= FALSE;
    }

    /**
     * Set URI
     *
     * @access  private
     * @param   string uri the complete path name
     */
    function setURI($uri) {
      $this->uri= realpath($uri);
      
      // Bug in real_path if file is not existant
      if ('' == $this->uri && $uri!= $this->uri) $this->uri= $uri;
      
      // Add trailing / (or \, or whatever else DIRECTORY_SEPARATOR is defined to)
      // if necessary
      if (DIRECTORY_SEPARATOR != substr($this->uri, -1 * strlen(DIRECTORY_SEPARATOR))) {
        $this->uri.= DIRECTORY_SEPARATOR;
      }
      
      $this->path= dirname($uri);
      $this->dirname= basename($uri);
    }
    
    /**
     * Get URI
     *
     * @access public
     * @return uri of this folder
     */    
    function getURI() {
      return $this->uri;
    }
    
    /**
     * Create this directory, recursively, if needed.
     *
     * @access  public
     * @param   int permissions default 0700
     * @return  bool TRUE in case the creation succeeded or the directory already exists
     * @throws  io.IOException in case of an error
     */
    function create($permissions= 0700) {
      if (is_dir($this->uri)) return TRUE;
      $i= 0;
      $umask= umask(000);
      while (FALSE !== ($i= strpos($this->uri, DIRECTORY_SEPARATOR, $i))) {
        if (is_dir($d= substr($this->uri, 0, ++$i))) continue;
        if (FALSE === mkdir($d, $permissions)) {
          umask($umask);
          return throw(new IOException(sprintf(
            'mkdir("%s", %d) failed',
            $d,
            $permissions
          )));
        }
      }
      umask($umask);
      return TRUE;
    }
    
    /**
     * Delete this folder and all its subentries recursively
     * Warning: Stops at the first element that can't be deleted!
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException in case one of the entries could'nt be deleted
     */
    function unlink($uri= NULL) {
      if (NULL === $uri) $uri= $this->uri; // We also use this recursively
      
      if (FALSE === ($d= dir($uri))) {
        return throw(new IOException('Directory '.$uri.' does not exist'));
      }
      
      while (FALSE !== ($e= $d->read())) {
        if ('.' == $e || '..' == $e) continue;
        
        $fn= $d->path.$e;
        if (!is_dir($fn)) {
          $ret= unlink($fn);
        } else {
          $ret= $this->unlink($fn.DIRECTORY_SEPARATOR);
        }
        if (FALSE === $ret) return throw(new IOException(sprintf(
          'unlink of "%s" failed',
          $fn
        )));
      }
      $d->close();

      if (FALSE === rmdir($uri)) return throw(new IOException(sprintf(
        'unlink of "%s" failed',
        $uri
      )));
      
      return TRUE;
    }

    /**
     * Move this directory
     *
     * Warning: Open directories cannot be moved. Use the close() method to
     * close the directory first
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException in case of an error (e.g., lack of permissions)
     * @throws  lang.IllegalStateException in case the directory is still open
     */
    function move($target) {
      if (is_resource($this->_hdir)) {
        return throw(new IllegalStateException('Directory still open'));
      }
      if (FALSE === rename($this->uri, $target)) {
        return throw(new IOException('Cannot move directory '.$this->uri.' to '.$target));
      }
      return TRUE;
    }

    /**
     * Returns whether this directory exists
     *
     * @access  public
     * @return  bool TRUE in case the directory exists
     */
    function exists() {
      return is_dir($this->uri);
    }
    
    /**
     * Read through the contents of the directory, ommitting the entries "." and ".."
     *
     * @access  public
     * @return  string entry directory entry (w/o path!), FALSE, if no more entries are left
     * @throws  io.IOException in case an error occurs
     */
    function getEntry() {
      if (FALSE === $this->_hdir) {

        // Not open yet, try to open
        if (!is_object($this->_hdir= dir($this->uri))) {
          $this->_hdir= FALSE;
          return throw(new IOException('Cannot open directory "'.$this->uri.'"'));
        }
      }
      
      while (FALSE !== ($entry= $this->_hdir->read())) {
        if ($entry != '.' && $entry != '..') return $entry;
      }
      return FALSE;
    }
   
    /**
     * Rewinds the directory to the beginning.
     *
     * @access  public
     * @throws  io.IOException in case an error occurs
     */
    function rewind() {
      if (FALSE === $this->_hdir)
        return throw (new IOException ('Cannot rewind non-open folder.'));
      
      rewinddir ($this->_hdir->handle);
    }

    /**
     * Retrieve when the folder was created
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
      if (FALSE === ($atime= fileatime($this->uri))) {
        return throw(new IOException('Cannot get atime for '.$this->uri));
      }
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
      if (FALSE === ($mtime= filemtime($this->uri))) {
        return throw(new IOException('Cannot get mtime for '.$this->uri));
      }
      return $mtime;
    }

    /**
     * Returns a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        '%s(uri= %s)',
        $this->getClassName(),
        $this->uri
      );
    }

    /**
     * Return if the folder was already opened
     *
     * @access  public
     * @return  bool
     */
    function isOpen() {
      return is_resource($this->_hdir);
    }
  }
?>
