<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('remote.server.message.EascMessage');

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class EascInitMessage extends EascMessage {
  
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function handle(&$listener, $data) {
      $this->setValue($b= TRUE);
    }
  }
?>
