<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.types.ArrayList');

  /**
   * Mapping for strictly numeric arrays
   *
   * @see      xp://remote.protocol.Serializer
   * @purpose  Mapping
   */
  class ArrayListMapping extends Object {

    /**
     * Returns a value for the given serialized string
     *
     * @access  public
     * @param   &server.protocol.Serializer serializer
     * @param   &remote.protocol.SerializedData serialized
     * @param   array<string, mixed> context default array()
     * @return  &mixed
     */
    function &valueOf(&$serializer, &$serialized, $context= array()) {
      $a= &new ArrayList();
      $size= $serialized->consumeSize();
      
      $serialized->offset++;  // Opening "{"
      for ($i= 0; $i < $size; $i++) {
        $a->values[$i]= &$serializer->valueOf($serialized, $context);
      }
      $serialized->offset++;  // Closing "}"
      return $a;
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
    function representationOf(&$serializer, &$value, $context= array()) {
      $s= 'A:'.sizeof($value->values).':{';
      foreach (array_keys($value->values) as $key) {
        $s.= $serializer->representationOf($value->values[$key], $context);
      }
      return $s.'}';
    }
    
    /**
     * Return XPClass object of class supported by this mapping
     *
     * @access  public
     * @return  &lang.XPClass
     */
    function &handledClass() {
      return XPClass::forName('lang.types.ArrayList');
    }
  } implements(__FILE__, 'remote.protocol.SerializerMapping');
?>
