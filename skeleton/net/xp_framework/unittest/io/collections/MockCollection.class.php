<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('net.xp_framework.unittest.io.collections.MockElement');

  /**
   * IOCollection implementation
   *
   * @see      xp://io.collections.IOCollection
   * @purpose  Mock object
   */
  class MockCollection extends Object {
    var
      $_elements = array(),
      $_offset   = -1;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string uri
     */
    function __construct($uri) {
      $this->uri= $uri;
    }

    /**
     * Add an element to the collection. Returns the added element.
     *
     * @access  public
     * @return  &io.collection.IOElement e
     * @return  &io.collection.IOElement
     */
    function &addElement(&$e) {
      $this->_elements[]= &$e;
      return $e;
    }
      
    /**
     * Returns this element's URI
     *
     * @access  public
     * @return  string
     */
    function getURI() {
      return $this->uri;
    }
    
    /**
     * Open this collection
     *
     * @access  public
     */
    function open() { 
      $this->_offset= 0;
    }

    /**
     * Rewind this collection (reset internal pointer to beginning of list)
     *
     * @access  public
     */
    function rewind() { 
      $this->_offset= 0;
    }
    
    /**
     * Retrieve next element in collection. Return NULL if no more entries
     * are available
     *
     * @access  public
     * @return  &io.collection.IOElement
     */
    function &next() {
      if (-1 == $this->_offset) return throw(new IllegalStateException('Not open'));
      if ($this->_offset >= sizeof($this->_elements)) return NULL;

      return $this->_elements[$this->_offset++];
    }

    /**
     * Close this collection
     *
     * @access  public
     */
    function close() { 
      $this->_offset= -1;
    }

    /**
     * Retrieve this element's size in bytes
     *
     * @access  public
     * @return  int
     */
    function getSize() { 
      return 512;
    }

    /**
     * Retrieve this element's created date and time
     *
     * @access  public
     * @return  &util.Date
     */
    function &createdAt() {
      return NULL;
    }

    /**
     * Retrieve this element's last-accessed date and time
     *
     * @access  public
     * @return  &util.Date
     */
    function &lastAccessed() {
      return NULL;
    }

    /**
     * Retrieve this element's last-modified date and time
     *
     * @access  public
     * @return  &util.Date
     */
    function &lastModified() {
      return NULL;
    }

    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() { 
      return $this->getClassName().'('.$this->uri.')';
    }
  
  } implements(__FILE__, 'io.collections.IOCollection');
?>
