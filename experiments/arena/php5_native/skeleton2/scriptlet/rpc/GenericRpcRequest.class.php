<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.rpc.AbstractRpcRequest');

  /**
   * Generic RPC request
   *
   * Instead of deriving the AbstractRpcRequest in every new
   * implementation, you can use this generic RPC request which
   * must be given a callback class (usually the RpcRouter) which
   * then can execute the actions.
   *
   * @see      xp://scriptlet.rpc.AbstractRpcRouter
   * @purpose  Generic RPC request
   */
  class GenericRpcRequest extends AbstractRpcRequest {
    public
      $_cb=   NULL;
    
    /**
     * Set callback object.
     *
     * @access  public
     * @param   &mixed object
     */
    public function setCallback(&$object) {
      $this->_cb= &$object;
    }
    
    /**
     * Create message from request
     *
     * @access  public
     * @return  &scriptlet.rpc.AbstractRpcMessage
     */
    public function &getMessage() {
      return $this->_cb->getMessage($this);
    }
    
    /**
     * Determine encoding.
     *
     * @access  public
     * @return  string
     */
    public function getEncoding() {
      if (method_exists($this->_cb, 'getEncoding')) return $this->_cb->getEncoding($this);
      return parent::getEncoding();
    }
  }
?>
