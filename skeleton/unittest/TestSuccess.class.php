<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Indicates a test was successful
   *
   * @see      xp://unittest.TestResult
   * @purpose  Result wrapper
   */
  class TestSuccess extends Object {
    var
      $test     = NULL,
      $elapsed  = 0.0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &unittest.TestCase test
     * @param   &mixed result
     * @param   float elapsed
     */
    function __construct(&$test, $elapsed) {
      $this->test= &$test;
      $this->elapsed= $elapsed;
    }
    
    /**
     * Return a string representation of this class
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return (
        $this->getClassName().
        '(test= '.$this->test->getClassName().'::'.$this->test->getName().
        sprintf(', time= %.3f seconds', $this->elapsed).
        ')'
      );
    }
  }
?>
