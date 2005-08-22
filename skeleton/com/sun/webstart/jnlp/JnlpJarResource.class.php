<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('com.sun.webstart.jnlp.JnlpResource');

  /**
   * JNLP resource that points to a JAR (Java Archive) file
   *
   * XML representation:
   * <pre>
   *   <jar href="lib/jbosssx-client.jar"/>
   * </pre>
   *
   * XML representation with version:
   * <pre>
   *   <jar href="./lib/util.jar" version="7.2.1.1.Build.12"/>
   * </pre>
   *
   * @see      xp://com.sun.webstart.JnlpResource
   * @purpose  JNLP resource
   */
  class JnlpJarResource extends JnlpResource {
    var
      $href     = '',
      $version  = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   string href
     * @param   string version default NULL
     */
    function __construct($href, $version= NULL) {
      $this->href= $href;
      $this->version= $version;
    }

    /**
     * Set Href
     *
     * @access  public
     * @param   string href
     */
    function setHref($href) {
      $this->href= $href;
    }

    /**
     * Get Href
     *
     * @access  public
     * @return  string
     */
    function getHref() {
      return $this->href;
    }
    
    /**
     * Get JAR location
     *
     * @access  public
     * @return  string
     */
    function getLocation() {
      return $this->href.($this->version ? '?version-id='.$this->version : '');
    }

    /**
     * Set Version
     *
     * @access  public
     * @param   string version
     */
    function setVersion($version) {
      $this->version= $version;
    }

    /**
     * Get Version
     *
     * @access  public
     * @return  string
     */
    function getVersion() {
      return $this->version;
    }

    /**
     * Get name
     *
     * @access  public
     * @return  string
     */
    function getTagName() { 
      return 'jar';
    }

    /**
     * Get attributes
     *
     * @access  public
     * @return  array
     */
    function getTagAttributes() { 
      return array_merge(
        array('href' => $this->href), 
        $this->version ? array('version' => $this->version) : array()
      );
    }
  }
?>
