<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Indicates a test was successful
   *
   * @see      xp://util.profiling.unittest.TestResult
   * @purpose  Result wrapper
   */
  class TestSuccess extends Object {
    public
      $test     = NULL,
      $elapsed  = 0.0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &util.profiling.unittest.TestCase test
     * @param   &mixed result
     * @param   float elapsed
     */
    public function __construct(&$test, $elapsed) {
      $this->test= &$test;
      $this->elapsed= $elapsed;
    }
    
    /**
     * Return a string representation of this class
     *
     * @access  public
     * @return  string
     */
    public function toString() {
      return (
        $this->getClassName().
        '(test= '.$this->test->getClassName().'::'.$this->test->getName().
        sprintf(', time= %.3f seconds', $this->elapsed).
        ')'
      );
    }
  }
?>
