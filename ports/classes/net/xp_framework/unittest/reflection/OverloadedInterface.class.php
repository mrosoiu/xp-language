<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Interface with overloaded methods
   *
   * @see      xp://lang.reflect.Proxy
   * @purpose  Test interface
   */
  class OverloadedInterface extends Interface {
    
    /**
     * Overloaded method.
     *
     * @access  public
     */
    #[@overloaded(signatures= array(
    #  array('string'),
    #  array('string', 'string'),
    #  array('string', 'string')
    #))]
    function overloaded() { }
  }
?>
