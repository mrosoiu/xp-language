<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.collections.IOElement');

  /**
   * Represents a file element
   *
   * @see      xp://io.collections.FileCollection
   * @purpose  Interface
   */
  class FileElement extends Object implements IOElement {
    public
      $uri= '';

    /**
     * Constructor
     *
     * @param   string uri
     */
    public function __construct($uri) {
      $this->uri= $uri;
    }

    /**
     * Returns this element's URI
     *
     * @return  string
     */
    public function getURI() { 
      return $this->uri;
    }

    /**
     * Retrieve this element's size in bytes
     *
     * @return  int
     */
    public function getSize() { 
      return filesize($this->uri);
    }

    /**
     * Retrieve this element's created date and time
     *
     * @return  &util.Date
     */
    public function createdAt() {
      return new Date(filectime($this->uri));
    }

    /**
     * Retrieve this element's last-accessed date and time
     *
     * @return  &util.Date
     */
    public function lastAccessed() {
      return new Date(fileatime($this->uri));
    }

    /**
     * Retrieve this element's last-modified date and time
     *
     * @return  &util.Date
     */
    public function lastModified() {
      return new Date(filemtime($this->uri));
    }
    
    /**
     * Creates a string representation of this object
     *
     * @return  string
     */
    public function toString() { 
      return $this->getClassName().'('.$this->uri.')';
    }

  } 
?>
