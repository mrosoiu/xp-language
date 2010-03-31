<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
  uses('org.codehaus.stomp.frame.Frame');

  $package= 'org.codehaus.stomp.frame';
  class org�codehaus�stomp�frame�LoginFrame extends org�codehaus�stomp�frame�Frame {
    protected
      $user = NULL,
      $pass = NULL;

    public function __construct($user, $pass) {
      $this->user= $user;
      $this->pass= $pass;
    }

    public function command() {
      return 'CONNECT';
    }

    /**
     * Login frame followed by CONNECTED response
     *
     */
    public function requiresImmediateResponse() {
      return TRUE;
    }

    public function getHeaders() {
      return array_merge(array(
        'login'  => $this->user,
        'passcode'  => $this->pass
        ),
        parent::getHeaders()
      );
    }
  }
?>
