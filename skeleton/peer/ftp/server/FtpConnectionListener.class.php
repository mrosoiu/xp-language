<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'peer.server.ConnectionListener', 
    'peer.ftp.server.FtpSession',
    'peer.SocketException'
  );
  
  define('DATA_PASSIVE',    0x0001);
  define('DATA_ACTIVE',     0x0002);
  define('TYPE_ASCII',      'A');
  define('TYPE_BINARY',     'I');
  define('STRU_FILE',       'F');
  define('STRU_RECORD',     'R');
  define('STRU_PAGE',       'P');
  define('MODE_STREAM',     'S');
  define('MODE_BLOCK',      'B');
  define('MODE_COMPRESSED', 'C');
  
  /**
   * Implement FTP server functionality
   *
   * @see      http://ipswitch.com/Support/WS_FTP-Server/guide/v4/A_FTPref4.html 
   * @see      xp://peer.server.ConnectionListener
   * @purpose  Connection listener
   */
  class FtpConnectionListener extends ConnectionListener {
    var
      $sessions         = array(),
      $cat              = NULL,
      $authenticator    = NULL,
      $storage          = NULL,
      $datasock         = array(),
      $interceptors     = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   &peer.ftp.server.Storage storage
     * @param   &peer.ftp.server.Authenticator authenticator
     */
    function __construct(&$storage, &$authenticator) {
      $this->storage= &$storage;
      $this->authenticator= &$authenticator;
    }

    /**
     * Set a trace for debugging
     *
     * @access  public
     * @param   &util.log.LogCategory cat
     */
    function setTrace(&$cat) { 
      $this->cat= &$cat;
    }
    
    /**
     * Check all interceptors
     *
     * @access private
     * @param peer.server.ConnectionEvent event The connection event
     * @param perr.server.ftp.server.StorageEntry entry The storage entry
     * @param string params The parameter string from request
     * @param string method Interceptor method to invoke
     * @return bool
     */
    function checkInterceptors(&$event, &$entry, $method) {
      if (!$this->interceptors) return TRUE;
    
      // Check each interceptors an it's conditions
      foreach ($this->interceptors as $intercept) {
        foreach ($intercept[0] as $condition) {
          if (!$condition->check($this->sessions[$event->stream->hashCode()], $entry)) {
            return TRUE;
          }
        }
        
        // Invoke interceptor method
        try(); {
          $intercept[1]->{$method}(
            $this->sessions[$event->stream->hashCode()],
            $entry
          );
        } if (catch('Exception', $e)) {
          $this->answer($event->stream, 550, 'Intercepted: '.$e->getMessage());
          return FALSE;
        }
      }
      return TRUE;
    }
    
    /**
     * Open the datasocket
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @return  &peer.BSDSocket
     */
    function &openDatasock(&$event) {
      if (is('ServerSocket', $this->datasock[$event->stream->hashCode()])) {

        // Open socket in passive mode
        $this->cat && $this->cat->debug('+++ Opening passive connection');
        try(); {
          $socket= &$this->datasock[$event->stream->hashCode()]->accept();
        } if (catch('SocketException', $e)) {
          $this->answer($event->stream, 425, 'Cannot open passive connection '.$e->getMessage());
          return FALSE;        
        }
      } else {
      
        // Open socket in active mode
        $this->cat && $this->cat->debug('+++ Opening active connection');
        with ($socket= &$this->datasock[$event->stream->hashCode()]); {
          try(); {
            $socket->connect();
          } if (catch('SocketException', $e)) {
            $this->answer($event->stream, 425, 'Cannot open active connection '.$e->getMessage());
            return FALSE;        
          }
        }
      }
      $this->cat && $this->cat->debug($socket);
      return $socket;
    }

    /**
     * Returns end of line identifier depending on the given type
     *
     * @access  protected
     * @param   char type
     * @return  string
     */
    function eol($type) {
      return (TYPE_ASCII == $type) ? "\r\n" : "\n";
    }
    
    /**
     * Write an answer message to the socket
     *
     * @access  protected
     * @param   &peer.Socket sock
     * @param   int code
     * @param   string text
     * @param   array lines default NULL lines of a multiline response
     * @return  int number of bytes written
     * @throws  io.IOException
     */
    function answer(&$sock, $code, $text, $lines= NULL) {
      if (is_array($lines)) {
        $answer= $code.'-'.$text.":\r\n  ".implode("\n  ", $lines)."\r\n".$code." End\r\n";
      } else {
        $answer= sprintf("%d %s\r\n", $code, $text);
      }
      $this->cat && $this->cat->debug('<<< ', addcslashes($answer, "\0..\17"));
      return $sock->write($answer);
    }
    
    /**
     * Callback for the "USER" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onUser(&$event, $params) {
      $this->sessions[$event->stream->hashCode()]->setUsername($params);
      $this->sessions[$event->stream->hashCode()]->setAuthenticated(FALSE);
      $this->answer($event->stream, 331, 'Password required for '.$params);
    }
    
    /**
     * Callback for the "PASS" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onPass(&$event, $params) {
      with ($user= $this->sessions[$event->stream->hashCode()]->getUsername()); {
        try(); {
          $r= $this->authenticator->authenticate($user, $params);
        } if (catch('AuthenticatorException', $e)) {
          $this->answer($event->stream, 550, $e->getMessage());
          return;
        }

        // Did the authentication succeed?
        if (!$r) {
          $this->answer($event->stream, 530, 'Authentication failed for '.$user);
          return;
        }
        $this->answer($event->stream, 230, 'User '.$user.' logged in');
        $this->sessions[$event->stream->hashCode()]->setAuthenticated(TRUE);
      }
    }
    
    /**
     * REIN: This command terminates a USER, flushing all I/O and 
     * account information, except to allow any transfer in progress 
     * to be completed. A USER command may be expected to follow.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onRein(&$event, $params) {
      delete($this->datasock[$event->stream->hashCode()]);
      $this->sessions[$event->stream->hashCode()]->setAuthenticated(FALSE);
    }
        
    /**
     * Callback for the "PWD" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onPwd(&$event, $params) {
      $this->answer($event->stream, 257, '"'.$this->storage->getBase($event->stream->hashCode()).'" is current directory');
    }

    /**
     * CWD: This command allows the user to work with a different 
     * directory or dataset without altering his login or account 
     * information.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onCwd(&$event, $params) {
      try(); {
        $pwd= $this->storage->setBase($event->stream->hashCode(), $params);
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 450, $e->getMessage());
        return;
      }
      $this->answer($event->stream, 250, '"'.$pwd.'" is new working directory');
    }

    /**
     * Change to the parent directory
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onCdup(&$event, $params) {
      try(); {
        $pwd= $this->storage->setBase(
          $event->stream->hashCode(),
          dirname($this->storage->getBase($event->stream->hashCode()))
        );
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $e->getMessage());
        return;
      }
      $this->answer($event->stream, 250, 'CDUP command successful');
    }

    /**
     * FEAT: This command causes the FTP server to list all new FTP 
     * features that the server supports beyond those described in 
     * RFC 959.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onFeat(&$event, $params) {
      $this->answer($event->stream, 211, 'Features', array('MDTM', 'SIZE'));
    }

    /**
     * HELP: This command causes the server to send a list of supported 
     * commands and other helpful information.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onHelp(&$event, $params) {
      $methods= array();
      $i= 0;
      foreach (get_class_methods($this) as $name) {
        if (0 != strncmp('on', $name, 2) || strlen($name) > 6) continue;

        if ($i++ % 8 == 0) $methods[++$offset]= '';
        $methods[$offset].= str_pad(strtoupper(substr($name, 2)), 8);
      }
      $this->answer($event->stream, 214, 'The following commands are recognized', $methods);
    }
    
    /**
     * SITE: This allows you to enter a command that is specific to the 
     * current FTP site.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onSite(&$event, $params) {
      $method= 'onSite'.strtolower(strtok($params, ' '));

      // Check if method is implemented and answer with code 550 in case
      // it isn't.
      if (!method_exists($this, $method)) {
        $this->answer($event->stream, 500, $command.' not understood');
        return;
      }

      $this->{$method}($event, substr($params, strlen($method) - 6));
    }
    
    /**
     * SITE HELP
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onSiteHelp(&$event, $params) {
      return $this->onHelp($event, $params);
    }

    /**
     * SITE CHMOD
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onSiteChmod(&$event, $params) {
      list($permissions, $uri)= explode(' ', trim($params), 2);
      $this->cat->warn($permissions);
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $uri))) {
        $this->answer($event->stream, 550, $uri.': No such file or directory');
        return;
      }
      
      $this->cat->debug($entry);
      
      $entry->setPermissions($permissions);
      $this->answer($event->stream, 200, 'SITE CHMOD command successful');
    }
   
    /**
     * Callback for the "SYST" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onSyst(&$event, $params) {
      $this->answer($event->stream, 215, 'UNIX Type: L8');
    }

    /**
     * NOOP:  This command does not affect any parameters or previously 
     * entered commands. It specifies no action other than that the 
     * server send an OK reply.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */    
    function onNoop(&$event, $params) {
      $this->answer($event->stream, 200, 'OK');
    }

    /**
     * Helper method
     *
     * @access  protected
     * @param   int bits
     * @return  string
     */
    function _rwx($bits) {
      return (
        (($bits & 4) ? 'r' : '-').
        (($bits & 2) ? 'w' : '-').
        (($bits & 1) ? 'x' : '-')
      );
    }
    
    /**
     * Create a string representation from integer permissions
     *
     * @access  protected
     * @param   int permissions
     * @return  string
     */
    function permissionString($permissions) {
      return (
        ($permissions & 0x4000 ? 'd' : '-').
        $this->_rwx(($permissions >> 6) & 7).
        $this->_rwx(($permissions >> 3) & 7).
        $this->_rwx(($permissions) & 7)
      );
    }
    
    /**
     * LIST: This command causes a list of file names and file details 
     * to be sent from the FTP site to the client.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onList(&$event, $params) {
      if (!$socket= &$this->openDatasock($event)) return;
            
      // Remove all -options
      if (($parts= sscanf($params, '-%s %s')) && $parts[0]) {
        $this->cat && $this->cat->debug('+++ Removed options:', $parts[0]);
        $params= $parts[1];
      }
      
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        $socket->close();
        delete($socket);
        $this->cat && $this->cat->debug($socket, $this->datasock[$event->stream->hashCode()]);
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRead')) {
        $socket->close();
        return;
      }

      $this->answer($event->stream, 150, sprintf(
        'Opening %s mode data connection for filelist',
        $this->sessions[$event->stream->hashCode()]->typeName()
      ));
      
      // If a collection was specified, list its elements, otherwise,
      // list the single element
      if (is('StorageCollection', $entry)) {
        $elements= $entry->elements();
      } else {
        $elements= array($entry);
      }
      
      for ($i= 0, $s= sizeof($elements); $i < $s; $i++) {
        $buf= sprintf(
          '%s  %2d %s  %s  %8d %s %s',
          $this->permissionString($elements[$i]->getPermissions()),
          $elements[$i]->numLinks(),
          $elements[$i]->getOwner(),
          $elements[$i]->getGroup(),
          $elements[$i]->getSize(),
          date('M d H:i', $elements[$i]->getModifiedStamp()),
          $elements[$i]->getName()
        );
        $this->cat && $this->cat->debug('    ', $buf);
        $socket->write($buf.$this->eol($this->sessions[$event->stream->hashCode()]->getType()));
      }
      $socket->close();
      $this->answer($event->stream, 226, 'Transfer complete');
    }

    /**
     * NLST: This command causes a list of file names (with no other 
     * information) to be sent from the FTP site to the client.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onNlst(&$event, $params) {
      if (!$socket= &$this->openDatasock($event)) return;
      
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        $socket->close();
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRead')) {
        $socket->close();
        return;
      }

      $this->answer($event->stream, 150, sprintf(
        'Opening %s mode data connection for filelist',
        $this->sessions[$event->stream->hashCode()]->typeName()
      ));
      
      // If a collection was specified, list its elements, otherwise,
      // list the single element
      if (is('StorageCollection', $entry)) {
        $elements= $entry->elements();
      } else {
        $elements= array($entry);
      }
      
      for ($i= 0, $s= sizeof($elements); $i < $s; $i++) {
        $socket->write(
          $elements[$i]->getName().
          $this->eol($this->sessions[$event->stream->hashCode()]->getType())
        );
      }
      $socket->close();
      $this->answer($event->stream, 226, 'Transfer complete');
    }

    /**
     * MDTM: This command can be used to determine when a file in the 
     * server was last modified.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onMdtm(&$event, $params) {
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRead')) return;

      $this->answer($event->stream, 213, date('YmdHis', $entry->getModifiedStamp()));
    }

    /**
     * SIZE:  This command is used to obtain the transfer size of a file 
     * from the server: that is, the exact number of octets (8 bit bytes) 
     * which would be transmitted over the data connection should that 
     * file be transmitted. 
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onSize(&$event, $params) {
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRead')) return;

      $this->answer($event->stream, 213, $entry->getSize());
    }

    /**
     * MKD:  This command causes the directory specified in pathname to 
     * be created as a directory (if pathname is absolute) or as a 
     * subdirectory of the current working directory (if pathname is 
     * relative).
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onMkd(&$event, $params) {
      if ($this->storage->lookup($event->stream->hashCode(), $params)) {
        $this->answer($event->stream, 550, $params.': already exists');
        return;
      }
      
      // Invoke interceptor
      $entry= &$this->storage->createEntry($event->stream->hashCode(), $params, ST_COLLECTION);
      if (!$this->checkInterceptors($event, $entry, 'onCreate')) return;

      // Create the element
      try(); {
        $this->storage->create($event->stream->hashCode(), $params, ST_COLLECTION);
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $params.': '.$e->getMessage());
        return;
      }
      $this->answer($event->stream, 257, $params.': successfully created');
    }

    /**
     * RMD: This command causes the directory specified in pathname to 
     * be removed as a directory (if pathname is absolute) or as a 
     * subdirectory of the current working directory (if pathname is 
     * relative).
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onRmd(&$event, $params) {
      if (!($element= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': no such file or directory');
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $element, 'onDelete')) return;

      // Delete the element
      try(); {
        $element->delete();
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $params.': '.$e->getMessage());
        return;
      }
      $this->answer($event->stream, 250, $params.': successfully deleted');
    }

    /**
     * RETR: This command causes the server to transfer a copy of the 
     * file specified in pathname to the client. The status and contents 
     * of the file at the server site are unaffected.
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onRetr(&$event, $params) {
      if (!$socket= &$this->openDatasock($event)) return;
    
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        $socket->close();
        return;
      }
      $this->cat && $this->cat->debug($entry->toString());
      if (is('StorageCollection', $entry)) {
        $this->answer($event->stream, 550, $params.': is a directory');
        $socket->close();
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRead')) {
        $socket->close();
        return;
      }

      $this->answer($event->stream, 150, sprintf(
        'Opening %s mode data connection for %s (%d bytes)',
        $this->sessions[$event->stream->hashCode()]->getType(),
        $entry->getName(),
        $entry->getSize()
      ));
      try(); {
        $entry->open(SE_READ);
        while (!$socket->eof() && $buf= $entry->read()) {
          if (!$socket->write($buf)) break;
        }
        $entry->close();
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $params.': '.$e->getMessage());
      } finally(); {
        $socket->close();
        if ($e) return;
      }
      $this->answer($event->stream, 226, 'Transfer complete');
    }

    /**
     * Callback for the "STOR" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onStor(&$event, $params) {
      if (!$socket= &$this->openDatasock($event)) return;
      
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {

        // Invoke interceptor
        $entry= &$this->storage->createEntry($event->stream->hashCode(), $params, ST_ELEMENT);
        if (!$this->checkInterceptors($event, $entry, 'onCreate')) {
          $socket->close();
          return;
        }

        try(); {
          $entry= &$this->storage->create($event->stream->hashCode(), $params, ST_ELEMENT);
        } if (catch('Exception', $e)) {
          $this->answer($event->stream, 550, $params.': '.$e->getMessage());
          $socket->close();
          return;
        }
      } else if (is('StorageCollection', $entry)) {
        $this->answer($event->stream, 550, $params.': is a directory');
        $socket->close();
        return;
      }
      
      $this->answer($event->stream, 150, sprintf(
        'Opening %s mode data connection for %s',
        $this->sessions[$event->stream->hashCode()]->getType(),
        $entry->getName()
      ));
      try(); {
        $entry->open(SE_WRITE);
        while (!$socket->eof() && $buf= $socket->readBinary(32768)) {
          $entry->write($buf);
        }
        $entry->close();
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $params.': '.$e->getMessage());
      } finally(); {
        $socket->close();
        if ($e) return;
      }
      $this->answer($event->stream, 226, 'Transfer complete');
    }

    /**
     * Callback for the "DELE" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onDele(&$event, $params) {
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        return;
      }
      if (is('StorageCollection', $entry)) {
        $this->answer($event->stream, 550, $params.': is a directory');
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onDelete')) return;

      try(); {
        $entry->delete();
      } if (catch('IOException', $e)) {
        $this->answer($event->stream, 450, $params.': ', $e->getMessage());
        return;
      }

      $this->answer($event->stream, 250, $params.': file deleted');
    }
    
    /**
     * Rename a file from filename
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onRnfr(&$event, $params) {
      if (!($entry= &$this->storage->lookup($event->stream->hashCode(), $params))) {
        $this->answer($event->stream, 550, $params.': No such file or directory');
        return;
      }
      $this->cat && $this->cat->debug($entry);
      
      $this->sessions[$event->stream->hashCode()]->setTempVar('rnfr', $entry);
      $this->answer($event->stream, 350, 'File or directory exists, ready for destination name.');
    }
    
    /**
     * Rename a file into filename
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onRnto(&$event, $params) {
      if (!$entry= &$this->sessions[$event->stream->hashCode()]->getTempVar('rnfr')) {
        $this->answer($event->stream, 503, 'Bad sequence of commands');
        return;
      }
      
      // Invoke interceptor
      if (!$this->checkInterceptors($event, $entry, 'onRename')) return;

      try(); {
        $entry->rename($params);
        $this->cat->debug($params);
      } if (catch('Exception', $e)) {
        $this->answer($event->stream, 550, $params.': '. $e->getMessage());
        return;
      }
      
      $this->sessions[$event->stream->hashCode()]->removeTempVar('rnfr');
      $this->answer($event->stream, 250, 'Rename successful');
    }
    

    /**
     * Callback for the "TYPE" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onType(&$event, $params) {
      switch ($params= strtoupper($params)) {
        case TYPE_ASCII:
        case TYPE_BINARY:
          $this->sessions[$event->stream->hashCode()]->setType($params);
          $this->answer($event->stream, 200, 'Type set to '.$params);
          break;
          
        default:
          $this->answer($event->stream, 550, 'Unknown type "'.$params.'"');
      }
    }

    /**
     * Callback for the "STRU" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onStru(&$event, $params) {
      switch ($params= strtoupper($params)) {
        case STRU_FILE:
          $this->answer($event->stream, 200, 'Structure set to '.$params);
          break;
        
        case STRU_RECORD:
        case STRU_PAGE:
          $this->answer($event->stream, 504, $params.': unsupported structure type');
          break;
          
        default:
          $this->answer($event->stream, 501, $params.': unrecognized structure type');
      }
    }

    /**
     * Callback for the "MODE" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onMode(&$event, $params) {
      switch ($params= strtoupper($params)) {
        case MODE_STREAM:
          $this->answer($event->stream, 200, 'Mode set to '.$params);
          break;
        
        case MODE_BLOCK:
        case STRU_COMPRESSED:
          $this->answer($event->stream, 504, $params.': unsupported transfer mode');
          break;
          
        default:
          $this->answer($event->stream, 501, $params.': unrecognized transfer mode');
      }
    }

    /**
     * Callback for the "QUIT" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onQuit(&$event, $params) {
      $this->answer($event->stream, 221, 'Goodbye');
      $event->stream->close();
      
      // Kill associated session
      delete($this->sessions[$event->stream->hashCode()]);
    }

    /**
     * Callback for the "PORT" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onPort(&$event, $params) {
      $this->mode[$event->stream->hashCode()]= DATA_ACTIVE;
      $octets= sscanf($params, '%d,%d,%d,%d,%d,%d');
      $host= sprintf('%s.%s.%s.%s', $octets[0], $octets[1], $octets[2], $octets[3]);
      $port= ($octets[4] * 256) + $octets[5];

      $this->cat && $this->cat->debug('+++ Host is ', $host);
      $this->cat && $this->cat->debug('+++ Port is ', $port);

      $this->datasock[$event->stream->hashCode()]= &new BsdSocket($host, $port);
      $this->answer($event->stream, 200, 'PORT command successful');      
    }

    /**
     * Callback for the "OPTS" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onOpts(&$event, $params) {
      if (2 != sscanf($params, '%s %s', $option, $value)) {
        $this->answer($event->stream, 501, 'OPTS: Invalid numer of arguments');
        return;
      }
      
      // Do not recognize any opts
      $this->answer($event->stream, 501, 'Opts: '.$option.'not understood');
    }

    /**
     * Callback for the "PASV" command
     *
     * @access  protected
     * @param   &peer.server.ConnectionEvent event
     * @param   string params
     */
    function onPasv(&$event, $params) {
      $this->mode[$event->stream->hashCode()]= DATA_PASSIVE;

      if ($this->datasock[$event->stream->hashCode()]) {
        $port= $this->datasock[$event->stream->hashCode()]->port;   // Recycle it!
      } else {      
        $port= rand(1000, 65536);
        $this->datasock[$event->stream->hashCode()]= &new ServerSocket($this->server->socket->host, $port);
        try(); {
          $this->datasock[$event->stream->hashCode()]->create();
          $this->datasock[$event->stream->hashCode()]->bind();
          $this->datasock[$event->stream->hashCode()]->listen();
        } if (catch('IOException', $e)) {
          $this->answer($event->stream, 425, 'Cannot open passive connection '.$e->getMessage());
          delete($this->datasock[$event->stream->hashCode()]);
          return;
        }
      }
      $this->cat && $this->cat->debug('Passive mode: Data socket is', $this->datasock[$event->stream->hashCode()]);
      $octets= strtr(gethostbyname($this->server->socket->host), '.', ',').','.($port >> 8).','.($port & 0xFF);
      $this->answer($event->stream, 227, 'Entering passive mode ('.$octets.')');
    }
    
    /**
     * Method to be triggered when a client connects
     *
     * @access  public
     * @param   &peer.server.ConnectionEvent event
     */
    function connected(&$event) {
      $this->cat && $this->cat->debugf('===> Client %s connected', $event->stream->host);

      // Create a new session object for this client
      $this->sessions[$event->stream->hashCode()]= &new FtpSession();
      $this->answer($event->stream, 220, 'FTP server ready');
    }
    
    /**
     * Method to be triggered when a client has sent data
     *
     * @access  public
     * @param   &peer.server.ConnectionEvent event
     */
    function data(&$event) {
      static $public= array('onhelp', 'onuser', 'onpass', 'onquit');
      
      $this->cat && $this->cat->debug('>>> ', addcslashes($event->data, "\0..\17"));
      sscanf($event->data, "%s %[^\r]", $command, $params);
      $method= 'on'.strtolower($command);

      // Check if method is implemented and answer with code 550 in case
      // it isn't.
      if (!method_exists($this, $method)) {
        $this->answer($event->stream, 500, $command.' not understood');
        return;
      }
      
      // Check if user needs to be logged in in order to execute this command
      if (
        !$this->sessions[$event->stream->hashCode()]->isAuthenticated() && 
        !in_array($method, $public)
      ) {
        $this->answer($event->stream, 530, 'Please log in first');
        return;
      }
      
      try(); {
        $this->{$method}($event, $params);
      } if (catch('Exception', $e)) {
        $this->cat && $this->cat->warn('*** ', $e->toString());
        // Fall through
      }
      xp::gc();
    }
    
    /**
     * Method to be triggered when a client disconnects
     *
     * @access  public
     * @param   &peer.server.ConnectionEvent event
     */
    function disconnected(&$event) {
      $this->cat && $this->cat->debugf('Client %s disconnected', $event->stream->host);
      
      // Kill associated session
      delete($this->sessions[$event->stream->hashCode()]);
    }

  } implements(__FILE__, 'util.log.Traceable');
?>
