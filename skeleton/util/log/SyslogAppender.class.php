<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.log.LogAppender');

  /**
   * LogAppender which appends data to syslog
   *
   * @see      xp://util.log.LogAppender
   * @see      php://syslog
   * @purpose  Appender
   */  
  class SyslogAppender extends LogAppender {

    /**
     * Destructor.
     *
     * @access  protected
     */
    function __destruct() {
      $this->finalize();
    }
        
    /**
     * Constructor
     *
     * @access  public
     * @param   string identifier default NULL if omitted, defaults to script's filename
     * @param   int facility default LOG_USER
     * @see     php://openlog for valid facility values
     */
    function __construct($identifier= NULL, $facility= LOG_USER) {
      openlog(
        $identifier ? $identifier : basename($_SERVER['PHP_SELF']), 
        LOG_ODELAY | LOG_PID, 
        $facility
      );
    }
    
    /**
     * Appends log data to the syslog
     *
     * @access  public
     * @param   mixed args variables
     */
    function append() {
      $buf= '';
      foreach (func_get_args() as $arg) {
        $buf.= $this->varSource($arg).' ';
      }
      syslog($buf);
    }
    
    /**
     * Finalize this appender - is called when the logger shuts down
     * at the end of the request.
     *
     * @access  public 
     */
    function finalize() {
      closelog();
    }
  }
?>
