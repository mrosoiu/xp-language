<?php
  uses('net.http.HTTPRequest');
  uses('xml.soap.SOAPMessage');
  
  class SOAPClient extends HTTPRequest {
    var 
      $call,
      $answer;
    
    var
      $action,
      $method= 'ident',
      $data;
    
    var
      $contentType= 'text/xml; charset=iso-8859-1';
    
    function _create() {
      $this->call= new SOAPMessage();
      $this->call->create($this->action, $this->method);
      $this->call->setData($this->data);
    }
    
    function call() {
      if (!isset($this->call)) {
        $this->data= func_get_args();
        $this->_create();
      }
      $this->headers['SOAPAction']= '"'.$this->action.'#'.$this->method.'"';
      
      // POST
      try(); {
        $return= $this->post('#'.XML_DECLARATION.$this->call->getSource(0));
      } if (catch('IOException', $e)) {
        return throw($e->type, $this->request);
      }
      
      // R�ckgabe auswerten
      $this->answer= new SOAPMessage();
      
      if (isset($this->response->ContentType)) {
        @list($type, $charset)= explode('; charset=', $this->response->ContentType);
        if (!empty($charset)) $this->answer->encoding= $charset;
      }
      
      // DEBUG
      // var_dump($this->response);

      # IFDEF PROFILING
      // $start_xml= microtime();
      # ENDIF
      
      $this->answer->action= $this->action;
      try(); {
        $this->answer->fromString($this->response->body);
      } if (catch('Exception', $e)) {
        return throw($e->type, $this->response);
      }
      
      // Nach Fault checken
      $this->fault= NULL;
      if (intval($this->response->HTTPstatus) != 200) {
        $this->fault= $this->answer->getFault();
        return throw(new SoapFaultException($this->fault->faultstring));
      }

      # IFDEF PROFILING
      // $start= microtime();
      # ENDIF

      $data= $this->answer->getData();
      
      # IFDEF PROFILING
      // $stop= microtime();
      //
      // function utime($m) {
      //   list($msec, $sec)= explode(' ', $m);
      //   return $sec+ $msec;
      // }
      //
      // printf("%0.3f sec\n", (utime($start) - utime($start_xml)));
      // printf("%0.3f sec\n", (utime($stop) - utime($start)));
      # ENDIF
      
      return $data;
    }
  }
  
?>
