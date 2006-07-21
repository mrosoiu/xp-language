<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Qualified name
   *
   * Example:
   * <code>
   *   new QName('http://schemas.xmlsoap.org/ws/2003/03/addressing', 'To', 'wsa');
   * </code>
   *
   * Result:
   * <pre>
   *   <wsa:To xmlns:wsa="http://schemas.xmlsoap.org/ws/2003/03/addressing"/>
   * </pre>
   *
   * @purpose  XML Namespaces
   */
  class QName extends Object {
    public
      $namespace    = '',
      $localpart    = '',
      $prefix       = '';
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string namespace
     * @param   string localpart
     */
    public function __construct($namespace, $localpart, $prefix= NULL) {
      $this->namespace= $namespace;
      $this->localpart= $localpart;
      $this->prefix= $prefix;
    }
    
    /**
     * Returns a string representation
     *
     * @access  public
     * @return  string
     */
    public function toString() {
      return ltrim($this->namespace.'/'.$this->localpart, '/');
    }
  }
?>
