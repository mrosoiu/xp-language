<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses(
    'peer.mail.Message',
    'peer.mail.MimeMessage'
  );

  /**
   * Mail folder
   *
   * @purpose  Wrap
   */
  class MailFolder extends Object {
    var
      $name  = '',
      $store = NULL;
      
    var
      $_ofs  = 0;
    
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
      $this->_ofs= 0;
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
      array_unshift($args, $this);
      return call_user_func_array(array($this->store, 'getMessages'), $args);
    }
    
    /**
     * Rewind this folder (set the iterator offset for getMessage() to 0)
     *
     * @access  public
     */
    function rewind() {
      $this->_ofs= 0;
    }
    
    /**
     * Delete a message
     *
     * @access  public
     * @param   &peer.mail.Message msg
     * @return  bool success
     */
    function deleteMessage(&$msg) {
      return $this->store->deleteMessage($this, $msg);
    }

    /**
     * Undelete a message
     *
     * @access  public
     * @param   &peer.mail.Message msg
     * @return  bool success
     */
    function undeleteMessage(&$msg) {
      return $this->store->undeleteMessage($this, $msg);
    }
    
    /**
     * Move a message
     *
     * @access  public
     * @param   &peer.mail.Message msg
     * @return  bool success
     */
    function moveMessage(&$msg) {
      return $this->store->moveMessage($this, $msg);
    }
    
    /**
     * Get next message (iterator)
     *
     * Example:
     * <code>
     *   $f->open();                           
     *   while ($msg= &$f->getMessage()) {     
     *     echo $msg->toString();
     *   }                                     
     *   $f->close();                          
     * </code>
     *
     * @access  public
     * @return  &peer.mail.Message or FALSE to indicate we reached the last mail
     */
    function &getMessage() {
      $this->_ofs++;
      $ret= &$this->store->getMessages($this, $this->_ofs);
      return $ret[0];
    }

    /**
     * Get a message part
     *
     * @access  public
     * @param   string uid
     * @param   string part
     * @return  &int
     */
    function &getMessagePart($uid, $part) { 
      return $this->store->getMessagePart($this, $uid, $part);
    }

    /**
     * Get a message structure
     *
     * @access  public
     * @param   string uid
     * @return  &object
     */
    function &getMessageStruct($uid) { 
      return $this->store->getMessageStruct($this, $uid);
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
