<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  uses(
    'peer.mail.store.MailStore',
    'io.Folder',
    'io.File'
  );
  
  /**
   * Mail store
   *
   * @see     http://cr.yp.to/proto/maildir.html
   * @see     http://www.courier-mta.org/maildir.html
   * @purpose Incarnation of abstract class MailStore for Maildir
   * @experimental
   */
  class MaildirStore extends MailStore {
    var 
      $cache= NULL,
      $currentFolder= NULL;
    
    var
      $_folder= NULL,
      $_root=   NULL;
      
    /**
     * Constructs a MaildirStore object. 
     *
     * @access public
     */
    function __construct($cache= NULL) {
      parent::__construct($cache);
    }
    
    /**
     * Opens a Maildir store. If no parameter is given, this opens
     * the users default mailbox located in $HOME/Maildir/.
     *
     * @access  public
     * @param   string folder default NULL
     * @return  boolean success
     */    
    function open($folder= NULL) {
      if (NULL === $folder)
        $folder= getenv ('HOME').DIRECTORY_SEPARATOR.'Maildir';
      
      try(); {
        $this->_folder= &new Folder ($folder);
        $this->_folder->open();
      } if (catch('IOException', $e)) {
        $this->_folder= NULL;
        return $e;
      }
      
      $this->currentfolder= '';
      $this->_root= realpath ($folder);
      
      return TRUE;
    }
    
    /**
     * Returns the non-global foldername.
     *
     * @access  private
     * @param   string folder
     * @return  string realfolder
     */
    function _getFolderName($folder) {
      return str_replace (
        $this->_root,
        '',
        realpath ($folder)
      );
    }    
    
    /**
     * Closes currently open mailstore.
     *
     * @access  public
     * @return  bool
     */
    function close() {
      return $this->_folder->close();
    }
    
    /**
     * Opens a subfolder of the current folder and returns
     * an object of that mailbox.
     *
     * @access  public
     * @param   string foldername
     * @return  &peer.mail.MailFolder folder;
     */    
    function &getFolder($name) {
      $f= &new Folder ($this->_folder->getURI().DIRECTORY_SEPARATOR.$name);
      if (!$f->exists())
        return throw (new MessagingException (
          'Maildir does not exist: '.$f->getURI()
        ));
      
      $mf= &new MailFolder ($this, $name);
      return $mf;
    }
    
    /**
     * Returns a list of all subfolders in current folder.
     *
     * @access  public
     * @return  &array* folders array of peer.mail.MailFolder objects
     */
    function &getFolders() {
      $f= array();
      while ($entry= $this->_folder->getEntry()) {
        if (is_dir ($this->_folder->getURI().DIRECTORY_SEPARATOR.$entry)) {
          if ('.' != $entry{0} || '.' == $entry || '..' == $entry) {
            $f[]= &new MailFolder (
              $this,
              $this->_getFolderName ($entry)
            );
          }
        }
      }
      
      return $f;
    }
    
    /**
     * Opens a folder.
     *
     * @access  public
     * @param   &peer.mail.MailFolder folder
     * @param   boolean readonly default FALSE
     * @return  boolean success
     * @throws  lang.IllegalAccessException if another folder is still open
     * @throws  io.IOException if folder cannot be opened
     */
    function openFolder(&$f, $readonly= FALSE) {
      // Is it already open?
      if ($this->currentfolder === $f->name)
        return TRUE;

      // Only one open folder at a time
      if (NULL !== $this->currentfolder) {
        trigger_error('Currently open Folder: '.$this->currentfolder, E_USER_NOTICE);
        return throw(new IllegalAccessException(
          'There can only be one open folder at a time. Close the currently open folder first.',
          $f->name
        ));      
      }
      
      try(); {
        $nf= &new Folder ($this->_root.DIRECTORY_SEPARATOR.$f->name);
        $nf->open();
      } if (catch('IOException', $e)) {
        return throw ($e);
      }
      
      $this->_folder= &$nf;
      
      $this->currentfolder= $f->name;
      return TRUE;
    }

    /**
     * Closes an open folder.
     *
     * @access  public
     * @param   &peer.mail.MailFolder folder
     * @return  boolean success
     * @throws  lang.IllegalArgumentException if folder is not opened folder
     */    
    function closeFolder(&$f) {
      // Is it already open?
      if ($this->currentfolder !== $f->name)
        return throw (new IllegalArgumentException (
          'Cannot close non-opened folder!',
          $f->name
        ));
      
      $this->_folder->close();
      $this->currentfolder= NULL;
      return TRUE;
    }
    
    /**
     * Gets the count of messages with speciefied attribute
     * or all messages when no attribute was specified
     *
     * @access  public
     * @param   &peer.mail.Mailfolder f
     * @param   int attr default 0xFFFF
     * @return  int count
     */    
    function getMessageCount(&$f, $attr= 0xFFFF) {
      $this->openFolder ($f);
      $f= &new Folder ($f->name.DIRECTORY_SEPARATOR.'cur');
      if (!$f->exists())
        return 0;
      
      $cnt= 0;
      try(); {
        $f->open();
        while ($e= $f->getEntry()) {
          if ($attr & $this->_getMailFlags ($e)) $cnt++;
        }
        $f->close();
      } if (catch('Exception', $e)) {
        return throw ($e);
      }
      
      return $cnt;
    }
    
    /**
     * Returns the URI to a specific message in a Maildir. This is the
     * absolute path to that file.
     *
     * @access  private
     * @param   &peer.mail.MailFolder folder
     * @param   int number
     * @return  string uri
     */    
    function _getMessageURI(&$f, $nr) {
      $this->_folder->rewind();

      while (FALSE !== ($entry= $this->_folder->getEntry()) && $nr <= $i++) {
        if ($nr == $i) 
          return $f->getURI().DIRECTORY_SEPARATOR.$entry;
      }
      return FALSE;
    }
    
    /**
     * Returns the flags of the specified message given in the filename.
     *
     * @access private
     * @param string filename
     * @return int flags
     */    
    function _getMailFlags($filename) {
      static
        $maildirFlagMatrix= array (
          'R' => MAIL_FLAG_ANSWERED,
          'S' => MAIL_FLAG_SEEN,
          'T' => MAIL_FLAG_DELETED,
          'D' => MAIL_FLAG_DRAFT,
          'F' => MAIL_FLAG_TAGGED
        );
    
      $flagString= substr ($e, strpos ('2,'));
      for ($i= 0; $i < count ($flagString); $i++)
        $flags|= $maildirFlagMatrix[$flagString{$i}];

      return $flags;
    }
    
    /**
     * Reads the whole message, applies the header information,
     * sets the body as a plain text (thus does not parse any
     * MIME-Information and returns the created Message object.
     *
     * @access  private
     * @param   string filename
     * @return  &peer.mail.Message
     * @throws  io.IOException if file cannot be read
     */    
    function &_readMessageRaw($filename) {
      $header= '';
      $body= '';
      try(); {
        $f= &new File ($filename);
        $f->open ();
        $d= $f->read ($f->size());
        $f->close();
      } if (catch('IOException', $e)) {
        return throw ($e);
      }
    
      if (FALSE === ($hdrEnd= strpos ($d, "\n\r\n\r")))
        $hdrEnd= 0;
        
      $h= substr ($c, 0, $hdrEnd);
      $b= substr ($c, $hdrEnd);
      
      $msg= &new Message();
      $msg->setHdrString ($h);
      $msg->setBody ($b);
      
      return $msg;
    }
    
    /**
     * Returns an array of messages specified by the numbers in the
     * argument
     *
     * @access  public 
     * @param   &peer.mail.MailFolder folder
     * @param   mixed* msgnums
     * @return  array messages
     */    
    function getMessages(&$f) {
      $this->openFolder ($f);
      if (1 == func_num_args()) {
        $count= $this->getMessageCount ();
        $msgnums= range (1, $count);
      } else {
        $msgnums= array();
        for ($i= 1, $s= func_num_args(); $i < $s; $i++) {
          $arg= &func_get_arg($i);
          $msgnums= array_merge($msgnums, $arg);
        }
      }
      
      $messages= array();
      foreach ($msgnums as $msg) {
        $filename= $this->_getMessageURI($f, $msg);
        $flags= $this->_getMailFlags($filename);
        
        try(); {
          $msg= &$this->_readMessageRaw($filename);
        } if (catch('IOException', $e)) {
        
          // Ignore any errors
          continue;
        }
        
        $messages[]= &$msg;
      }
      
      return $messages;
    }
    
  }
?>
