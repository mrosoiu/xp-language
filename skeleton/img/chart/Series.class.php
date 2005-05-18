<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * A series of data
   *
   * @see      xp://img.chart.Chart
   * @purpose  Value object
   */
  class Series extends Object {
    var
      $name   = '',
      $values = array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     * @param   float[] values default array()
     */
    function __construct($name, $values= array()) {
      $this->name= $name;
      $this->values= $values;
    }
    
    /**
     * Adds a value to this series
     *
     * @access  public
     * @param   float f
     */
    function add($f) {
      $this->values[]= $f;
    }
  }
?>
