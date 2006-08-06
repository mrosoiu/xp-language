<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('net.xp_framework.tools.vm.VNode');

  /**
   * Switch
   *
   * @see   xp://net.xp_framework.tools.vm.nodes.VNode
   */ 
  class SwitchNode extends VNode {
    var
      $condition,
      $cases;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   mixed condition
     * @param   mixed cases
     */
    function __construct($condition, $cases) {
      $this->condition= $condition;
      $this->cases= $cases;
    }  
  }
?>
