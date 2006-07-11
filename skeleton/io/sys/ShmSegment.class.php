<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('io.IOException');
   
  /**
   * Shared memory segment
   *
   * Shared memory may be used to provide access to global variables. Different 
   * httpd-daemons and even other programs (such as Perl, C, ...) are able to access 
   * this data to provide a global data-exchange. Remember, that shared memory is NOT 
   * safe against simultaneous access. Use semaphores for synchronization.
   *
   * Note: This extension is not available on Windows platforms. 
   *
   * @purpose   Provide a wrapper around shared memory segments
   * @ext       sem
   * @see       http://www.cs.cf.ac.uk/Dave/C/node27.html#SECTION002700000000000000000
   * @see       http://www.cs.cf.ac.uk/Dave/C/node26.html#SECTION002600000000000000000
   * @see       xp://io.sys.Semaphore
   */
  class ShmSegment extends Object {
    var 
      $name     = '',
      $spot     = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     */
    function __construct($name) {
      $this->name= $name;
      $str= str_pad($name, 4, 'Z');
      $this->spot= '';
      for ($i= 0; $i < 4; $i++) {
        $this->spot.= dechex(ord($str{$i}));
      }
      $this->spot= hexdec('0x'.$this->spot);
      
    }
    
    /**
     * Private helper function
     *
     * @access  private
     * @return  &mixed data
     */
    function &_get() {
      $h= shm_attach($this->spot);
      $data= shm_get_var($h, $this->name);
      shm_detach($h);
      
      return is_array($data) ? $data : FALSE;
    }
    
    /**
     * Returns whether this segment is empty (i.e., has not been written or was
     * previously removed)
     *
     * @access  public
     * @return  bool TRUE if this segment is empty
     */
    function isEmpty() {
      return (FALSE === $this->_get());
    }
    
    /**
     * Get this segment's contents
     *
     * @access  public
     * @return  &mixed data
     * @throws  io.IOException in case an error occurs
     */
    function &get() {
      if (FALSE === ($data= $this->_get())) {
        return throw(new IOException('Could not read segment '.$this->name));
      }
      
      return $data[0];
    }

    /**
     * Put this segment's contents
     *
     * @access  public
     * @param   &mixed data
     * @param   int permissions default 0666 permissions
     * @return  bool success
     * @throws  io.IOException in case an error occurs
     */
    function put(&$val, $permissions= 0666) {
      $v= array($val);
      $h= shm_attach($this->spot, (strlen(serialize($v)) + 44) * 2, $permissions);
      $ret= shm_put_var($h, $this->name, $v);
      shm_detach($h);
      if (FALSE === $ret) {
        return throw(new IOException('Could not write segment '.$this->name));
      }
      
      return $ret;
    }
    
    /**
     * Remove this segment's contents
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException in case an error occurs
     */
    function remove() {
      $h= shm_attach($this->spot);
      $ret= shm_remove_var($h, $this->name);
      shm_detach($h);
      
      if (FALSE === $ret) {
        return throw(new IOException('Could not remove segment '.$this->name));
      }
      
      return $ret;
    }

  }
?>
