<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.SocketException');

  /**
   * Indicate an error occured during authentication
   *
   * @see      xp://peer.SocketException
   * @purpose  Exception
   */
  class AuthenticationException extends SocketException {
    var
      $user = '',
      $pass = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   string user
     * @param   string pass default ''
     */
    function __construct($message, $user, $pass= '') {
      parent::__construct($message);
      $this->user= $user;
      $this->pass= $pass;
    }

    /**
     * Get User
     *
     * @access  public
     * @return  string
     */
    function getUser() {
      return $this->user;
    }

    /**
     * Get Pass
     *
     * @access  public
     * @return  string
     */
    function getPass() {
      return $this->pass;
    }
  }
?>
