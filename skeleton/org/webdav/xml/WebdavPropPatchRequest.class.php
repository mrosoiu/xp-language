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
   * PropPatch request XML
   *
   * PROPPATCH xml
   * <pre>
   *   <?xml version="1.0" encoding="utf-8" ?>
   *   <D:propertyupdate xmlns:D="DAV:">
   *     <D:set>
   *       <D:prop>
   *         <key xmlns="http://webdav.org/cadaver/custom-properties/">value</key>
   *       </D:prop>
   *     </D:set>
   *   </D:propertyupdate>
   * </pre>
   *
   *
   * @purpose  Encapsulate PROPPATCH XML request
   * @see      xp://org.webdav.WebdavScriptlet#doPropPatch
   */
  class WebdavPropPatchRequest extends WebdavScriptletRequest {
    var
      $filename=   '',
      $properties= array(),
      $baseurl=    '';
    
    /**
     * Set data and parse for properties
     *
     * @access public
     * @param  string data The data
     */
    function setData(&$data) {
      static $trans;
      parent::setData($data);

      // Get the NamespacePrefix
      $ns= $this->getNamespacePrefix();
      
      // Select properties which should be set
      foreach (array(
        FALSE => $this->getNode('/'.$ns.':propertyupdate/'.$ns.':set/'.$ns.':prop'),
        TRUE  => $this->getNode('/'.$ns.':propertyupdate/'.$ns.':remove/'.$ns.':prop')
      ) as $remove => $propupdate) {
        if (!$propupdate) continue;
        
        // Copied from WebdavPropFindRequest::setData()
        foreach ($propupdate->children as $node) {
          $name= $node->getName();
          $ns= 'xmlns';
          $nsprefix= '';
          if (($p= strpos($name, ':')) !== FALSE) {
            $ns.= ':'.($nsprefix= substr($name, 0, $p));
            $name= substr($name, $p+1);
          }
          $p= &new WebdavProperty(
            $name,
            $this->decode($node->getContent())
          );
          if ($nsname= $node->getAttribute($ns)) {
            $p->setNamespaceName($nsname);
            if ($nsprefix) $p->setNamespacePrefix($nsprefix);
          }
          $this->addProperty($p, $remove);
        }
      }
      
    }
    
    /**
     * Retrieve base url of request
     *
     * @access  public
     * @return  string
     */
    function getFilename() {
      return $this->filename;
    }
    
    /**
     * Add a property
     *
     * @access  public
     * @param   org.webdav.WebdavProperty property The property object
     */
    function addProperty($property, $remove= FALSE) {
      $this->properties[$remove][]= $property;
    }
    
    /**
     * Get all properties
     *
     * @access  public
     * @return  &org.webdav.WebdavProperty[]
     */
    function &getProperties($remove= FALSE) {
      return $this->properties[$remove];
    }
  }
?>
