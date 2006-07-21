<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.ldap.LDAPEntry');

  /**
   * Wraps ldap search results
   *
   * @see php://ldap_get_entries
   */
  class LDAPSearchResult extends Object {
    public
      $data= NULL,
      $size= 0;
      
    public
      $_offset= -1;
  
    /**
     * Constructor
     *
     * @access  public
     * @param   array result returnvalue of ldap_get_entries()
     */
    public function __construct(&$hdl, $res) {
      $this->data= ldap_get_entries($hdl, $res);
      $this->size= $this->data['count'];
      ldap_free_result($res);
    }
    
    /**
     * Returns number of found elements
     *
     * @access  public
     * @return  int
     */
    public function numEntries() {
      return $this->size;
    }
    
    /**
     * Gets first entry
     *
     * @access  public
     * @return  &mixed entry or FALSE if there is no such entry
     */
    public function &getFirstEntry() {
      return $this->getEntry($this->_offset= 0);
    }
    
    /**
     * Get a search entry by offset
     *
     * @access  public
     * @param   int offset
     * @return  &mixed entry or FALSE if none exists by this offset
     * @throws  lang.IllegalStateException in case no search has been performed before
     */
    public function &getEntry($offset) {
      if (NULL == $this->data) {
        throw(new IllegalStateException('Please perform a search first'));
      }
      
      if (!isset($this->data[$offset])) return FALSE;
      return LDAPEntry::fromData($this->data[$offset]);
    }
    
    /**
     * Gets next entry - ideal for loops such as:
     * <code>
     *   while ($entry= &$l->getNextEntry()) {
     *     // doit
     *   }
     * </code>
     *
     * @access  public
     * @return  &mixed entry or FALSE if there are none more
     */
    public function &getNextEntry() {
      return $this->getEntry(++$this->_offset);
    }

  }
?>
