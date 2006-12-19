<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.types.Byte', 'remote.protocol.SerializerMapping');

  /**
   * Mapping for lang.types.Byte
   *
   * @see      xp://remote.protocol.Serializer
   * @purpose  Mapping
   */
  class ByteMapping extends Object implements SerializerMapping {

    /**
     * Returns a value for the given serialized string
     *
     * @access  public
     * @param   &server.protocol.Serializer serializer
     * @param   &remote.protocol.SerializedData serialized
     * @param   array<string, mixed> context default array()
     * @return  &mixed
     */
    public function &valueOf(&$serializer, &$serialized, $context= array()) {
      $value= new Byte($serialized->consumeWord());
      return $value;
    }

    /**
     * Returns an on-the-wire representation of the given value
     *
     * @access  public
     * @param   &server.protocol.Serializer serializer
     * @param   &lang.Object value
     * @param   array<string, mixed> context default array()
     * @return  string
     */
    public function representationOf(&$serializer, &$value, $context= array()) {
      return 'B:'.$value->value.';';
    }
    
    /**
     * Return XPClass object of class supported by this mapping
     *
     * @access  public
     * @return  &lang.XPClass
     */
    public function &handledClass() {
      return XPClass::forName('lang.types.Byte');
    }
  } 
?>
