<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.File', 'security.crypto.UnixCrypt');

  /**
   * Autenticates users against a .htpasswd file
   *
   * @purpose  Authenticator
   */
  class HtpasswdAuthenticator extends Object {
    var
      $_modified = 0,
      $_file     = NULL,
      $_hash     = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   &io.File file
     */
    function __construct(&$file) {
      $this->_file= &$file;
    }
    
    /**
     * Lookup crypted password. Returns the crypt as a string on
     * success and NULL on failure.
     *
     * @access  private
     * @param   string username
     * @return  string
     * @throws  security.auth.AuthenticatorException
     */
    function lookup($user) {
      if ($this->_file->lastModified() != $this->_modified) {
        $hash= array();
        try(); {
          $this->_file->open(FILE_MODE_READ);
          while ($line= $this->_file->readLine()) {
            list($username, $crypt)= explode(':', $line, 2);
            $hash[$username]= $crypt;
          }
          $this->_file->close();
        } if (catch('IOException', $e)) {
          return throw(new AuthenticatorException(
            'Failed rehashing from '.$this->_file->getURI(), 
            $e
          ));
        }
        $this->_modified= $this->_file->lastModified();
        $this->_hash= $hash;
      }
      return isset($this->_hash[$user]) ? $this->_hash[$user] : NULL;
    }
  
    /**
     * Authenticate a user
     *
     * @access  public
     * @param   string user
     * @param   string pass
     * @return  bool
     * @throws  security.auth.AuthenticatorException
     */
    function authenticate($user, $pass) {
      if (!($crypt= $this->lookup($user))) return FALSE;
      return UnixCrypt::matches($crypt, $pass);
    }
      
  } implements(__FILE__, 'security.auth.Authenticator');
?>
