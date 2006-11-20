<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Dummy class for testing purposes only.
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class SoapMappingTestTarget extends Object {
    var
      $string     = '',
      $integer    = 0;  

    /**
     * Constructor.
     *
     * @access  public
     * @param   string string default ''
     * @param   integer integer default 0
     */
    function __construct($string= '', $integer= 0) {
      $this->string= $string;
      $this->integer= $integer;
    }

    /**
     * Set String
     *
     * @access  public
     * @param   string string
     */
    function setString($string) {
      $this->string= $string;
    }

    /**
     * Get String
     *
     * @access  public
     * @return  string
     */
    function getString() {
      return $this->string;
    }

    /**
     * Set Integer
     *
     * @access  public
     * @param   int integer
     */
    function setInteger($integer) {
      $this->integer= $integer;
    }

    /**
     * Get Integer
     *
     * @access  public
     * @return  int
     */
    function getInteger() {
      return $this->integer;
    }
  }
?>
