<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('xml.soap.SOAPMessage', 'xml.QName', 'xml.soap.Parameter');
  
  /**
   * Basic SOAP-Client
   *
   * Example:
   * <code>
   *   $s= &new SOAPClient(new SOAPHTTPTransport('<URL>'), '<URN>');
   *   try(); {
   *     $return= $s->invoke('<METHOD>', <PARAMETERS>);
   *   } if (catch('Exception', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *   var_dump($return);
   * </code>
   * 
   * @purpose  Generic SOAP client base class
   */
  class SOAPClient extends Object {
    var 
      $transport          = NULL,
      $action             = '',
      $targetNamespace    = NULL,
      $mapping            = array();
    
    /**
     * Constructor
     *
     * @access  public
     * @param   &xml.soap.transport.SOAPTransport transport a SOAP transport
     * @param   string action Action
     * @param   string targetNamespace default NULL
     */
    function __construct(&$transport, $action, $targetNamespace= NULL) {
      $this->transport= &$transport;
      $this->action= $action;
      $this->targetNamespace= $namespace;
    }

    /**
     * Set TargetNamespace
     *
     * @access  public
     * @param   string targetNamespace
     */
    function setTargetNamespace($targetNamespace= NULL) {
      $this->targetNamespace= $targetNamespace;
    }

    /**
     * Set trace for debugging
     *
     * @access  public
     * @param   &util.log.LogCategory cat
     */
    function setTrace(&$cat) {
      $this->transport->setTrace($cat);
    }
    
    /**
     * Register mapping for a qname to a class object
     *
     * @access  public
     * @param   &xml.QName qname
     * @param   &lang.XPClass class
     * @throws  lang.IllegalArgumentException
     */
    function registerMapping(&$qname, &$class) {
      if (!is('XPClass', $class)) {
        return throw(new IllegalArgumentException(
          'Argument class is not an XPClass (given: '.xp::typeOf($class).')'
        ));
      }
      $this->mapping[strtolower($qname->toString())]= &$class;
    }
    
    /**
     * Invoke method call
     *
     * @access  public
     * @param   string method name
     * @param   mixed vars
     * @return  mixed answer
     * @throws  lang.IllegalArgumentException
     * @throws  xml.soap.SOAPFaultException
     */
    function invoke() {
      if (!is_a($this->transport, 'SOAPTransport')) return throw(new IllegalArgumentException(
        'Transport must be a xml.soap.transport.SOAPTransport'
      ));
      
      $args= func_get_args();
      
      $this->answer= &new SOAPMessage();
      $this->message= &new SOAPMessage();
      $this->message->create($this->action, array_shift($args), $this->targetNamespace);
      $this->message->setData($args);

      // Send
      if (FALSE == ($response= &$this->transport->send($this->message))) return FALSE;
      
      // Response
      if (FALSE == ($this->answer= &$this->transport->retrieve($response))) return FALSE;
      
      $data= $this->answer->getData('ENUM', $this->mapping);
      return sizeof($data) == 1 ? $data[0] : $data;
    }
  } implements(__FILE__, 'util.log.Traceable');
?>
