<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Markup processor
   *
   * @see      xp://net.xp_framework.util.markup.MarkupBuilder
   * @purpose  Base class
   */
  class MarkupProcessor extends Object {
    
    /**
     * Initializes the processor. Returns an empty string in this default
     * implementation.
     *
     * @access  public
     * @return  string
     */
    function initialize() {
      return '';
    }
    
    /**
     * Process a token. Returns an empty string in this default
     * implementation.
     *
     * @access  public
     * @param   string token
     * @return  string
     */
    function process($token) {
      return '';
    }
    
    /**
     * Finalizes the processor. Returns an empty string in this default
     * implementation.
     *
     * @access  public
     * @return  string
     */
    function finalize() {
      return '';
    }
  }
?>
