<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Indicates prerequisites have not been met
   *
   * @purpose  Exception
   */
  class PrerequisitesNotMetError extends XPException {
    const
      PREREQUISITE_LIBRARYMISSING = 'library.missing',
      PREREQUISITE_INITFAILED = 'initialization.failed';

    public
      $cause           = NULL,
      $prerequisites   = array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   &lang.Exception cause 
     * @param   array prerequisites default array()
     * @param   string code
     */
    public function __construct($message, &$cause, $prerequisites= array()) {
      $this->cause= $cause;
      $this->prerequisites= $prerequisites;
      parent::__construct($message);
    }
    
    /**
     * Get Trace
     *
     * @access  public
     * @return  string
     */
    public function getStackTrace() {
      return $this->cause ? $this->cause->getStackTrace() : self::getStackTrace();
    }
  }
?>
