<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.File');

  /**
   * Implements the StorageElement via filesystem
   *
   * @ext      posix
   * @purpose  StorageElement
   */
  class FilesystemStorageElement extends Object implements StorageElement {
    public
      $path = NULL,
      $f    = NULL,
      $st   = array();

    /**
     * Constructor
     *
     * @access  public
     * @param string path The path to resource (including file's name)
     * @param string root The FTP root directory
     * @return  string uri
     */
    public function __construct($path, $root) { 
      $this->path= $path;
      $this->f= &new File($root.$path);
      $this->st= stat($this->f->getURI());
      $this->st['pwuid']= posix_getpwuid($this->st['uid']);
      $this->st['grgid']= posix_getgrgid($this->st['gid']);
    }

    /**
     * Deletes an entry
     *
     * @access  public
     * @return  bool TRUE to indicate success
     */
    public function delete() { 
      return $this->f->unlink();
    }

    /**
     * Renames an entry
     *
     * @access  public
     * @param   string target
     * @return  bool TRUE to indicate success
     */
    public function rename($target) {
      $path= (DIRECTORY_SEPARATOR == $target{0}
        ? substr($this->f->getPath(), 0, strrpos($this->f->getPath(), dirname($target)))
        : $this->f->getPath().DIRECTORY_SEPARATOR
      ).$target;
            
      return $this->f->move($path);
    }
    
    /**
     * Returns the filename including the path (relative to storage root)
     *
     * @access public
     * @return string
     */
    public function getFilename() {
      return $this->path;
    }

    /**
     * Retrieves the (short) name of a storage entry
     *
     * @access  public
     * @return  string
     */  
    public function getName() { 
      return basename($this->f->getURI());
    }
    
    /**
     * Retrieves the owner's username
     *
     * @access  public
     * @return  string
     */  
    public function getOwner() { 
      return $this->st['pwuid']['name'];
    }

    /**
     * Retrieves the owner's group name
     *
     * @access  public
     * @return  string
     */  
    public function getGroup() {
      return $this->st['grgid']['name'];
    }
    
    /**
     * Retrieves the size of this storage entry
     *
     * @access  public
     * @return  int bytes
     */  
    public function getSize() { 
      return $this->st['size'];
    }

    /**
     * Retrieves the modified timestamp of this storage entry
     *
     * @access  public
     * @return  int unix timestamp
     */  
    public function getModifiedStamp() { 
      return $this->st['mtime'];
    }
    
    /**
     * Retrieves the permissions of this storage entry expressed in a
     * unix-permission style integer
     *
     * @see     http://www.google.com/search?ie=UTF8&q=Unix%20permissions
     * @access  public
     * @return  int
     */  
    public function getPermissions() { 
      return $this->st['mode'];
    }

    /**
     * Sets the permissions of this storage entry expressed in a
     * unix-permission style integer
     *
     * @access  public
     * @param   int permissions
     */  
    public function setPermissions($permissions) { 
      chmod($this->f->getURI(), intval((string)$permissions, 8));
      $this->st['mode']= $permissions;
    }

    /**
     * Retrieves the number of links
     *
     * @access  protected
     * @return  string
     */
    public function numLinks() {
      return $this->st['nlink'];
    }

    /**
     * Open this element with a specified mode
     *
     * @access  public
     * @param   string mode of of the SE_* constants
     */
    public function open($mode) { 
      switch ($mode) {
        case SE_READ: return $this->f->open(FILE_MODE_READ);
        case SE_WRITE: return $this->f->open(FILE_MODE_WRITE);
      }
    }
    
    /**
     * Read a chunk of data from this element
     *
     * @access  public
     * @return  string
     */
    public function read() { 
      return $this->f->read(32768);
    }
    
    /**
     * Write a chunk of data to this element
     *
     * @access  public
     * @param   string buf
     */
    public function write($buf) { 
      return $this->f->write($buf);
    }
    
    /**
     * Close this element
     *
     * @access  public
     */
    public function close() { 
      return $this->f->close();
    }
    
  } 
?>
