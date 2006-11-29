<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Mapping for lang.StackTraceElement
   *
   * @see      xp://remote.protocol.Serializer
   * @purpose  Mapping
   */
  class StackTraceElementMapping extends Object {

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
      $size= $serialized->consumeSize();
      $details= array();
      $serialized->offset++;  // Opening "{"
      for ($i= 0; $i < $size; $i++) {
        $detail= $serializer->valueOf($serialized, $context);
        $details[$detail]= $serializer->valueOf($serialized, $context);
      }
      $serialized->offset++;  // Closing "}"
      
      $value= &new StackTraceElement(
        $details['file'],
        $details['class'],
        $details['method'],
        $details['line'],
        array(),
        NULL
      );
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
    function representationOf(&$serializer, &$value, $context= array()) {
      return 't:4:{'.
        's:4:"file";'.$serializer->representationOf($value->file).
        's:5:"class";'.$serializer->representationOf($value->class).
        's:6:"method";'.$serializer->representationOf($value->method).
        's:4:"line";'.$serializer->representationOf($value->line).
      '}';
    }
    
    /**
     * Return XPClass object of class supported by this mapping
     *
     * @access  public
     * @return  &lang.XPClass
     */
    function &handledClass() {
      return XPClass::forName('lang.StackTraceElement');
    }
  } implements(__FILE__, 'remote.protocol.SerializerMapping');
?>
