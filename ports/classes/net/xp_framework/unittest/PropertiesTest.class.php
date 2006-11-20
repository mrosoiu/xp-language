<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'util.Properties',
    'util.Hashmap'
  );

  /**
   * Testcase for util.Properties class.
   *
   * @see      xp://util.Properties
   * @purpose  Testcase
   */
  class PropertiesTest extends TestCase {
  
  
    /**
     * Test construction.
     *
     * @access  public
     */
    #[@test]
    function testFromString() {
      $p= &Properties::fromString('');
    }
    
    /**
     * Test simple reading of values.
     *
     * @access  public
     */
    #[@test]
    function basicTest() {
      $p= &Properties::fromString('
[section]
string="value1"
int=2
bool=0
      ');
      
      $this->assertEquals('value1', $p->readString('section', 'string'));
      $this->assertEquals(2, $p->readInteger('section', 'int'));
      $this->assertEquals(FALSE, $p->readBool('Section', 'bool'));
    }
    
    /**
     * Test string reading
     *
     * @access  public
     */
    #[@test]
    function readString() {
      $p= &Properties::fromString('
[section]
string1=string
string2="string"
      ');
      
      $this->assertEquals('string', $p->readString('section', 'string1'));
      $this->assertEquals('string', $p->readString('section', 'string2'));
    }    
    
    /**
     * Test simple reading of arrays
     *
     * @access  public
     */
    #[@test]
    function readArray() {
      $p= &Properties::fromString('
[section]
array="foo|bar|baz"
      ');
      $this->assertEquals(array('foo', 'bar', 'baz'), $p->readArray('section', 'array'));
    }
    
    /**
     * Test simple reading of hashes
     *
     * @access  public
     */
    #[@test]
    function readHash() {
      $p= &Properties::fromString('
[section]
hash="foo:bar|bar:foo"
      ');
      $this->assertEquals(
        new HashMap(array('foo' => 'bar', 'bar' => 'foo')),
        $p->readHash('section', 'hash')
      );
    }   
    
    /**
     * Test simple reading of range
     *
     * @access  public
     */
    #[@test]
    function readRange() {
      $p= &Properties::fromString('
[section]
range="1..5"
      ');
      $this->assertEquals(
        range(1, 5),
        $p->readRange('section', 'range')
      );
    }
    
    /**
     * Test simple reading of integer
     *
     * @access  public
     */
    #[@test]
    function readInteger() {
      $p= &Properties::fromString('
[section]
int1=1
int2=0
int3=-5
      ');
      $this->assertEquals(1, $p->readInteger('section', 'int1'));
      $this->assertEquals(0, $p->readInteger('section', 'int2'));
      $this->assertEquals(-5, $p->readInteger('section', 'int3'));
    }
    
    /**
     * Test simple reading of float
     *
     * @access  public
     */
    #[@test]
    function readFloat() {
      $p= &Properties::fromString('
[section]
float1=1
float2=0
float3=0.5
float4=-5.0
      ');
      $this->assertEquals(1.0, $p->readFloat('section', 'float1'));
      $this->assertEquals(0.0, $p->readFloat('section', 'float2'));
      $this->assertEquals(0.5, $p->readFloat('section', 'float3'));
      $this->assertEquals(-5.0, $p->readFloat('section', 'float4'));
    }
    
    /**
     * Tests reading of a boolean
     *
     * @access  public
     */
    #[@test]
    function readBool() {
     $p= &Properties::fromString('
[section]
bool1=1
bool5=0
      ');
      $this->assertTrue($p->readBool('section', 'bool1'));
      $this->assertFalse($p->readBool('section', 'bool2'));
    }
    
    /**
     * Test simple reading of section
     *
     * @access  public
     */
    #[@test]
    function hasSection() {
      $p= &Properties::fromString('
[section]
foo=bar
      ');
      
      $this->assertTrue($p->hasSection('section'));
      $this->assertFalse($p->hasSection('nonexistant'));
    }

    /**
     * Test iterating over sections
     *
     * @access  public
     */
    #[@test]
    function iterateSections() {
     $p= &Properties::fromString('
[section]
foo=bar

[next]
foo=bar

[empty]

[final]
foo=bar
      ');
      
      $this->assertEquals('section', $p->getFirstSection());
      $this->assertEquals('next', $p->getNextSection());
      $this->assertEquals('empty', $p->getNextSection());     
      $this->assertEquals('final', $p->getNextSection());
    }
  }
?>
