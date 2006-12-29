<?php
/* This file provides the XMLRPC service sapi for the XP framework
 * 
 * $Id$
 */

  uses('webservices.xmlrpc.rpc.XmlRpcRouter');
  
  define('EPREPEND_IDENTIFIER', "\6100");
  
  // {{{ final class sapi�soap�service
  class sapi�soap�service {
  
    // {{{ internal string fault(&lang.Throwable exception, string code)
    //     Convert an exception to XML
    static function fault($exception, $code) {
      $answer= new XmlRpcMessage();
      $answer->create('Server', 'Error');

      $answer->setFault($code, $exception->toString());
      
      header('Content-type: text/xml; charset=iso-8859-1');
      return (
        $answer->getDeclaration()."\n".
        $answer->getSource(0)
      );      
    }

    // {{{ internal string output(string buf)
    //     Output handler
    static function output($buf) {

      // Check for fatal errors
      if (FALSE !== ($p= strpos($buf, EPREPEND_IDENTIFIER))) {
        return sapi�xmlrpc�service::fault(
          new Error(str_replace(EPREPEND_IDENTIFIER, '', substr($buf, $p))),
          'xp.internalerror'
        );
      }

      return $buf;
    }
    // }}}
    
    // {{{ internal void except(Exception e)
    //     Exception handler
    static function except($e) {
      self::fault(
        $e instanceof XPException ? $e : new XPException($e->getMessage()), 
        xp.'uncaughtexception'
      );
    }
    // }}}
  }
  // }}}
  
  ini_set('html_errors', 0);
  ini_set('display_errors', 1);
  ini_set('error_prepend_string', EPREPEND_IDENTIFIER);

  set_exception_handler(array('sapi�xmlrpc�service', 'except'));
  ob_start(array('sapi�xmlrpc�service', 'output'));
?>
