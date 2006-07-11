<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('security.crypto.CryptoKey', 'security.OpenSslUtil');

  /**
   * Private key
   *
   * @purpose  Private key
   */
  class PrivateKey extends CryptoKey {

    /**
     * Create private key from its string representation
     *
     * @model   static
     * @access  public
     * @param   string str
     * @param   string passphrase default NULL
     * @return  &security.crypto.PrivateKey
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function &fromString($str, $passphrase= NULL) {
      if (!is_resource($_hdl= openssl_pkey_get_private($str, $passphrase))) {
        return throw(new CryptoException(
          'Could not read private key', OpenSslUtil::getErrors()
        ));
      }
    
      $pk= &new PrivateKey($_hdl);
      return $pk;
    }
    
    /**
     * Signs the data using this private key
     *
     * @access  public
     * @param   string data
     * @return  string
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function sign($data) {
      if (FALSE === openssl_sign($data, $signature, $this->_hdl)) {
        return throw(new CryptoException(
          'Could not sign data', OpenSslUtil::getErrors()
        ));
      }
      
      return $signature;
    }
    
    /**
     * Encrypt data using this public key. Data will be decryptable
     * only with the matching private key.
     *
     * This method can only encrypt short data (= shorter than the key,
     * see the PHP manual). To encrypt larger values, use the seal()
     * method.
     *
     * @see     php://openssl_private_encrypt
     * @access  public
     * @param   string data
     * @return  string
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function encrypt($data) {
      if (FALSE === openssl_private_encrypt($data, $crypted, $this->_hdl)) {
        return throw(new CryptoException(
          'Could not decrypt data', OpenSslUtil::getErrors()
        ));
      }
      
      return $crypted;
    }    
    
    /**
     * Decrypt data using this private key. Only data encrypted with
     * the public key matching this key will be decryptable.
     *
     * @access  public
     * @param   string data
     * @return  string
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function decrypt($data) {
      if (FALSE === openssl_private_decrypt($data, $decrypted, $this->_hdl)) {
        return throw(new CryptoException(
          'Could not decrypt data', OpenSslUtil::getErrors()
        ));
      }
      
      return $decrypted;
    }
    
    /**
     * Export this key into its string representation
     *
     * @access  public
     * @param   string passphrase default NULL
     * @return  string
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function export($passphrase= NULL) {
      if (FALSE === openssl_pkey_export($this->_hdl, $out, $passphrase)) {
        return throw(new CryptoException(
          'Could not export private key', OpenSslUtil::getErrors()
        ));
      }
      
      return $out;
    }
    
    /**
     * Unseal data sealed with the public key matching this key. This method
     * also needs the hash-key created by the seal() method.
     *
     * @access  public
     * @param   string data
     * @param   string key
     * @return  string
     * @throws  security.crypto.CryptoException if the operation fails
     */
    function unseal($data, $key) {
      if (FALSE === openssl_open($data, $unsealed, $key, $this->_hdl)) {
        return throw(new CryptoException(
          'Could not export private key', OpenSslUtil::getErrors()
        ));
      }
      
      return $unseal;
    }
  }

?>
