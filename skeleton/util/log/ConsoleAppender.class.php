<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.log.LogAppender');

  /**
   * LogAppender which appends data to console. The data goes to STDERR.
   *
   * Note: STDERR will not be defined in a web server's environment,
   * so using this class will have no effect - have a look at the
   * SyslogAppender or FileAppender classes instead.
   *
   * @see      xp://util.log.LogAppender
   * @purpose  Appender
   */  
  class ConsoleAppender extends LogAppender {
    
    /**
     * Appends log data to STDERR
     *
     * @access public
     * @param  mixed args variables
     */
    function append() {
      foreach (func_get_args() as $arg) {
        fwrite(STDERR, $this->varSource($arg).' ');
      }
      fwrite(STDERR, "\n");
    }
  }
?>
