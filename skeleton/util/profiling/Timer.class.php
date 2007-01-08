<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * The Timer class provides a simple timer
   *
   * <code>
   *   $p= new Timer();
   *   $p->start();
   *   // ... code you want profiled
   *   $p->stop();
   *   var_dump($p->elapsedTime());
   * </code>
   *
   * @test     xp://net.xp_framework.unittest.util.TimerTest
   * @purpose  Provide a simple profiling timer
   */
  class Timer extends Object {
    public
      $start = 0.0,
      $stop  = 0.0;
      
    /**
     * Start the timer
     *
     */
    public function start() {
      $this->start= microtime(TRUE);
    }
    
    /**
     * Stop the timer
     *
     */
    public function stop() {
      $this->stop= microtime(TRUE);
    }
    
    /**
     * Retrieve elapsed time
     *
     * @return  float seconds elapsed
     */
    public function elapsedTime() {
      return $this->stop - $this->start;
    }
  }
?>
