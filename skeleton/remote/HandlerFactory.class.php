<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'remote.protocol.ProtocolHandler',
    'remote.protocol.UnknownProtocolException',
    'remote.protocol.XpProtocolHandler'
  );

  /**
   * Handler factory implementation
   *
   * @see      xp://remote.protocol.XpProtocolHandler
   * @purpose  Factory
   */
  class HandlerFactory extends Object {
    var
      $handlers= array();

    /**
     * Static initializer. Registers the default protocol "xp" with this
     * factory.
     *
     * @model   static
     * @access  public
     */
    function __static() {
      $self= &HandlerFactory::getInstance();
      $self->register('xp', XPClass::forName('remote.protocol.XpProtocolHandler'));
    }
    
    /**
     * Retrieve the HandlerFactory instance
     *
     * @model   static
     * @access  public
     * @return  &remote.HandlerFactory
     */
    function &getInstance() {
      static $instance= NULL;
      
      if (!isset($instance)) $instance= new HandlerFactory();
      return $instance;
    }

    /**
     * Registers protocol handler for a specified type
     *
     * @access  public
     * @param   string type
     * @param   &lang.XPClass<remote.protocol.ProtocolHandler> handler class
     * @return  &lang.XPClass<remote.protocol.ProtocolHandler>
     */
    function &register($type, &$handler) {
      $this->handlers[$type]= &$handler;
      return $handler;
    }
  
    /**
     * Retrieve a handler for a given scheme
     *
     * @model   static
     * @access  public
     * @param   string type
     * @return  &lang.XPClass<remote.protocol.ProtocolHandler>
     * @throws  remote.protocol.UnknownProtocolException
     */
    function &handlerFor($type) {
      $self= &HandlerFactory::getInstance();
      if (!isset($self->handlers[$type])) {
        return throw(new UnknownProtocolException($type));
      }
      return $self->handlers[$type];
    }
  }
?>
