<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * Permission base class
   *
   * @purpose  A single permission
   * @see      http://java.sun.com/j2se/1.4.1/docs/guide/security/permissions.html
   * @see      xp://security.Policy
   */
  class Permission extends Object {
    var
      $name     = '',
      $actions  = array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     */
    function __construct($name, $actions) {
      $this->name= $name;
      $this->actions= $actions;
      
    }
    
    /**
     * Get this permission's name
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }
    
    /**
     * Get this permission's actions
     *
     * @access  public
     * @return  string[]
     */
    function getActions() {
      return $this->actions;
    }
    
    /**
     * Create a string representation
     * 
     * Examples:
     * <pre>
     * permission io.FilePermission "/foo/bar", "read";
     * permission io.FilePermission "/baz/example", "read,write";
     * </pre>
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        'permission %s: "%s", "%s";',
        $this->getClassName(),
        $this->name,
        implode(',', $this->actions)
      );
    }
  }
?>
