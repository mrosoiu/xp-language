<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses('peer.mail.Message');

  /**
   * Mail folder
   *
   * @see
   * @purpose  Wrap
   */
  class MailFolder extends Object {
    var
      $name  = '',
      $store = NULL;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   &peer.mail.store.MailStore store
     * @param   string name default ''
     */  
    function __construct(&$store, $name= '') {
      $this->name= $name;
      $this->store= &$store;
      parent::__construct();
    }
  
    /**
     * Create string representation, e.g.
     * <pre>
     * peer.mail.MailFolder[INBOX]@{
     *   name  -> peer.mail.store.ImapStore
     *   cache -> peer.mail.store.StoreCache[5]@{
     *     [folder/INBOX            ] object [mailfolder]
     *     [list/message/INBOX1     ] object [message]
     *     [list/message/INBOX2     ] object [message]
     *     [list/message/INBOX3     ] object [message]
     *     [list/message/INBOX5     ] object [message]
     *   }
     * }
     * </pre>
     *
     * @see     xp://peer.mail.store.StoreCache#toString
     * @access  public
     * @return  string
     */
    function toString() {
      return (
        $this->getClassName().
        '['.
        $this->name.
        "]@{\n  name  -> ".
        $this->store->getClassName().
        "\n  cache -> ".
        str_replace("\n", "\n  ", $this->store->cache->toString()).
        "\n}"
      );
    }
    
    /**
     * Open this folder
     *
     * @access  public
     * @param   bool readonly default FALSE
     * @return  bool success
     */
    function open($readonly= FALSE) { 
      return $this->store->openFolder($this, $readonly);
    }

    /**
     * Close this folder
     *
     * @access  public
     * @return  bool success
     */
    function close() { 
      return $this->store->closeFolder($this);
    }
  
    /**
     * Get messages
     *
     * <code>
     *   // Get all messages
     *   $f->getMessages();
     *
     *   // Get messages #1, #4 and #5
     *   $f->getMessages(1, 4, 5);
     *
     *   // Get messages #3, #7 and #10 through #14
     *   $f->getMessages(3, 7, range(10, 14));
     * </code>
     *
     * @access  public
     * @param   mixed* msgnums
     * @return  &peer.mail.Message[]
     */
    function &getMessages() { 
      $args= func_get_args();
      array_unshift($args, &$this);
      return call_user_func_array(array($this->store, 'getMessages'), $args);
    }

    /**
     * Get a message part
     *
     * @access  public
     * @param   string uid
     * @param   string part
     * @return  int
     */
    function &getMessagePart($uid, $part) { 
      return $this->store->getMessagePart($this, $uid, $part);
    }

    /**
     * Get number of messages in this folder
     *
     * @access  public
     * @return  int
     */
    function getMessageCount() {
      return $this->store->getMessageCount($this, 'message');
    }

    /**
     * Get number of new messages in this folder
     *
     * @access  public
     * @return  int
     */
    function getNewMessageCount() {
      return $this->store->getNewMessageCount($this, 'recent');
    }

    /**
     * Get number of unread messages in this folder
     *
     * @access  public
     * @return  intGet number of messages in this folder
     */
    function getUnreadMessageCount() {
      return $this->store->getUnreadMessageCount($this, 'unseen');
    }

  }
?>
