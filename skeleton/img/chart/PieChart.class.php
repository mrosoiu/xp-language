<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('img.chart.Chart');

  /**
   * Pie chart
   *
   * @see      xp://img.chart.Chart
   * @purpose  Chart
   */
  class PieChart extends Chart {

    var
      $valinset= array();
    
    /**
     * Helper method which returns the sum from all values
     *
     * @access  public
     * @return  float
     */
    function sum() {
      $sum= 0;
      for ($i= 0, $s= sizeof($this->series[0]->values); $i < $s; $i++) {
        $sum+= $this->series[0]->values[$i];
      }
      return $sum;
    }
    
    /**
     * Sets inset for the specified item
     *
     * @access public
     * @param int item The item index
     */
    function setValueInset($item, $inset= 10) {
      $this->valinset[$item]= $inset;
    }
    
    /**
     * Returns the inset for the specified item
     *
     * @access public
     * @param int item The item index
     * @return int
     */
    function getValueInset($item) {
      return isset($this->valinset[$item]) ? $this->valinset[$item] : 0;
    }
  }
?>
