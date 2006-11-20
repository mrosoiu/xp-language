<?php
/* This file provides the SOAP service sapi for the XP framework
 * 
 * $Id$
 */

  uses('webservices.soap.rpc.SoapRpcRouter');
  
  define('EPREPEND_IDENTIFIER', "\6100");
  
  // {{{ final class sapi�soap�service
  class sapi�soap�service {
  
    // {{{ internal string fault(&lang.Throwable exception, string code)
    //     Convert an exception to XML
    function fault(&$exception, $code) {
      $answer= &new SOAPMessage();
      $answer->create();

      foreach ($exception->getStackTrace() as $element) {
        $stacktrace[]= $element->toString();
      }

      $answer->setFault(
        $code,
        $exception->getMessage(),
        getenv('SERVER_NAME').':'.getenv('SERVER_PORT'),
        $stacktrace
      );
      
      header('Content-type: text/xml; charset=iso-8859-1');
      return (
        $answer->getDeclaration()."\n".
        $answer->getSource(0)
      );      
    }

    // {{{ internal string output(string buf)
    //     Output handler
    function output($buf) {

      // Check for fatal errors
      if (FALSE !== ($p= strpos($buf, EPREPEND_IDENTIFIER))) {
        return sapi�soap�service::fault(
          new Error(str_replace(EPREPEND_IDENTIFIER, '', substr($buf, $p))),
          'xp.internalerror'
        );
      }

      // Check for uncaught exceptions
      if ($exceptions= &xp::registry('exceptions')) {
        return sapi�soap�service::fault(
          $exceptions[key($exceptions)],
          'xp.uncaughtexception'
        );
      }

      return $buf;
    }
    // }}}
    
  }
  // }}}
  
  ini_set('html_errors', 0);
  ini_set('display_errors', 1);
  ini_set('error_prepend_string', EPREPEND_IDENTIFIER);

  ob_start(array('sapi�soap�service', 'output'));
?>
