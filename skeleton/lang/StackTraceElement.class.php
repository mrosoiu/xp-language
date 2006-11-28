<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * An element in a stack trace, as returned by Throwable::getStackTrace(). 
   * Each element represents a single stack frame.
   *
   * @see      xp://lang.Throwable#getStackTrace()
   * @purpose  Represent execution stack frame
   */
  class StackTraceElement extends Object {
    var
      $file     = '',
      $class    = '',
      $method   = '',
      $line     = 0,
      $args     = array(),
      $message  = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string file
     * @param   string class
     * @param   string method
     * @param   int line
     * @param   array args
     * @param   string message
     */
    function __construct($file, $class, $method, $line, $args, $message) {
      $this->file     = $file;  
      $this->class    = $class; 
      $this->method   = $method;
      $this->line     = $line;
      $this->args     = $args;
      $this->message  = $message;
    }
    
    /**
     * Create string representation
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $args= array();
      if (isset($this->args)) {
        for ($j= 0, $a= sizeof($this->args); $j < $a; $j++) {
          if (is_array($this->args[$j])) {
            $args[]= 'array['.sizeof($this->args[$j]).']';
          } else if (is_object($this->args[$j])) {
            $args[]= get_class($this->args[$j]).'{}';
          } else if (is_string($this->args[$j])) {
            $display= str_replace('%', '%%', addcslashes(substr($this->args[$j], 0, min(
              (FALSE === $p= strpos($this->args[$j], "\n")) ? 0x40 : $p, 
              0x40
            )), "\0..\17"));
            $args[]= (
              '(0x'.dechex(strlen($this->args[$j])).")'".
              $display.
              "'"
            );
          } else if (is_null($this->args[$j])) {
            $args[]= 'NULL';
          } else if (is_scalar($this->args[$j])) {
            $args[]= (string)$this->args[$j];
          } else if (is_resource($this->args[$j])) {
            $args[]= (string)$this->args[$j]; 
          } else {
            $args[]= '<'.gettype($this->args[$j]).'>';
          }
        }
      }
      return sprintf(
        "  at %s::%s(%s) [line %d of %s] %s\n",
        isset($this->class) ? xp::nameOf($this->class) : '<main>',
        isset($this->method) ? $this->method : '<main>',
        implode(', ', $args),
        $this->line,
        basename(isset($this->file) ? $this->file : __FILE__),
        $this->message
      );
    }

    /**
     * Compares this stacktrace element to another object
     *
     * @access  public
     * @param   &lang.Object cmp
     * @return  bool
     */
    function equals(&$cmp) {
      return is('lang.StackTraceElement', $cmp) && $this->toString() == $cmp->toString();
    }
  }
?>
