<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Container which collects all versions of file
   *
   * @see      org.webdav.DavImpl#VersionControl
   * @purpose  Container of versions
   */
  class WebdavVersionsContainer extends Object {
    var
      $versions= array();
  
    /**
     * Construct
     *
     * @access  public
     * @param   org.webdav.version.Webdav*Version
     */
    function __construct($version= NULL) {
      if ($version !== NULL) $this->addVersion($version);
    }
    
    /**
     * Add a version to the container
     *
     * @access  public
     * @param   org.webdav.version.Webdav*Version
     */
    function addVersion(&$version) {
      $this->versions[]= $version;
    }
    
    /**
     * Get all versions
     *
     * @access  public
     * @return  array versions
     */
    function getVersions() {
      return $this->versions;
    }
    
    /**
     * Returns the last added version object
     *
     * @access  public
     * @return  &org.webdav.version.Webdav*Version
     */
    function &getLatestVersion() {
      return end($this->versions);
    }
  
  }
?>
