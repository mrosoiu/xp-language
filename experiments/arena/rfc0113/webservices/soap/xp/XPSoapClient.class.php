<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'webservices.soap.SOAPMessage',
    'xml.QName',
    'webservices.soap.Parameter',
    'webservices.soap.xp.SOAPMapping',
    'util.log.Traceable',
    'webservices.soap.transport.SOAPHTTPTransport'
  );
  
  /**
   * Basic SOAP-Client
   *
   * Example:
   * <code>
   *   $s= new SOAPClient(new SOAPHTTPTransport($url), $urn);
   *   try {
   *     $return= $s->invoke($methods, $paramaters);
   *   } catch (XPException $e) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *   var_dump($return);
   * </code>
   * 
   * @test     xp://net.xp_framework.unittest.soap.SoapClientTest
   * @purpose  Generic SOAP client base class
   */
  class XPSoapClient extends Object implements Traceable {
    public 
      $encoding           = 'iso-8859-1',
      $transport          = NULL,
      $action             = '',
      $targetNamespace    = NULL,
      $mapping            = NULL,
      $headers            = array();
    
    /**
     * Constructor
     *
     * @param   webservices.soap.transport.SOAPTransport transport a SOAP transport
     * @param   string action Action
     * @param   string targetNamespace default NULL
     */
    public function __construct($url, $action) {
      $this->transport= new SOAPHTTPTransport($url);
      $this->action= $action;
      $this->targetNamespace= NULL;
      $this->mapping= new SOAPMapping();
    }

    /**
     * Set TargetNamespace
     *
     * @param   string targetNamespace
     */
    public function setTargetNamespace($targetNamespace= NULL) {
      $this->targetNamespace= $targetNamespace;
    }

    /**
     * Set encoding
     *
     * @param   string encoding either utf-8 oder iso-8859-1
     */
    public function setEncoding($encoding) {
      $this->encoding= $encoding;
    }

    /**
     * Set trace for debugging
     *
     * @param   util.log.LogCategory cat
     */
    public function setTrace($cat) {
      $this->transport->setTrace($cat);
    }

    /**
     * (Insert method's description here)
     *
     * @throws lang.MethodNotImplementedException  
     */
    public function setWsdl() {
      throw new MethodNotImplementedException ('XPSoapClient does not support WSDL-Mode');
    }
    /**
     * Register mapping for a qname to a class object
     *
     * @param   xml.QName qname
     * @param   lang.XPClass class
     */
    public function registerMapping($qname, $class) {
      $this->mapping->registerMapping($qname, $class);
    }
    
    /**
     * Add a header
     *
     * @param   webservices.soap.SOAPHeader header
     * @return  webservices.soap.SOAPHeader the header added
     */
    public function addHeader($header) {
      $this->headers[]= $header;
      return $header;
    }
    
    /**
     * Invoke method call
     *
     * @param   string method name
     * @param   mixed vars
     * @return  mixed answer
     * @throws  lang.IllegalArgumentException
     * @throws  webservices.soap.SOAPFaultException
     */
    public function invoke() {
      if (!is('SOAPTransport', $this->transport)) throw new IllegalArgumentException(
        'Transport must be a webservices.soap.transport.SOAPTransport'
      );
      
      $args= func_get_args();
      
      $message= new SOAPMessage();
      $message->setEncoding($this->encoding);
      $message->createCall($this->action, array_shift($args), $this->targetNamespace, $this->headers);
      $message->setMapping($this->mapping);
      $message->setData($args);

      // Send
      if (FALSE == ($response= $this->transport->send($message))) return FALSE;
      
      // Response
      if (FALSE == ($answer= $this->transport->retrieve($response))) return FALSE;
      
      $answer->setMapping($this->mapping);
      $data= $answer->getData();
      
      if (sizeof($data) == 1) return $data[0];
      if (sizeof($data) == 0) return NULL;

      throw new IllegalArgumentException(
        'Multiple return values not supported (have '.sizeof($data).')'
      );
    }
  } 
?>
