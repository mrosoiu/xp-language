<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * OpenSSL utility functions
   *
   * @ext      openssl
   * @purpose  Utiltiy functions
   */
  class OpenSslUtil extends Object {
  
    /**
     * Retrieve errors
     *
     * @model   static
     * @access  public
     * @return  string[] error
     */
    function getErrors() {
      $e= array();
      while ($msg= openssl_error_string()) {
        $e[]= $msg;
      }
      return $e;
    }
    
    /**
     * Get OpenSSL configuration file environment value
     *
     * @access  public
     * @return  string
     */
    function getConfiguration() {
      return getenv('OPENSSL_CONF');
    }
  }
?>
