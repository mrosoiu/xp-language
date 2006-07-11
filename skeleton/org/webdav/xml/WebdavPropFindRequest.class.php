<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'org.webdav.WebdavScriptletRequest',
    'org.webdav.WebdavProperty'
  );

  /**
   * PropFind request XML
   *
   * PROPFIND xml[1]:
   * <pre>
   *   <?xml version="1.0" encoding="utf-8"?>
   *   <propfind xmlns="DAV:">
   *     <allprop/>
   *   </propfind>
   * </pre>
   *
   * PROPFIND xml[2]:
   * <pre>
   *   <?xml version="1.0" encoding="utf-8"?>
   *   <propfind xmlns="DAV:">
   *     <prop>
   *       <getcontentlength xmlns="DAV:"/>
   *       <getlastmodified xmlns="DAV:"/>
   *       <displayname xmlns="DAV:"/>
   *       <executable xmlns="http://apache.org/dav/props/"/>
   *       <resourcetype xmlns="DAV:"/>
   *     </prop>
   *   </propfind>
   * </pre>
   *
   * @purpose  Encapsulate PROPFIND XML request
   * @see      xp://org.webdav.WebdavScriptlet#doPropFind
   */
  class WebdavPropFindRequest extends WebdavScriptletRequest {
    var
      $request    = NULL,
      $properties = array(),
      $path       = '',
      $webroot    = '',
      $depth      = 0;
    
    /**
     * Set data and parse for properties
     *
     * @access public
     * @param  string data The data
     */
    function setData(&$data) {
      parent::setData($data);
      
      // Set properties
      if (
        !$this->getNode('/propfind/allprop') &&
        ($propfind= &$this->getNode('/propfind/prop'))
      ) {
        foreach ($propfind->children as $node) {
          $name= $node->getName();
          $ns= 'xmlns';
          $nsprefix= '';
          if (($p= strpos($name, ':')) !== FALSE) {
            $ns.= ':'.($nsprefix= substr($name, 0, $p));
            $name= substr($name, $p+1);
          }
          $p= &new WebdavProperty($name);
          if ($nsname= $node->getAttribute($ns)) {
            $p->setNamespaceName($nsname);
            if ($nsprefix) $p->setNamespacePrefix($nsprefix);
          }
          $this->addProperty($p);
        }
      }
    }
    
    /**
     * Return Depth header field
     *
     * @access public
     * @return string
     */
    function getDepth() {
      switch ($this->getHeader('Depth')) {
        case 'infinity': return 0x7FFFFFFF; break;
        case 1:          return 0x00000001; break;
        default:         return 0x00000000; break;
      }
      
    }
    
    /**
     * Retrieve base uri of request
     *
     * @access  public
     * @return  string
     */
    function getWebroot() {
      return $this->webroot;
    }

    /**
     * Add a property
     *
     * @access  public
     * @param   org.webdav.WebdavProperty property The property object
     */
    function addProperty($property) {
      $this->properties[]= $property;
    }
    
    /**
     * Get all properties
     *
     * @access  public
     * @return  &org.webdav.WebdavProperty[]
     */
    function &getProperties() {
      return $this->properties;
    }
  }
?>
