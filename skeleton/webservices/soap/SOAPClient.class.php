<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('webservices.soap.SOAPMessage', 'xml.QName', 'webservices.soap.Parameter', 'webservices.soap.SOAPMapping');
  
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
   * @test     xp://net.xp_framework.unittest.soap.SoapClientTest
   * @purpose  Generic SOAP client base class
   */
  class SOAPClient extends Object {
    var 
      $encoding           = 'iso-8859-1',
      $transport          = NULL,
      $action             = '',
      $targetNamespace    = NULL,
      $mapping            = NULL,
      $headers            = array();
    
    /**
     * Constructor
     *
     * @access  public
     * @param   &webservices.soap.transport.SOAPTransport transport a SOAP transport
     * @param   string action Action
     * @param   string targetNamespace default NULL
     */
    function __construct(&$transport, $action, $targetNamespace= NULL) {
      $this->transport= &$transport;
      $this->action= $action;
      $this->targetNamespace= $targetNamespace;
      $this->mapping= &new SOAPMapping();
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
     * Set encoding
     *
     * @access  public
     * @param   string encoding either utf-8 oder iso-8859-1
     */
    function setEncoding($encoding) {
      $this->encoding= $encoding;
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
     */
    function registerMapping(&$qname, &$class) {
      $this->mapping->registerMapping($qname, $class);
    }
    
    /**
     * Add a header
     *
     * @access  public
     * @param   &webservices.soap.SOAPHeader header
     * @return  &webservices.soap.SOAPHeader the header added
     */
    function &addHeader(&$header) {
      $this->headers[]= &$header;
      return $header;
    }
    
    /**
     * Invoke method call
     *
     * @access  public
     * @param   string method name
     * @param   mixed vars
     * @return  mixed answer
     * @throws  lang.IllegalArgumentException
     * @throws  webservices.soap.SOAPFaultException
     */
    function invoke() {
      if (!is_a($this->transport, 'SOAPTransport')) return throw(new IllegalArgumentException(
        'Transport must be a webservices.soap.transport.SOAPTransport'
      ));
      
      $args= func_get_args();
      
      $message= &new SOAPMessage();
      $message->setEncoding($this->encoding);
      $message->createCall($this->action, array_shift($args), $this->targetNamespace, $this->headers);
      $message->setMapping($this->mapping);
      $message->setData($args);

      // Send
      if (FALSE == ($response= &$this->transport->send($message))) return FALSE;
      
      // Response
      if (FALSE == ($answer= &$this->transport->retrieve($response))) return FALSE;
      
      $answer->setMapping($this->mapping);
      $data= $answer->getData();
      
      if (sizeof($data) == 1) return $data[0];
      if (sizeof($data) == 0) return NULL;

      return throw(new IllegalArgumentException(
        'Multiple return values not supported (have '.sizeof($data).')'
      ));
    }
  } implements(__FILE__, 'util.log.Traceable');
?>
