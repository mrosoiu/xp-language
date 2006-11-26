<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Comparator interface 
   *
   * @see      xp://util.Hashmap#usort
   * @see      php://usort
   * @purpose  A comparison function
   */
  class Comparator extends Interface {
  
    /**
     * Compares its two arguments for order. Returns a negative integer, 
     * zero, or a positive integer as the first argument is less than, 
     * equal to, or greater than the second.
     *
     * @model   static
     * @access  public
     * @param   &mixed a
     * @param   &mixed b
     * @return  int
     */
    function compare(&$a, &$b) { }
  }
?>
