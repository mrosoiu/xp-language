<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.ldap.LDAPClient');

  /**
   * Class encapsulating LDAP queries.
   *
   * @see     xp://peer.ldap.LDAPClient
   * @see     rfc://2254
   * @test    xp://net.xp_framework.unittest.peer.LDAPQueryTest
   * @purpose Wrap LDAP queries
   */
  class LDAPQuery extends Object {
    var
      $filter=      '',
      $scope=       0,
      $base=        '',
      $attrs=       array(),
      $sizelimit=   0,
      $timelimit=   0,
      $sort=        FALSE,
      $deref=       FALSE;
      
    /**
     * Constructor.
     *
     * @access  public
     * @param   string base
     * @param   mixed[] args
     */
    function __construct() {
      $args= func_get_args();
      
      $this->base= array_shift($args);
      if (sizeof ($args)) $this->filter= $this->_prepare($args);
    }

    /**
     * Format the query as requested by the format identifiers. Values are escaped
     * approriately, so they're safe to use in the query.
     *
     * @access  protected
     * @param   mixed[] args
     * @return  string filter
     */
    function _prepare($args) {
      $query= $args[0];
      if (sizeof($args) <= 1) return $query;

      $i= 0;
      
      // This fixes strtok for cases where '%' is the first character
      $query= $tok= strtok(' '.$query, '%');
      while (++$i && $tok= strtok('%')) {
      
        // Support %1$s syntax
        if (is_numeric($tok{0})) {
          sscanf($tok, '%d$', $ofs);
          $mod= strlen($ofs) + 1;
        } else {
          $ofs= $i;
          $mod= 0;
        }
        
        if (is_array($args[$ofs])) {
          return throw(new IllegalArgumentException(
            'Non-scalar or -object given in for LDAP query.'
          ));
        } 
        
        // Type-based conversion
        if (is_a($args[$ofs], 'Date')) {
          $tok{$mod}= 's';
          $arg= $args[$ofs]->toString('YmdHi\\ZO');
        } else if (is_a($args[$ofs], 'Object')) {
          $arg= $args[$ofs]->toString();
        } else {
          $arg= $args[$ofs];
        }
        
        // NULL actually doesn't exist in LDAP, but is being used here to
        // clarify things (ie. show that no argument has been passed)
        switch ($tok{$mod}) {
          case 'd': $r= is_null($arg) ? 'NULL' : sprintf('%.0f', $arg); break;
          case 'f': $r= is_null($arg) ? 'NULL' : floatval($arg); break;
          case 'c': $r= is_null($arg) ? 'NULL' : $arg; break;
          case 's': $r= is_null($arg) ? 'NULL' : strtr($arg, array('(' => '\\28', ')' => '\\29', '\\' => '\\5c', '*' => '\\2a', chr(0) => '\\00')); break;
          default: $r= '%'; $mod= -1; $i--; continue;
        }
        $query.= $r.substr($tok, 1 + $mod);
        
      }
      return substr($query, 1);
    }
    
    /**
     * Prepare a query statement.
     *
     * @access  public
     * @param   mixed[] args
     * @return  string
     */
    function prepare() {
      $args= func_get_args();
      return $this->_prepare($args);
    }
    
    /**
     * Set Filter
     *
     * @access  public
     * @param   string filter
     */
    function setFilter() {
      $args= func_get_args();
      $this->filter= $this->_prepare($args);
    }

    /**
     * Get Filter
     *
     * @access  public
     * @return  string
     */
    function getFilter() {
      return $this->filter;
    }

    /**
     * Set Scope
     *
     * @access  public
     * @param   int scope
     */
    function setScope($scope) {
      $this->scope= $scope;
    }

    /**
     * Get Scope
     *
     * @access  public
     * @return  string
     */
    function getScope() {
      return $this->scope;
    }

    /**
     * Set Base
     *
     * @access  public
     * @param   mixed[] args
     */
    function setBase() {
      $args= func_get_args();
      $this->base= $this->_prepare($args);
    }

    /**
     * Get Base
     *
     * @access  public
     * @return  string
     */
    function getBase() {
      return $this->base;
    }

    /**
     * Checks whether query has a base specified.
     *
     * @access  public
     * @return  bool 
     */
    function hasBase() {
      return (bool)strlen($this->base);
    }

    /**
     * Set Attrs
     *
     * @access  public
     * @param   mixed[] attrs
     */
    function setAttrs($attrs) {
      $this->attrs= $attrs;
    }

    /**
     * Get Attrs
     *
     * @access  public
     * @return  mixed[]
     */
    function getAttrs() {
      return $this->attrs;
    }
    
    /**
     * Check whether to return only requested attributes. If the
     * attrs-array is empty, this returns FALSE. If one element is
     * in it at least, it returns TRUE.
     *
     * @access  public
     * @return  bool attrsonly
     */
    function getAttrsOnly() {
      return sizeof($this->attrs);
    }

    /**
     * Set Sizelimit
     *
     * @access  public
     * @param   int sizelimit
     */
    function setSizelimit($sizelimit) {
      $this->sizelimit= $sizelimit;
    }

    /**
     * Get Sizelimit
     *
     * @access  public
     * @return  int
     */
    function getSizelimit() {
      return $this->sizelimit;
    }

    /**
     * Set Timelimit
     *
     * @access  public
     * @param   int timelimit
     */
    function setTimelimit($timelimit) {
      $this->timelimit= $timelimit;
    }

    /**
     * Get Timelimit
     *
     * @access  public
     * @return  int
     */
    function getTimelimit() {
      return $this->timelimit;
    }

    /**
     * Set sort fields; the field(s) to sort on must be
     * used in the filter, as well, for the sort to take
     * place at all.
     *
     * @see     php://ldap_sort
     * @access  public
     * @param   string[] sort array of fields to sort with
     */
    function setSort($sort) {
      $this->sort= $sort;
    }

    /**
     * Get sort
     *
     * @access  public
     * @return  array sort
     */
    function getSort() {
      return (array)$this->sort;
    }        

    /**
     * Set Deref
     *
     * @access  public
     * @param   bool deref
     */
    function setDeref($deref) {
      $this->deref= $deref;
    }

    /**
     * Get Deref
     *
     * @access  public
     * @return  bool
     */
    function getDeref() {
      return $this->deref;
    }
    
    /**
     * Return a nice string representation of this object.
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $namelen= 0;
      
      $str= sprintf('%s(%s)@{', $this->getClassName(), $this->__id)."\n";
      foreach (array_keys(get_object_vars($this)) as $index) { $namelen= max($namelen, strlen($index)); }
      foreach (get_object_vars($this) as $name => $value) {
        if ('_' == $name{0}) continue;
      
        // Nicely convert certain types
        if (is_bool($value)) $value= $value ? 'TRUE' : 'FALSE';
        if (is_array($value)) $value= implode(', ', $value);
        
        if ('scope' == $name) switch ($value) {
          case LDAP_SCOPE_BASE: $value= 'LDAP_SCOPE_BASE'; break;
          case LDAP_SCOPE_ONELEVEL: $value= 'LDAP_SCOPE_ONELEVEL'; break;
          case LDAP_SCOPE_SUB: $value= 'LDAP_SCOPE_SUB'; break;
        }
        
        $str.= sprintf("  [%-".($namelen+5)."s] %s\n",
          $name,
          $value
        );
      }
      
      return $str."}\n";
    }
  }
?>
