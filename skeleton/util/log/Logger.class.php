<?php
/* Diese Klasse ist Bestandteil des XP-Frameworks
 *
 * $Id$
 */

  // Logger-Flags, die Definieren, was geloggt wird
  define('LOGGER_FLAG_INFO',    0x0001);
  define('LOGGER_FLAG_WARN',    0x0002);
  define('LOGGER_FLAG_ERROR',   0x0004);
  define('LOGGER_FLAG_DEBUG',   0x0008);
  define('LOGGER_FLAG_ALL',     LOGGER_FLAG_INFO | LOGGER_FLAG_WARN | LOGGER_FLAG_ERROR | LOGGER_FLAG_DEBUG);
  
  /**
   * Kapselt einen Logger
   * �ber die Flgas 
   */
  class Logger extends Object {
    var 
      $_appenders= array(),
      $_indicators= array(
        LOGGER_FLAG_INFO        => '[info]',
        LOGGER_FLAG_WARN        => '[warn]',
        LOGGER_FLAG_ERROR       => '[error]',
        LOGGER_FLAG_DEBUG       => '[debug]'
      ),
      $_flags= LOGGER_FLAG_ALL;
  
    /**
     * Gibt eine Instanz zur�ck
     *
     * @access  public
     * @return  Logger Das Logger-Objekt
     */
    function &getInstance() {
      static $LOG__instance;
  
      if (!isset($LOG__instance)) {
        $LOG__instance= new Logger();
      }
      return $LOG__instance;
    }
    
    /**
     * Setzt die Flags (was geloggt werden soll)
     *
     * @access  public
     * @param   int flags Bitfeld mit den Flags (LOGGER_FLAG_*)
     */
    function setFlags($flags) {
      $this->_flags= $flags;
    }
    
    /**
     * Private Helper-Funktion
     *
     * @access private
     */
    function callAppenders() {
      $args= func_get_args();
      $flag= $args[0];
      if (!($this->_flags & $flag)) return;
      
      $args[0]= $this->_indicators[$flag];
      foreach ($this->_appenders as $appender) {
        call_user_func_array(
          array(&$appender, 'append'),
          $args
        );
      }
    }
    
    /**
     * F�gt einen Appender hinzu
     *
     * @access  public
     * @param   Appender appender Das Appender-Objekt
     */
    function addAppender(&$appender) {
      $this->_appenders[]= &$appender;
    }

    /**
     * H�ngt einen Info-String an
     *
     * @access  public
     * @param   mixed args Beliebige Variablen
     */
    function info() {
      $args= func_get_args();
      array_unshift($args, LOGGER_FLAG_INFO);
      call_user_func_array(
        array(&$this, 'callAppenders'),
        $args
      );
    }

    /**
     * H�ngt einen Info-String an
     *
     * @access  public
     * @param   string format Format-String (siehe sprintf() und Konsorten)
     * @param   mixed args Beliebige Variablen
     */
    function infof() {
      $args= func_get_args();
      $this->callAppenders(
        LOGGER_FLAG_INFO,
        vsprintf($args[0], array_slice($args, 1))
      );
    }

    /**
     * H�ngt einen Warn-String an
     *
     * @access  public
     * @param   mixed args Beliebige Variablen
     */
    function warn() {
      $args= func_get_args();
      array_unshift($args, LOGGER_FLAG_WARN);
      call_user_func_array(
        array(&$this, 'callAppenders'),
        $args
      );
    }

    /**
     * H�ngt einen Warn-String an
     *
     * @access  public
     * @param   string format Format-String (siehe sprintf() und Konsorten)
     * @param   mixed args Beliebige Variablen
     */
    function warnf() {
      $args= func_get_args();
      $this->callAppenders(
        LOGGER_FLAG_WARN,
        vsprintf($args[0], array_slice($args, 1))
      );
    }

    /**
     * H�ngt einen Fehler-String an
     *
     * @access  public
     * @param   mixed args Beliebige Variablen
     */
    function error() {
      $args= func_get_args();
      array_unshift($args, LOGGER_FLAG_ERROR);
      call_user_func_array(
        array(&$this, 'callAppenders'),
        $args
      );
    }

    /**
     * H�ngt einen Fehler-String an
     *
     * @access  public
     * @param   string format Format-String (siehe sprintf() und Konsorten)
     * @param   mixed args Beliebige Variablen
     */
    function errorf() {
      $args= func_get_args();
      $this->callAppenders(
        LOGGER_FLAG_ERROR,
        vsprintf($args[0], array_slice($args, 1))
      );
    }

    /**
     * H�ngt einen Debug-String an
     *
     * @access  public
     * @param   mixed args Beliebige Variablen
     */
    function debug() {
      $args= func_get_args();
      array_unshift($args, LOGGER_FLAG_DEBUG);
      call_user_func_array(
        array(&$this, 'callAppenders'),
        $args
      );
    }
 
     /**
     * H�ngt einen Debug-String an
     *
     * @access  public
     * @param   string format Format-String (siehe sprintf() und Konsorten)
     * @param   mixed args Beliebige Variablen
     */
    function debugf() {
      $args= func_get_args();
      $this->callAppenders(
        LOGGER_FLAG_ERROR,
        vsprintf($args[0], array_slice($args, 1))
      );
    }
   
    /**
     * H�ngt einen Trenner an
     *
     * @access  public
     */
    function mark() {
      $this->callAppenders(
        LOGGER_FLAG_INFO, 
        '---------------------------------------------------------------------'
      );
    }

  }
?>
