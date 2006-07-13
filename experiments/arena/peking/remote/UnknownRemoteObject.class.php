<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Unknown remote object
   *
   * @ext      overload
   * @see      php://__PHP_Incomplete_Class
   * @test     xp://net.xp_framework.unittest.remote.UnknownRemoteObjectTest
   * @purpose  purpose
   */
  class UnknownRemoteObject extends Object {
    var
      $__name     = '',
      $__members  = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     * @param   array<string, mixed> members default array()
     */
    function __construct($name, $members= array()) {
      $this->__name= $name;
      $this->__members= $members;
    }

    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $s= $this->getClassName().'@('.$this->__name.") {\n";
      foreach (array_keys($this->__members) as $member) {

        // Create a temporary copy of the member because of ext/overload
        // weirdnesses with references. Omitting the $m= $this->__members...
        // assignment will lead to a fatal error:
        //
        //   "Only variables can be passed by reference"...
        //
        // ...and creating a reference (via $m= &$this->__members...) will
        // lead to this fatal error:
        //
        //   "Cannot create references to/from string offsets nor overloaded objects"
        //
        // This is a bug in ext/overload.
        $s.= sprintf("  [%-20s] %s\n", $member, xp::stringOf($m= $this->__members[$member]));
      }
      return $s.'}';
    }

    /**
     * Member set interceptor
     *
     * @access  public
     * @param   string name
     * @param   mixed value
     * @return  bool TRUE on success
     */
    function __set($name, $value) {
      throw(new IllegalAccessException('Access to undefined member "'.$name.'"'));
      return FALSE;
    }
    
    /**
     * Member get interceptor
     *
     * @access  public
     * @param   string name
     * @param   &mixed value
     * @return  bool TRUE on success
     */
    function __get($name, &$value) {
      throw(new IllegalAccessException('Access to undefined member "'.$name.'"'));
      return FALSE;
    }
  
    /**
     * Method call interceptor
     *
     * @access  public
     * @param   string name
     * @param   mixed[] args
     * @param   &mixed return
     * @return  bool TRUE on success
     * @throws  lang.IllegalAccessException
     */
    function __call($name, $args, &$return) {
      throw(new IllegalAccessException('Cannot call method "'.$name.'" on an unknown remote object'));
      return FALSE;
    }

  } overload('UnknownRemoteObject');
?>
