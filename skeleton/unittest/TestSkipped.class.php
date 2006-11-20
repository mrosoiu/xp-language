<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Indicates a test was skipped
   *
   * @see      xp://unittest.TestResult
   * @purpose  Result wrapper
   */
  class TestSkipped extends Object {
    var
      $result   = NULL,
      $test     = NULL,
      $elapsed  = 0.0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &unittest.TestCase test
     * @param   &mixed reason
     * @param   float elapsed
     */
    function __construct(&$test, &$reason, $elapsed) {
      $this->test= &$test;
      $this->reason= &$reason;
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
        sprintf(', time= %.3f seconds', $this->elapsed).") {\n  ".
        str_replace("\n", "\n  ", xp::stringOf($this->reason))."\n".
        ' }'
      );
    }
  }
?>
