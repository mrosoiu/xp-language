<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('webservices.json.rpc.JsonMessage');

  /**
   * JSON request message
   *
   * @see      http://json-rpc.org
   * @purpose  Wrap JSON request message
   */
  class JsonRequestMessage extends JsonMessage {
  
    /**
     * Create message from string representation
     *
     * @model   static
     * @access  public
     * @param   string string
     * @return  &webservices.json.rpc.JsonRequestMessage
     */
    function &fromString($string) {
      $decoder= &JsonFactory::create();

      $msg= &new JsonRequestMessage();
      $data= $decoder->decode($string);

      $msg->data= $data;
      $msg->id= $data['id'];
      
      list($cn, $method)= explode('.', $data['method']);
      $msg->setHandlerClass($cn);
      $msg->setMethod($method);
      
      return $msg;
    }
    
    /**
     * Create new message
     *
     * @access  public
     * @param   string method
     * @param   int id
     */
    function create($method= NULL, $id= NULL) {
      $this->method= $method;
      $this->id= $id;
    }
    
    /**
     * Set the data for the message
     *
     * @access  public
     * @param   mixed data
     */
    function setData($data) {
      $this->data= array(
        'method'  => $this->method,
        'params'  => (array)$data,
        'id'      => $this->id
      );
    }
    
    /**
     * Get data
     *
     * @access  public 
     * @return  mixed
     */
    function &getData() {
      return $this->data['params'];
    }    
  }
?>
