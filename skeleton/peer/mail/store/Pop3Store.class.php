<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses('peer.mail.store.CclientStore');
  
  /**
   * Mail store
   *
   * @see
   * @purpose  Wrap
   */
  class Pop3Store extends CclientStore {

    /**
     * Protected method to check whether this DSN is supported
     *
     * Supported notations:
     * <pre>
     * - pop3://localhost
     * - pop3://user:pass@localhost
     * - pop3://user@localhost:111
     * </pre>
     *
     * @access  protected
     * @param   array u
     * @param   &array attr
     * @param   &int port
     * @return  bool
     * @throws  IllegalArgumentException
     */
    function _supports($u, &$attr) {
      switch (strtolower($u['scheme'])) {
        case 'pop3': 
          $attr['proto']= 'pop3'; 
          $attr['port']= 110; 
          break;
          
        default: 
          return parent::_supports($u, $attr);
      }
      
      return TRUE;   
    }
  
  }
?>
