<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'net.xp_framework.unittest.reflection.TestClass'
  );

  /**
   * Test the XP reflection API
   *
   * @see      xp://lang.reflect.Argument
   * @purpose  Testcase
   */
  class ArgumentTest extends TestCase {
    public
      $class  = NULL;
  
    /**
     * Setup method
     *
     */
    public function setUp() {
      $this->class= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
    }

    /**
     * Tests the constructor's argument
     *
     */
    #[@test]
    public function constructorArgument() {
      with ($argument= $this->class->getConstructor()->getArgument(0)); {
        $this->assertEquals('mixed', $argument->getType());
        $this->assertEquals('in', $argument->getName());
        $this->assertTrue($argument->isOptional());
        $this->assertEquals('NULL', $argument->getDefault());
      }
    }

    /**
     * Tests the setDate() method's argument
     *
     */
    #[@test]
    public function dateSetterArgument() {
      with ($argument= $this->class->getMethod('setDate')->getArgument(0)); {
        $this->assertEquals('util.Date', $argument->getType());
        $this->assertEquals('date', $argument->getName());
        $this->assertFalse($argument->isOptional());
        $this->assertFalse($argument->getDefault());
      }
    }
  }
?>
