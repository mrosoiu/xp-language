<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Chained Exception
   *
   * @purpose  Exception base class
   * @see      http://mindprod.com/jgloss/chainedexceptions.html
   * @see      http://www.jguru.com/faq/view.jsp?EID=1026405  
   */
  class ChainedException extends Exception {
    var
      $cause    = NULL;

    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   &lang.Throwable cause
     */
    function __construct($message, &$cause) {
      parent::__construct($message);
      $this->cause= &$cause;
    }

    /**
     * Set cause
     *
     * @access  public
     * @param   &lang.Throwable cause
     */
    function setCause(&$cause) {
      $this->cause= &$cause;
    }

    /**
     * Get cause
     *
     * @access  public
     * @return  &lang.Throwable
     */
    function &getCause() {
      return $this->cause;
    }
    
    /**
     * Return string representation of this exception
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $s= $this->compoundMessage()."\n";
      $t= sizeof($this->trace);
      for ($i= 0; $i < $t; $i++) {
        $s.= $this->trace[$i]->toString(); 
      }
      if (!$this->cause) return $s;
      
      $loop= &$this->cause;
      while ($loop) {

        // String of cause
        $s.= 'Caused by '.$loop->compoundMessage()."\n";

        // Find common stack trace elements
        for ($ct= $cc= sizeof($loop->trace)- 1, $t= sizeof($this->trace)- 1; $ct > 0, $t > 0; $cc--, $t--) {
          if (!$loop->trace[$cc]->equals($this->trace[$t])) break;
        }

        // Output uncommon elements only and one line how many common elements exist!
        for ($i= 0; $i < $cc; $i++) {
          $s.= $this->cause->trace[$i]->toString(); 
        }
        if ($cc != $ct) $s.= '  ... '.($ct - $cc + 1)." more\n";
        
        $loop= is_a($loop, 'ChainedException') ? $loop->cause : NULL;
      }
      
      return $s;
    }
  }
?>
