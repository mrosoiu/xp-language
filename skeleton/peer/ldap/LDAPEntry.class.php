<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Wraps LDAP entry
   *
   * @purpose  Represent a single entry
   * @see      xp://peer.ldap.LDAPSearchResult
   * @see      xp://peer.ldap.LDAPClient
   */
  class LDAPEntry extends Object {
    var
      $dn=          '',
      $attributes=  array();
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string dn default NULL "distinct name"
     */
    function __construct($dn= NULL, $attrs= array()) {
      $this->dn= $dn;
      $this->attributes= $attrs;
      parent::__construct();
    }

    /**
     * Decode entries (recursively, if needed)
     *
     * @access  private
     * @param   &mixed v
     * @return  string decoded entry
     */
    function _decode(&$v) {
      if (is_array($v)) for ($i= 0, $m= sizeof($v); $i < $m; $i++) {
        $v[$i]= $this->_decode($v[$i]);
        return $v;
      }
      return utf8_decode($v);
    }
        
    /**
     * Creates an LDAP from the raw return data of PHP's ldap_* functions
     * Also performs decoding on the attributes.
     *
     * @model   static
     * @access  public
     * @param   &mixed data return value from ldap_* functions
     * @return  &peer.ldap.LDAPEntry object
     */
    function &fromData(&$data) {
      $e= &new LDAPEntry($data['dn']);
      
      foreach (array_keys($data) as $key) {
        if ('count' == $key || is_int($key)) continue;
        
        $e->attributes[$key]= (is_array($data[$key])
          ? array_map(array(&$e, '_decode'), $data[$key])
          : $e->_decode($data[$key])
        );
        unset($e->attributes[$key]['count']);
      }
      
      return $e;
    }
    
    /**
     * Set this entrie's DN (distinct name)
     *
     * @access  public
     * @param   string dn
     */
    function setDN($dn) {
      $this->dn= $dn;
    }
    
    /**
     * Retreive this entries' DN (distinct name)
     *
     * @access  public
     * @return  string DN
     */
    function getDN() {
      return $this->dn;
    }
    
    /**
     * Set attribute
     *
     * @access  public
     * @param   string key
     * @param   mixed value
     */
    function setAttribute($key, $value) {
      $this->attributes[$key]= $value;
    }
    
    /**
     * Retreive an attribute - an offset may be supplied to define
     * the values offset within the attribute. If -1 (the default)
     * is supplied, an array of attribute values is returned.
     *
     * Note: If the value does not exist, NULL is returned
     *
     * @access  public
     * @param   string key
     * @param   int idx default -1
     * @return  mixed attribute
     */
    function getAttribute($key, $idx= -1) {
      return (($idx >= 0)
        ? isset($this->attributes[$key][$idx]) ? $this->attributes[$key][$idx]  : NULL 
        : isset($this->attributes[$key]) ? $this->attributes[$key] : NULL
      );
    }
    
    /**
     * Retreive all attributes
     *
     * @access  public
     * @return  array
     */
    function getAttributes() {
      return $this->attributes;
    }
  }
?>
