<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'xml.Tree',
    'xml.Node',
    'xml.soap.SOAPNode',
    'xml.soap.SOAPFault'
  );
  
  define('XMLNS_SOAPENV',       'http://schemas.xmlsoap.org/soap/envelope/');
  define('XMLNS_SOAPENC',       'http://schemas.xmlsoap.org/soap/encoding/');
  define('XMLNS_SOAPINTEROP',   'http://soapinterop.org/xsd');
  define('XMLNS_XSD',           'http://www.w3.org/2001/XMLSchema');
  define('XMLNS_XSI',           'http://www.w3.org/2001/XMLSchema-instance');
  define('XMLNS_XP',            'http://xp-framework.net/xmlns/xp');
  
  /**
   * A SOAP Message consists of an envelope containing a body, and optionally,
   * headers.
   *
   * Example message in its XML representation:
   * <pre>
   * <?xml version="1.0" encoding="iso-8859-1"?>
   * <SOAP-ENV:Envelope
   *  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
   *  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
   *  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   *  xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
   *  xmlns:si="http://soapinterop.org/xsd"
   *  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
   *  xmlns:ctl="urn://binford/Power"
   * >
   *   <SOAP-ENV:Body>
   *     <ctl:getPower/>
   *   </SOAP-ENV:Body>
   * </SOAP-ENV:Envelope>
   * </pre>
   *
   * @see      xp://xml.Tree
   * @purpose  Represent SOAP Message
   */
  class SOAPMessage extends Tree {
    var 
      $body         = '',
      $namespace    = 'ctl',
      $encoding     = XML_ENCODING_DEFAULT,
      $nodeType     = 'SOAPNode',
      $action       = '',
      $method       = '';

    var 
      $namespaces   = array(
        XMLNS_SOAPENV     => 'SOAP-ENV',
        XMLNS_XSD         => 'xsd',
        XMLNS_XSI         => 'xsi',
        XMLNS_SOAPENC     => 'SOAP-ENC',
        XMLNS_SOAPINTEROP => 'si'
      );

    /**
     * Create a message
     *
     * @access  public
     * @param   string action
     * @param   string method
     */
    function create($action, $method) {
      $this->action= $action;
      $this->method= $method;

      $this->root= &new Node('SOAP-ENV:Envelope', NULL, array(
        'xmlns:SOAP-ENV'              => XMLNS_SOAPENV,
        'xmlns:xsd'                   => XMLNS_XSD,
        'xmlns:xsi'                   => XMLNS_XSI,
        'xmlns:SOAP-ENC'              => XMLNS_SOAPENC,
        'xmlns:si'                    => XMLNS_SOAPINTEROP,
        'SOAP-ENV:encodingStyle'      => XMLNS_SOAPENC,
        'xmlns:'.$this->namespace     => $this->action   
      ));
      $this->root->addChild(new Node('SOAP-ENV:Body'));
      $this->root->children[0]->addChild(new Node($this->namespace.':'.$this->method));
    }
    
    /**
     * Set data
     *
     * @access  public
     * @param   array arr
     */
    function setData($arr) {
      $node= &SOAPNode::fromArray($arr, 'item');
      $node->namespace= $this->namespace;
      if (empty($node->children)) return;
      
      // Copy all of node's children to root element
      foreach (array_keys($node->children) as $i) {
        $this->root->children[0]->children[0]->addChild($node->children[$i]);
      }
    }

    /**
     * Deserialize a single node
     *
     * @access  private
     * @param   &xml.Node child
     * @param   string context default NULL
     * @param   array mapping
     * @return  &mixed result
     */
    function &unmarshall(&$child, $context= NULL, $mapping) {
       // DEBUG Console::writeLine('Unmarshalling ', $child->name, ' (', var_export($child->attribute, 1), ') >>> ', $child->content, '<<<', "\n"); // DEBUG
      if (
        isset($child->attribute[$this->namespaces[XMLNS_XSI].':null']) or       // Java
        isset($child->attribute[$this->namespaces[XMLNS_XSI].':nil'])           // SOAP::Lite
      ) {
        return NULL;
      }

      // References
      if (isset($child->attribute['href'])) {
        foreach (array_keys($this->root->children[0]->children) as $idx) {
          if (0 != strcasecmp(
            @$this->root->children[0]->children[$idx]->attribute['id'],
            substr($child->attribute['href'], 1)
          )) continue;
 
          // Create a copy and pass name to it
          $c= $this->root->children[0]->children[$idx];
          $c->name= $child->name;
          return $this->unmarshall($c, $context, $mapping);
          break;
        }
      }
      
      // Update namespaces list
      $xpns= NULL;
      foreach ($child->attribute as $key => $val) {
        if (0 != strncmp('xmlns:', $key, 6)) continue;
        $this->namespaces[$val]= substr($key, 6);

        // Recognize XP objects
        if (0 == strncmp(XMLNS_XP, $val, 32)) {
          $xpns= substr($child->attribute[$this->namespaces[XMLNS_XSI].':type'], strlen($key) - 5);
        }
      }
      
      if ($xpns) {
        try(); {
          $class= &XPClass::forName($xpns);
        } if (catch('ClassNotFoundException', $e)) {

          // Handle this gracefully
          $class= &XPClass::forName('lang.Object');
        }

        $result= &$class->newInstance();
        foreach ($this->_recurseData($child, TRUE, 'OBJECT', $mapping) as $key => $val) {
          $result->$key= $val;
        }

        return $result;          
      }

      // Type dependant
      if (!isset($child->attribute[$this->namespaces[XMLNS_XSI].':type']) || !preg_match(
        '#^([^:]+):([^\[]+)(\[[0-9+]\])?$#', 
        $child->attribute[$this->namespaces[XMLNS_XSI].':type'],
        $regs
      )) {
        // E.g.: SOAP-ENV:Fault
        $regs= array(0, 'xsd', 'string');
      }

      // SOAP-ENC:arrayType="xsd:anyType[4]"
      if (isset($child->attribute[$this->namespaces[XMLNS_SOAPENC].':arrayType'])) {
        $regs[2]= 'Array';
      }

      switch (strtolower($regs[2])) {
        case 'array':
        case 'vector':
          $result= $this->_recurseData($child, FALSE, 'ARRAY', $mapping);
          break;

        case 'map':
          // <old_data xmlns:ns4="http://xml.apache.org/xml-soap" xsi:type="ns4:Map">
          // <item>
          // <key xsi:type="xsd:string">Nachname</key>
          // <value xsi:type="xsd:string">Braun</value>
          // </item>
          // <item>
          // <key xsi:type="xsd:string">PLZ</key>
          // <value xsi:type="xsd:string">76135</value>
          // </item>
          // <item>
          if (empty($child->children)) break;
          foreach ($child->children as $item) {
            $key= $item->children[0]->getContent($this->getEncoding(), $this->namespaces);
            $result[$key]= ((empty($item->children[1]->children) && !isset($item->children[1]->attribute['href']))
              ? $item->children[1]->getContent($this->getEncoding(), $this->namespaces)
              : $this->unmarshall($item->children[1], 'MAP', $mapping)
            );
          }
          break;

        case 'soapstruct':
        case 'struct':      
        case 'ur-type':
          if ('xsd' == $regs[1]) {
            $result= $this->_recurseData($child, TRUE, 'HASHMAP', $mapping);
            break;
          }

          $result= &new Object();
          foreach ($this->_recurseData($child, TRUE, 'OBJECT', $mapping) as $key => $val) {
            $result->$key= $val;
          }
          break;
          
        default:
          if (!empty($child->children)) {
            if ($this->namespaces[XMLNS_XSD] == $regs[1]) {
              $result= $this->_recurseData($child, TRUE, 'STRUCT', $mapping);
              break;
            }

            // Check for mapping
            $qname= strtolower(array_search($regs[1], $this->namespaces).'/'.$regs[2]);
            if (isset($mapping[$qname])) {
              $result= &$mapping[$qname]->newInstance();
            } else {
              $result= &new Object();
              $result->qname= $qname;
            }
            foreach ($this->_recurseData($child, TRUE, 'OBJECT', $mapping) as $key => $val) {
              $result->$key= $val;
            }
            break;
          }

          $result= $child->getContent($this->getEncoding(), $this->namespaces);
      }

      return $result;
    }

    /**
     * Recursively unmarshall data
     *
     * @access  private
     * @param   &xml.Node node
     * @param   bool names default FALSE
     * @param   string context default NULL
     * @param   array mapping
     * @return  &mixed data
     */    
    function &_recurseData(&$node, $names= FALSE, $context= NULL, $mapping) {
      if (empty($node->children)) return array();

      foreach ($node->attribute as $key => $val) {
        if (0 != strncmp('xmlns:', $key, 6)) continue;
        $this->namespaces[$val]= substr($key, 6);
      }
      
      $results= array();
      for ($i= 0, $s= sizeof($node->children); $i < $s; $i++) {
        $results[$names ? array_pop(explode(':', $node->children[$i]->name)) : $i]= $this->unmarshall(
          $node->children[$i], 
          $context,
          $mapping
        );
      }
      return $results;
    }

    /**
     * Set fault
     *
     * @access  public
     * @param   int faultcode
     * @param   string faultstring
     * @param   string faultactor default NULL
     * @param   mixed detail default NULL
     */    
    function setFault($faultcode, $faultstring, $faultactor= NULL, $detail= NULL) {
      $this->root->children[0]->children[0]= &SOAPNode::fromObject(new SOAPFault(
        $faultcode,
        $faultstring,
        $faultactor,
        $detail
      ), 'SOAP-ENV:Fault');
      $this->root->children[0]->children[0]->name= 'SOAP-ENV:Fault';
    }

    /**
     * Construct a SOAP message from a string
     *
     * <code>
     *   $msg= &SOAPMessage::fromString('<SOAP-ENV:Envelope>...</SOAP-ENV:Envelope>');
     * </code>
     *
     * @model   static
     * @access  public
     * @param   string string
     * @return  &xml.Tree
     */
    function &fromString($string) {
      return parent::fromString($string, 'SOAPMessage');
    }

    /**
     * Construct a SOAP message from a file
     *
     * <code>
     *   $msg= &SOAPMessage::fromFile(new File('foo.soap.xml');
     * </code>
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @return  &xml.Tree
     */ 
    function &fromFile(&$file) {
      return parent::fromFile($file, 'SOAPMessage');
    }

    /**
     * Retrieve body element
     *
     * @access  protected
     * @return  &xml.SOAPNode
     * @throws  lang.FormatException in case the body element could not be found
     */    
    function &_bodyElement() {

      // Look for namespaces in the root node
      foreach ($this->root->attribute as $key => $val) {
        if (0 != strncmp('xmlns:', $key, 6)) continue;
        $this->namespaces[$val]= substr($key, 6);
      }
      
      // Search for the body node. For usual, this will be the first element,
      // but some SOAP clients may include a header node (which we silently 
      // ignore for now).
      for ($i= 0, $s= sizeof($this->root->children); $i < $s; $i++) {
        if (0 == strcasecmp(
          $this->root->children[$i]->getName(), 
          $this->namespaces[XMLNS_SOAPENV].':Body'
        )) return $this->root->children[$i]->children[0];
      }

      return throw(new FormatException('Could not locate Body element'));
    }

    /**
     * Get fault
     *
     * @access  public
     * @return  &xml.soap.SOAPFault or NULL if none exists
     */
    function &getFault() {
      if ($body= &$this->_bodyElement()) {
        if (0 != strcasecmp(
          $body->getName(), 
          $this->namespaces[XMLNS_SOAPENV].':Fault'
        )) return NULL;

        $return= $this->_recurseData($body, TRUE, 'OBJECT', array());
        // DEBUG Console::writeLine('RETURN >>> ', var_export($return, 1), '***'); // DEBUG
        return new SOAPFault(
          isset($return['faultcode'])   ? $return['faultcode']    : '',
          isset($return['faultstring']) ? $return['faultstring']  : '',
          isset($return['faultactor'])  ? $return['faultactor']   : '',
          isset($return['detail'])      ? $return['detail']       : ''
        );
      }
    }
    
    /**
     * Get data
     *
     * @access  public
     * @param   string context default 'ENUM'
     * @param   array mapping default array()
     * @return  &mixed data
     * @throws  lang.FormatException in case no XMLNS_SOAPENV:Body was found
     */
    function &getData($context= 'ENUM', $mapping= array()) {
      if ($body= &$this->_bodyElement()) {
        return $this->_recurseData($body, FALSE, $context, $mapping);
      }
    }
  }
?>
