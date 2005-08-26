<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Represents a method's argument
   *
   * @see      xp://lang.reflect.Routine#getArguments
   * @purpose  Reflection
   */
  class Argument extends Object {
    var
      $name     = '',
      $type     = '',
      $optional = FALSE,
      $default  = NULL;

    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     * @param   string type default 'mixed'
     * @param   bool optional default FALSE
     * @param   string default default NULL
     */    
    function __construct($name, $type= 'mixed', $optional= FALSE, $default= NULL) {
      $this->name= $name;
      $this->type= $type;
      $this->optional= $optional;
      $this->default= $default;
    }

    /**
     * Get Name
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }

    /**
     * Get Type
     *
     * @access  public
     * @return  string
     */
    function getType() {
      return ltrim($this->type, '&');
    }

    /**
     * Returns whether this argument is passed by reference
     *
     * @access  public
     * @return  string
     */
    function isPassedByReference() {
      return '&' == $this->type{0};
    }

    /**
     * Retrieve whether this argument is optional
     *
     * @access  public
     * @return  bool
     */
    function isOptional() {
      return $this->optional;
    }

    /**
     * Get default value as a string ("NULL" for NULL). Returns FALSE if
     * no default value is set.
     *
     * @access  public
     * @return  string
     */
    function getDefault() {
      return $this->optional ? $this->default : FALSE;
    }
  }
?>
