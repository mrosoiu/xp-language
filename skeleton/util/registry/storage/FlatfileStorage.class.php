<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'lang.System',
    'io.File',
    'io.FileUtil',
    'util.Hashmap'
  );
  
  /**
   * DBA storage
   *
   * @purpose  A storage provider that uses flat files
   * @see      php://serialize
   */
  class FlatfileStorage extends Object {
    var
      $_file   = NULL,
      $_hash   = NULL;

    /**
     * Initialize this storage
     *
     * @access  public
     * @param   string id
     */
    function initialize($id) {
      $this->_file= &new File(System::tempDir().DIRECTORY_SEPARATOR.$id.'.dat');
      if ($this->_file->exists()) {
        $this->_hash= unserialize(FileUtil::getContents($this->_file));
      }
      
      if (!$this->_hash) {
        touch($this->_file->getURI());
        $this->_hash= &new Hashmap();
      }    
    }
    
    /**
     * Returns whether this storage contains the given key
     *
     * @access  public
     * @param   string key
     * @return  bool TRUE when this key exists
     */
    function contains($key) {
      return $this->_hash->containsKey($key);
    }

    /**
     * Get all keys
     *
     * @access  public
     * @return  string[] key
     */
    function keys() { 
      return $this->_hash->keys();
    }
    
    /**
     * Get a key by it's name
     *
     * @access  public
     * @param   string key
     * @return  &mixed
     */
    function &get($key) {
      return $this->_hash->get($key);
    }

    /**
     * Insert/update a key
     *
     * @access  public 
     * @param   string key
     * @param   &mixed value
     * @param   int permissions default 0666 (ignored for this storage)
     */
    function put($key, &$value, $permissions= 0666) {
      $this->_hash->put($key, $value);
      FileUtil::setContents($this->_file, serialize($this->_hash));
    }

    /**
     * Remove a key
     *
     * @access  public
     * @param   string key
     */
    function remove($key) {
      $this->_hash->remove($key);
      FileUtil::setContents($this->_file, serialize($this->_hash));
    }
  
    /**
     * Remove all keys
     *
     * @access  public
     */
    function free() { 
      $this->_hash->clear();
      FileUtil::setContents($this->_file, serialize($this->_hash));
    }
  } implements(__FILE__, 'util.registry.RegistryStorageProvider');
?>
