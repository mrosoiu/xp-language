<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.profiling.unittest.TestCase', 'net.xp_framework.unittest.core.AnnotatedClass');

  /**
   * Tests the XP Framework's annotations
   *
   * @see      rfc://0016
   * @purpose  Testcase
   */
  class AnnotationTest extends TestCase {
    public
      $class = NULL;

    /**
     * Setup method. .
     *
     * @access  public
     */
    public function setUp() {
      $this->class= &XPClass::forName('net.xp_framework.unittest.core.AnnotatedClass');
    }

    /**
     * Helper method to return whether a specified annotation exists
     *
     * @access  protected
     * @param   string method
     * @param   string annotation
     * @return  bool
     */
    public function annotationExists($method, $annotation) {
      $method= &$this->class->getMethod($method);
      return $method->hasAnnotation($annotation);
    }

    /**
     * Helper method to get an annotation of a specified method
     *
     * @access  protected
     * @param   string method
     * @param   string annotation
     * @return  mixed annotation value
     */
    public function methodAnnotation($method, $annotation) {
      $method= &$this->class->getMethod($method);
      return $method->getAnnotation($annotation);
    }

    /**
     * Tests method with a simple annotation without a value exists
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#simple
     * @access  public
     */
    #[@test]
    public function simpleAnnotationExists() {
      $this->assertTrue($this->annotationExists('simple', 'simple'));
    }
    
    /**
     * Tests getAnnotation() returns NULL for simple annotations without
     * any value.,
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#simple
     * @access  public
     */
    #[@test]
    public function simpleAnnotationValue() {
      $this->assertEquals(NULL, $this->methodAnnotation('simple', 'simple'));
    }

    /**
     * Tests method with multiple annotations
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#multiple
     * @access  public
     */
    #[@test]
    public function multipleAnnotationsExist() {
      foreach (array('one', 'two', 'three') as $annotation) {
        $this->assertTrue($this->annotationExists('multiple', $annotation), $annotation);
      }
    }

    /**
     * Tests method with multiple annotations
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#multiple
     * @access  public
     */
    #[@test]
    public function multipleAnnotationsReturnedAsList() {
      $method= &$this->class->getMethod('multiple');
      $this->assertEquals(
        array('one' => NULL, 'two' => NULL, 'three' => NULL),
        $method->getAnnotations()
      );
    }

    /**
     * Tests getAnnotation() returns the string associated with the 
     * annotation.
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#stringValue
     * @access  public
     */
    #[@test]
    public function stringAnnotationValue() {
      $this->assertEquals('String value', $this->methodAnnotation('stringValue', 'strval'));
    }

    /**
     * Tests getAnnotation() returns the string associated with the 
     * annotation.
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#keyValuePair
     * @access  public
     */
    #[@test]
    public function keyValuePairAnnotationValue() {
      $this->assertEquals(array('key' => 'value'), $this->methodAnnotation('keyValuePair', 'config'));
    }

    /**
     * Tests unittest annotations
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#testMethod
     * @access  public
     */
    #[@test]
    public function testMethod() {
      $m= &$this->class->getMethod('testMethod');
      $this->assertTrue($m->hasAnnotation('test'));
      $this->assertTrue($m->hasAnnotation('ignore'));
      $this->assertEquals(0.1, $m->getAnnotation('limit', 'time'));
      $this->assertEquals(100, $m->getAnnotation('limit', 'memory'));
      $this->assertEquals(
        array('time' => 0.1, 'memory' => 100), 
        $m->getAnnotation('limit')
      );
    }

    /**
     * Tests getAnnotation() returns the string associated with the 
     * annotation.
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#keyValuePairs
     * @access  public
     */
    #[@test]
    public function keyValuePairsAnnotationValue() {
      $this->assertEquals(
        array('key' => 'value', 'times' => 5, 'disabled' => FALSE, 'null' => NULL, 'list' => array(1, 2)), 
        $this->methodAnnotation('keyValuePairs', 'config')
      );
    }

    /**
     * Tests multi-line annotations
     *
     * @see     xp://net.xp_framework.unittest.core.AnnotatedClass#multiLine
     * @access  public
     */
    #[@test]
    public function multiLineAnnotation() {
      $this->assertEquals(array('classes' => array(
        'net.xp_framework.unittest.core.FirstInterceptor',
        'net.xp_framework.unittest.core.SecondInterceptor',
      )), $this->methodAnnotation('multiLine', 'interceptors'));
    }
  }
?>
