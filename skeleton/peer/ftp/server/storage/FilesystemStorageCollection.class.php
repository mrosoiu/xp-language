<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.Folder', 'peer.ftp.server.storage.FilesystemStorageElement');

  /**
   * Implements the StorageCollection via filesystem
   *
   * @ext      posix
   * @purpose  StorageCollection
   */
  class FilesystemStorageCollection extends Object {
    var
      $f    = NULL,
      $name = '';

    /**
     * Constructor
     *
     * @access  public
     * @return  string uri
     */
    function __construct($uri) {
      if ('..' == substr($uri, -2)) {
        $this->name= '..';
      } elseif ('.' == substr($uri, -1)) {
        $this->name= '.';
      } else {
        $this->name= basename($uri);
      }
      $this->f= &new Folder($uri);
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
    function delete() { 
      return $this->f->unlink();
    }

    /**
     * Renames an entry
     *
     * @access  public
     * @param   string target
     * @return  bool TRUE to indicate success
     */
    function rename($target) { 
      return $this->f->move($target);
    }

    /**
     * Retrieves the (short) name of a storage entry
     *
     * @access  public
     * @return  string
     */  
    function getName() { 
      return $this->name;
    }
    
    /**
     * Retrieves the owner's username
     *
     * @access  public
     * @return  string
     */  
    function getOwner() { 
      return $this->st['pwuid']['name'];
    }

    /**
     * Retrieves the owner's group name
     *
     * @access  public
     * @return  string
     */  
    function getGroup() {
      return $this->st['grgid']['name'];
    }
    
    /**
     * Retrieves the size of this storage entry
     *
     * @access  public
     * @return  int bytes
     */  
    function getSize() { 
      return $this->st['size'];
    }

    /**
     * Retrieves the modified timestamp of this storage entry
     *
     * @access  public
     * @return  int unix timestamp
     */  
    function getModifiedStamp() { 
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
    function getPermissions() { 
      return $this->st['mode'];
    }

    /**
     * Sets the permissions of this storage entry expressed in a
     * unix-permission style integer
     *
     * @access  public
     * @param   int permissions
     */  
    function setPermissions($permissions) {
      chmod($this->f->getURI(), intval((string)$permissions, 8));
      $this->st['mode']= $permissions;
    }

    /**
     * Retrieves the number of links
     *
     * @access  public
     * @return  string
     */
    function numLinks() {
      return $this->st['nlink'];
    }
    
    /**
     * Retrieves a list of elements
     *
     * @access  public
     * @return  &peer.ftp.server.storage.StorageEntry[]
     */
    function elements() { 
      $r= array();
      $r[]= &new FilesystemStorageCollection($this->f->getURI().'.');
      $r[]= &new FilesystemStorageCollection($this->f->getURI().'..');
      while ($entry= $this->f->getEntry()) {
        $path= $this->f->getURI().$entry;
        if (is_dir($path)) {
          $r[]= &new FilesystemStorageCollection($path);
        } else {
          $r[]= &new FilesystemStorageElement($path);
        }
      }
      $this->f->rewind();
      return $r;
    }
  
  } implements(__FILE__, 'peer.ftp.server.storage.StorageCollection');
?>
