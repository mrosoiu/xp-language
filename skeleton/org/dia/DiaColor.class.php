<?php
/*
 *
 * $Id:$
 */

  uses(
    'org.dia.DiaElement'
  );

  /**
   * Represents a 'dia:color' node
   */
  class DiaColor extends DiaElement {

    var
      $node_name= 'dia:color';

    /**
     * Return XML representation of DiaComposite
     *
     * @access  public
     * @return  &xml.Node
     */
    function &getNode() {
      $node= &parent::getNode();
      if (isset($this->value)) 
        $node->setAttribute('val', $this->value);
      return $node;
    }

  }
?>
