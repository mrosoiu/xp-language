<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.Date');

  /**
   * Test Class
   *
   * @purpose  Fixture for MethodInvocationTest
   */
  class TestClass extends Object {
  
    /**
     * Method with one argument
     *
     * @access  public
     * @param   &util.Date date
     */
    function setDate(&$date) {
      $this->date= &$date;
    }
    
    /**
     * A method without arguments
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return $this->getClassName();
    }
  
  }
?>
