<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('io.sys.ShmSegment', 'util.registry.storage.RegistryStorage');
  
  /**
   * Shared Memory storage
   *
   * @purpose  A storage provider that uses shared memory
   * @see      xp://io.sys.ShmSegment
   */
  class SharedMemoryStorage extends RegistryStorage {
    var
      $segments = array();
      
    var
      $_seg     = NULL;
    
    /**
     * Initialize this storage
     *
     * @access  public
     */
    function initialize() {
      $this->_seg= &new ShmSegment($this->id);
      if (!$this->_seg->isEmpty('segments')) {
        $this->segments= $this->_seg->get('segments');
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
      return isset($this->segments[$key]);
    }
    
    /**
     * Get a key by it's name
     *
     * @access  public
     * @param   string key
     * @return  &mixed
     */
    function &get($key) {
      if (!isset($this->segments[$key])) {
        return throw(new ElementNotFoundException($key.' does not exist'));
      }
      
      return $this->segments[$key]->get();
    }

    /**
     * Insert/update a key
     *
     * @access  public 
     * @param   string key
     * @param   &mixed value
     * @param   int permissions default 0666
     */
    function put($key, &$value, $permissions= 0666) {
      if (!isset($this->segments[$key])) {
        $this->segments[$key]= &new ShmSegment($key);
        $this->_seg->put($this->segments);
      }
      return $this->segments[$key]->put($value, $permissions);
    }

    /**
     * Remove a key
     *
     * @access  public
     * @param   string key
     */
    function remove($key) {
      if (!isset($this->segments[$key])) {
        return throw(new ElementNotFoundException($key.' does not exist'));
      }
      
      if (FALSE === $this->segments[$key]->remove()) return FALSE;
      
      unset($this->segments[$key]);
      $this->_seg->put($this->segments);
    }
  
  }
?>
