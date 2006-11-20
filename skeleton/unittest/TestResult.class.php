<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestFailure', 
    'unittest.TestSuccess', 
    'unittest.TestSkipped'
  );

  /**
   * Test result
   *
   * @see      xp://unittest.TestSuite
   * @purpose  Wrapper class
   */
  class TestResult extends Object {
    var
      $succeeded    = array(),
      $failed       = array(),
      $skipped      = array();
      
    /**
     * Mark a test as succeeded
     *
     * @access  public
     * @param   &unittest.TestCase test
     * @param   float elapsed
     */
    function setSucceeded(&$test, $elapsed) {
      $this->succeeded[$test->hashCode()]= &new TestSuccess($test, $elapsed);
    }
    
    /**
     * Mark a test as failed
     *
     * @access  public
     * @param   &unittest.TestCase test
     * @param   mixed reason
     * @param   float elapsed
     */
    function setFailed(&$test, $reason, $elapsed) {
      $this->failed[$test->hashCode()]= &new TestFailure($test, $reason, $elapsed);
    }

    /**
     * Mark a test as been skipped
     *
     * @access  public
     * @param   &unittest.TestCase test
     * @param   mixed reason
     * @param   float elapsed
     */
    function setSkipped(&$test, $reason, $elapsed) {
      $this->skipped[$test->hashCode()]= &new TestSkipped($test, $reason, $elapsed);
    }

    /**
     * Get number of succeeded tests
     *
     * @access  public
     * @return  int
     */
    function successCount() {
      return sizeof($this->succeeded);
    }
    
    /**
     * Get number of failed tests
     *
     * @access  public
     * @return  int
     */
    function failureCount() {
      return sizeof($this->failed);
    }

    /**
     * Get number of skipped tests
     *
     * @access  public
     * @return  int
     */
    function skipCount() {
      return sizeof($this->skipped);
    }

    /**
     * Get number of run tests (excluding skipped)
     *
     * @access  public
     * @return  int
     */
    function runCount() {
      return sizeof($this->succeeded) + sizeof($this->failed);
    }

    /**
     * Get number of total tests
     *
     * @access  public
     * @return  int
     */
    function count() {
      return sizeof($this->succeeded)+ sizeof($this->failed)+ sizeof($this->skipped);
    }
    
    /**
     * Create a nice string representation
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $div= str_repeat('=', 72);
      $str= sprintf(
        "Results for test suite run at %s\n".
        "%d tests, %d succeeded, %d failed, %d skipped\n",
        date('r'),
        $this->count(),
        $this->successCount(),
        $this->failureCount(),
        $this->skipCount()
      );
      
      // Details
      if (!empty($this->succeeded)) {
        $str.= "\n- Succeeded tests details:\n";
        foreach (array_keys($this->succeeded) as $key) {
          $str.= '  * '.$this->succeeded[$key]->toString()."\n";
        }
      }
      if (!empty($this->skipped)) {
        $str.= "\n- Skipped tests details:\n";
        foreach (array_keys($this->skipped) as $key) {
          $str.= '  * '.$this->skipped[$key]->toString()."\n";
        }
      }
      if (!empty($this->failed)) {
        $str.= "\n- Failed tests details:\n";
        foreach (array_keys($this->failed) as $key) {
          $str.= '  * '.$this->failed[$key]->toString()."\n";
        }
      }
      return $str.$div."\n";
    }
  }
?>
