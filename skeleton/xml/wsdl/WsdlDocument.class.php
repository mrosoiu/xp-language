<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'xml.Tree', 
    'xml.Node',
    'xml.wsdl.WsdlMessage',
    'xml.schema.XmlSchema'
  );
  
  // Namespaces
  define('XMLNS_WSDL',    'http://schemas.xmlsoap.org/wsdl/');
  define('XMLNS_XSD',     'http://www.w3.org/2001/XMLSchema');
  define('XMLNS_SOAP',    'http://schemas.xmlsoap.org/wsdl/soap/');
  define('XMLNS_SOAPENC', 'http://schemas.xmlsoap.org/soap/encoding/');
  
  /**
   * WSDL
   *
   * <code>
   *   uses('xml.wsdl.WsdlDocument');
   *   
   *   $d= &new WsdlDocument('urn:GoogleSearch', 'urn:GoogleSearch');
   *   $d->addNamespace('xmlns:typens', 'urn:GoogleSearch');
   *   
   *   $d->addMessage(new WsdlMessage('doGoogleSearch', array(
   *     'key'            => 'string',
   *     'q'              => 'string',
   *     'start'          => 'int',
   *     'maxResults'     => 'int',
   *     'filter'         => 'boolean',
   *     'restrict'       => 'string',
   *     'safeSearch'     => 'boolean',
   *     'lr'             => 'string',
   *     'ie'             => 'string',
   *     'oe'             => 'string',
   *   )));
   *   $d->addMessage(new WsdlMessage('doGoogleSearchResponse', array(
   *     'return'        => array('GoogleSearchResult', 'typens')
   *   )));
   * 
   *   echo $d->getSource(0);
   * </code>
   *
   * @purpose  WSDL
   */
  class WsdlDocument extends Tree {
    var 
      $types    = array(),
      $messages = array(),
      $portTypes= array(),
      $bindings = array(),
      $service  = array();
  
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct($name= NULL, $targetNamespace= NULL) {
      parent::__construct();
      $this->root= &new Node('definitions', NULL, array(
        'xmlns:xsd'       => XMLNS_XSD,
        'xmlns:soap'      => XMLNS_SOAP,
        'xmlns:soapenc'   => XMLNS_SOAPENC,
        'xmlns:wsdl'      => XMLNS_WSDL,
        'xmlns'           => XMLNS_WSDL
      ));
      if (NULL !== $name) $this->setName($name);
      if (NULL !== $targetNamespace) $this->setTargetNamespace($targetNamespace);
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function addNamespace($name, $urn) {
      $this->root->attribute['xmlns:'.$name]= $urn;
    }
    
    /**
     * Set types
     *
     * @access  public
     * @param   &xml.schema.XmlSchema schema
     */
    function setTypes(&$schema) {
      if (!is_a($schema, 'XmlSchema')) {
        trigger_error('Type: '.get_class($schema), E_USER_NOTICE);
        return throw(new IllegalArgumentException('schema is not a xml.schema.XmlSchema'));
      }

      $this->types= &$schema->getComplexTypes();
      
      // Build DOM
      $s= &$this->root->addChild(new Node('xsd:schema', NULL, array(
        'xmlns'           => XMLNS_SCHEMA,
        'targetNamespace' => $schema->getTargetNamespace()
      )));
                
      foreach (array_keys($this->types) as $key) {
        $n= &$s->addChild(new Node('xsd:complexType', NULL, array(
          'name'  => $this->types[$key]->getName()
        )));
        
        foreach ($this->types[$key]->getElements() as $element) {
          $n->addChild(new Node('xsd:element', NULL, array(
            'name'  => $element->name,
            'type'  => $element->namespace.':'.$element->type
          )));
        }
      }
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function addService() {
    
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function addPortType() {
    
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function addBinding() {
    
    }
    
    /**
     * Add a message
     *
     * @access  public
     * @param   &xml.soap.wsdl.WsdlMessage message
     * @return  &xml.soap.wsdl.WsdlMessage the added message
     * @throws  IllegalArgumentException when message is not a WsdlMessage object or message has already been added
     */
    function &addMessage(&$message) {
      if (!is_a($message, 'WsdlMessage')) {
        trigger_error('Type: '.get_class($message), E_USER_NOTICE);
        return throw(new IllegalArgumentException('message is not a xml.wsdl.WsdlMessage'));
      }
      
      // Does this message already exists
      if (isset($this->messages[$message->name])) {
        return throw(new IllegalArgumentException('Cannot add message "'.$message->name.'" twice'));
      }
      
      // Put this in a associative array so searching is O(1)
      $this->messages[$message->name]= array();
      $this->messages[$message->name]['obj']= &$message;
      $this->messages[$message->name]['node']= &$this->root->addChild(new Node(
        'message',
        NULL,
        array('name' => $message->name)
      ));
      
      // Build DOM
      foreach (array_keys($message->parts) as $key) {
        $n= &$this->messages[$message->name]['node']->addChild(new Node(
          'part',
          NULL,
          array('name' => $key)
        ));
        
        // Type
        if (NULL != $message->parts[$key]->type) {
          $n->attribute['type']= $message->parts[$key]->namespace.':'.$message->parts[$key]->type;
        }
        
        // Element
        if (NULL != $message->parts[$key]->element) {
          $n->attribute['element']= $message->parts[$key]->element;
        }
      }
      
      return $message;
    }
    
    /**
     * Retreive a message by name
     *
     * @access  public
     * @param   string name
     * @return  &xml.wsdl.WsdlMessage message or NULL if none is found
     */
    function &getMessageByName($name) {
      return isset($this->messages[$name]) ? $this->messages[$name]['obj'] : NULL;
    }
    
    /**
     * Get first message
     *
     * @access  public
     * @return  &xml.wsdl.WsdlMessage message
     */
    function &getFirstMessage() {
      reset($this->messages);
      return $this->messages[key($this->messages)]['obj'];
    }
    
    /**
     * Get next message
     *
     * <code>
     *   $msg= &$wsdl->getFirstMessage();
     *   do {
     *     var_dump($msg);
     *   } while ($wsdl->getNextMessage());
     * </code>
     *
     * @access  public
     * @return  &xml.wsdl.WsdlMessage message
     */
    function &getNextMessage() {
      if (FALSE === next($this->messages)) return FALSE;
      return $this->messages[key($this->messages)]['obj'];
    }
    
    /**
     * Set this WSDL's name
     *
     * @access  publiuc
     * @param   string name
     */
    function setName($name) {
      $this->root->attribute['name']= $name;
    }
    
    /**
     * Get this WSDL's name
     *
     * @access  public
     * @return  string name
     */
    function getName() {
      return (isset($this->root->attribute['name']) 
        ? $this->root->attribute['name']
        : NULL
      );
    }
    
    /**
     * Set this WSDL's target namespace
     *
     * @access  public
     * @param   string ns
     */
    function setTargetNamespace($ns) {
      $this->root->attribute['targetNamespace']= $ns;
    }

    /**
     * Get this WSDL's target namespace
     *
     * @access  public
     * @return  string
     */
    function getTargetNamespace() {
      return (isset($this->root->attribute['targetNamespace']) 
        ? $this->root->attribute['targetNamespace']
        : NULL
      );
    }

  }

?>
