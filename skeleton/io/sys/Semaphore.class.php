<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * Semaphore
   *
   * <code>
   *   $s= &Semaphore::get(6100);
   *   $s->acquire();
   *   // [...]
   *   $s->release();
   *   $s->remove();
   * </code>
   *
   * @purpose   Provide a wrapper around semaphores
   * @ext       sem
   * @see       http://www.cs.cf.ac.uk/Dave/C/node27.html#SECTION002700000000000000000
   * @see       http://www.cs.cf.ac.uk/Dave/C/node26.html#SECTION002600000000000000000
   */
  class Semaphore extends Object {
    var
      $key       = 0,
      $maxAquire = 1;
      
    var
      $_hdl      = NULL;
      
    /**
     * Get a semaphore
     *
     * Note: A second call to this function with the same key will actually return
     * the same semaphore
     *
     * @model   static
     * @access  public
     * @param   int key
     * @param   int maxAquire default 1
     * @param   int permissions default 0666
     * @return  &io.sys.Semaphore a semaphore
     * @throws  io.IOException
     */
    function &get($key, $maxAquire= 1, $permissions= 0666) {
      static $semaphores= array();
      
      if (!isset($semaphores[$key])) {
        $s= &new Semaphore();
        $s->key= $key;
        $s->maxAquire= $maxAquire;
        $s->permissions= $permissions;
        if (FALSE === ($s->_hdl= sem_get($key, $maxAquire, $permissions, TRUE))) {
          return throw(new IOException('Could not get semaphore '.$key));
        }
        
        $semaphores[$key]= &$s;
      }
      
      return $semaphores[$key];
    }
    
    /**
     * Acquire a semaphore - blocks (if necessary) until the semaphore can be acquired. 
     * A process attempting to acquire a semaphore which it has already acquired will 
     * block forever if acquiring the semaphore would cause its max_acquire value to 
     * be exceeded. 
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException
     */
    function acquire() {
      if (FALSE === sem_acquire($this->_hdl)) {
        return throw(new IOException('Could not acquire semaphore '.$this->key));
      }
      return TRUE;
    }
    
    /**
     * Release a semaphore
     * After releasing the semaphore, acquire() may be called to re-acquire it. 
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException
     * @see     xp://io.sys.Semaphore#acquire
     */
    function release() {
      if (FALSE === sem_release($this->_hdl)) {
        return throw(new IOException('Could not release semaphore '.$this->key));
      }
      return TRUE;
    }
    
    /**
     * Remove a semaphore
     * After removing the semaphore, it is no more accessible.
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException
     */
    function remove() {
      if (FALSE === sem_remove($this->_hdl)) {
        return throw(new IOException('Could not remove semaphore '.$this->key));
      }
      return TRUE;
    }
  }
?>
