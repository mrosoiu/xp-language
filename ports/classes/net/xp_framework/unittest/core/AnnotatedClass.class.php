<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Helper class for AnnotationTest
   *
   * @see      xp://net.xp_framework.unittest.core.AnnotationTest
   * @purpose  Helper
   */
  class AnnotatedClass extends Object {

    /**
     * Method annotated with one simple annotation
     *
     * @access  public
     */
    #[@simple]
    function simple() { }

    /**
     * Method annotated with more than one annotation
     *
     * @access  public
     */
    #[@one, @two, @three]
    function multiple() { }

    /**
     * Method annotated with an annotation with a string value
     *
     * @access  public
     */
    #[@strval('String value')]
    function stringValue() { }

    /**
     * Method annotated with an annotation with a hash value containing one
     * key/value pair
     *
     * @access  public
     */
    #[@config(key = 'value')]
    function keyValuePair() { }

    /**
     * Unittest method annotated with @test, @ignore and @limit
     *
     * @access  public
     */
    #[@test, @ignore, @limit(time = 0.1, memory = 100)]
    function testMethod() { }

    /**
     * Method annotated with an annotation with a hash value containing 
     * multiple key/value pairs
     *
     * @access  public
     */
    #[@config(key = 'value', times= 5, disabled= FALSE, null = NULL, list= array(1, 2))]
    function keyValuePairs() { }

    /**
     * Method annotated with a multi-line annotation
     *
     * @access  public
     */
    #[@interceptors(classes= array(
    #  'net.xp_framework.unittest.core.FirstInterceptor',
    #  'net.xp_framework.unittest.core.SecondInterceptor',
    #))]
    function multiLine() { }

    /**
     * Method annotated with a simple xpath expression
     *
     * @access  public
     */
    #[@fromXml(xpath= '/parent/child/@attribute')]
    function simpleXPath() { }
    /**
     * Method annotated with a complex xpath expression
     *
     * @access  public
     */
    #[@fromXml(xpath= '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"')]
    function complexXPath() { }
  }
?>
