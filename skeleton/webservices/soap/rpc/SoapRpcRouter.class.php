<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
  uses(
    'scriptlet.rpc.AbstractRpcRouter',
    'webservices.soap.rpc.SoapRpcRequest',
    'webservices.soap.rpc.SoapRpcResponse',
    'webservices.soap.SOAPMessage',
    'webservices.soap.SOAPMapping'
  );

  /**
   * Serves as a working base for SOAP request passed to a CGI
   * executed in an Apache environment.
   *
   * Example:
   * <code>
   *   uses('webservices.soap.rpc.SoapRpcRouter');
   *
   *   $s= &new SoapRpcRouter(new ClassLoader('info.binford6100.webservices'));
   *   try(); {
   *     $s->init();
   *     $response= &$s->process();
   *   } if (catch('HttpScriptletException', $e)) {
   *     // Retrieve standard "Internal Server Error"-Document
   *     $response= &$e->getResponse();
   *   }
   *   $response->sendHeaders();
   *   $response->sendContent();
   *
   *   $s->finalize();
   * </code>
   *
   * Pass the classpath to the handlers to the constructor of this class
   * to where your handlers are. Handlers are the classes that do the
   * work for the requested SOAP-Action.
   *
   * Example: Let's say, the SOAP-Action passed in is Ident#echoStruct, and
   * the constructor was given the classpath info.binford6100.webservices,
   * the rpc router would look for a class with the fully qualified name
   * info.binford6100.webservices.IdentHandler and call it's method echoStruct.
   *
   * @see scriptlet.HttpScriptlet
   */
  class SoapRpcRouter extends AbstractRpcRouter {
    public
      $mapping     = NULL;

    /**
     * Constructor
     *
     * @param   string package
     */
    public function __construct($package) {
      parent::__construct($package);
      $this->mapping= new SOAPMapping();
    }
    
    /**
     * Create a request object.
     *
     * @return  &webservices.soap.rpc.SoapRpcRequest
     */
    public function _request() {
      return new SoapRpcRequest();
    }

    /**
     * Create a response object.
     *
     * @return  &webservices.soap.rpc.SoapRpcResponse
     */
    public function _response() {
      return new SoapRpcResponse();
    }
    
    /**
     * Create message object.
     *
     * @return  &webservices.soap.SOAPMessage
     */
    public function _message() {
      return new SOAPMessage();
    }    

    /**
     * Calls the handler that the action reflects to
     *
     * @param   &webservices.xmlrpc.XmlRpcMessage message object (from request)
     * @return  &mixed result of method call
     * @throws  lang.IllegalArgumentException if there is no such method
     * @throws  lang.IllegalAccessException for non-public methods
     */
    public function callReflectHandler($msg) {
      try {
        $r= parent::callReflectHandler($msg);
      } catch (Throwable $e) {
        throw($e);   // catch/rethrow because of PHP4 limitations
      }
      return array($r);
    }   
  }
?>
