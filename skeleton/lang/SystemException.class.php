<?php
/* Diese Klasse ist Teil des XP-Frameworks
 * 
 * $Id$
 */
 
  /**
   * Kapselt die SystemException, die au�er der Fehlermeldung
   * noch einen Fehler-Code definiert
   *
   * @see Exception
   */
  class SystemException extends Exception {
    var $code= 0;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string message Die Fehlermeldung
     * @param   int code Der Fehlercode
     */
    function __construct($message, $code) {
      $this->code= $code;
      parent::__construct($message);
    }
  }
?>
