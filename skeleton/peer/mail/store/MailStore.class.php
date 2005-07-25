<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses(
    'peer.mail.MessagingException', 
    'peer.mail.store.StoreCache',
    'peer.mail.MailFolder'
  );
 
  /**
   * An abstract class that models a message store and its access protocol, 
   * for storing and retrieving messages. Subclasses provide actual 
   * implementations. 
   *
   * Usage [example with IMAP]:
   * <code>
   *   $stor= &new ImapStore();
   *   try(); {
   *     $stor->connect('imap://user:pass@imap.example.com');
   *     if ($f= &$stor->getFolder('INBOX')) {
   *       $f->open();
   *       $list= &$f->getMessages(range(1, 4), 5);
   *     }
   *   } if (catch('Exception', $e)) {
   *     $e->printStackTrace();
   *     $f->close();
   *     $stor->close();
   *     exit();
   *   }
   * 
   *   for ($i= 0, $s= sizeof($list); $i < $s; $i++) {
   *     echo $list[$i]->toString();
   *   }
   *   $f->close();
   *   $stor->close();
   * </code>
   *
   * @see      xp://peer.mail.MailFolder
   * @purpose  Interface for different MailStores
   */
  class MailStore extends Object {
    var 
      $_hdl  = NULL,
      $cache = NULL;
     
    /**
     * Constructor
     *
     * @access  public
     * @param   peer.mail.store.StoreCache cache default NULL
     */ 
    function __construct($cache= NULL) {
      if (NULL === $cache) {
        $this->cache= &new StoreCache();
      } else {
        $this->cache= &$cache;
      }
      
    }
      
    /**
     * Connect to store
     *
     * @access  abstract
     * @param   string dsn
     * @return  bool success
     */
    function open($dsn) { }
    
    /**
     * Disconnect from store
     *
     * @access  abstract
     * @return  bool success
     */
    function close() { }
  
    /**
     * Get a folder
     *
     * @access  abstract
     * @param   string name
     * @return  &peer.mail.MailFolder
     */
    function &getFolder($name) { }
    
    /**
     * Get all folders
     *
     * @access  abstract
     * @return  &peer.mail.MailFolder[]
     */
    function &getFolders() { }

    /**
     * Open a folder
     *
     * @access  abstract
     * @param   &peer.mail.MailFolder f
     * @param   bool readonly default FALSE
     * @return  bool success
     */
    function openFolder(&$f, $readonly= FALSE) { }
    
    /**
     * Close a folder
     *
     * @access  abstract
     * @param   &peer.mail.MailFolder f
     * @return  bool success
     */
    function closeFolder(&$f) { }
    
    /**
     * Get messages in a folder
     *
     * @access  abstract
     * @param   &peer.mail.MailFolder f
     * @param   mixed* msgnums
     * @return  &peer.mail.Message[]
     */
    function &getMessages(&$f) { }

    /**
     * Get number of messages in this folder
     *
     * @access  abstract
     * @param   &peer.mail.MailFolder f
     * @param   string attr one of "message", "recent" or "unseen"
     * @return  int
     */
    function getMessageCount(&$f, $attr) { }
  }
?>
