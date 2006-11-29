<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'unittest.TestCase',
    'util.Hashmap',
    'util.Comparator'
  );

  /**
   * Test Hashmap class
   *
   * @see      xp://util.Hashmap
   * @purpose  Unit Test
   */
  class HashmapTest extends TestCase {
    var
      $map= NULL;
    
    /**
     * Setup method. Creates the map member
     *
     * @access  public
     */
    function setUp() {
      $this->map= &new Hashmap();
    }
        
    /**
     * Tests the map is initially empty
     *
     * @access  public
     */
    #[@test]
    function initiallyEmpty() {
      $this->assertTrue($this->map->isEmpty());
    }

    /**
     * Tests map equals its clone
     *
     * @access  public
     */
    #[@test]
    function equalsClone() {
      $this->map->put('color', 'green');
      $this->assertTrue($this->map->equals(clone($this->map)));
    }
 
    /**
     * Tests map equals another map with the same contents
     *
     * @access  public
     */
    #[@test]
    function equalsOtherMapWithSameContents() {
      $other= &new HashMap();
      $this->map->put('color', 'green');
      $other->put('color', 'green');
      $this->assertTrue($this->map->equals($other));
    }

    /**
     * Tests map does not equal map with different contents
     *
     * @access  public
     */
    #[@test]
    function doesNotEqualMapWithDifferentContents() {
      $other= &new HashMap();
      $this->map->put('color', 'green');
      $other->put('color', 'pink');
      $this->assertFalse($this->map->equals($other));
    }
   
    /**
     * Tests put()
     *
     * @access  public
     */
    #[@test]
    function put() {
      $this->map->put('color', 'green');
      $this->assertFalse($this->map->isEmpty());
      $this->assertEquals(1, $this->map->size());
    }

    /**
     * Tests get()
     *
     * @access  public
     */
    #[@test]
    function get() {
      $this->map->put('key', 'value');
      $this->assertEquals('value', $this->map->get('key'));
    }

    /**
     * Tests remove()
     *
     * @access  public
     */
    #[@test]
    function remove() {
      $this->map->put('key', 'value');
      $this->map->remove('key');
      $this->assertTrue($this->map->isEmpty());
    }

    /**
     * Tests get() returns NULL if the list is empty
     *
     * @access  public
     */
    #[@test]
    function getReturnsNullOnEmptyList() {
      $this->assertTrue($this->map->isEmpty());
      $this->assertNull($this->map->get('key'));
    }

    /**
     * Tests containsKey() method
     *
     * @access  public
     */
    #[@test]
    function containsKey() {
      $this->map->put('key', 'value');
      $this->assertTrue($this->map->containsKey('key'));
      $this->assertFalse($this->map->containsKey('non-existant-key'));
    }
    
    /**
     * Helper method for merge* test methods
     *
     * @access  protected
     * @param   bool recursive default FALSE Merge hashmaps recursively
     * @param   array<mixed, mixed> toMerge
     * @param   array<mixed, mixed> expect
     */
    function testMerge($recursive, $toMerge, $expect) {
      $this->map->put('color', 'red');
      $this->map->put('count', 5);

      $this->map->merge($toMerge, $recursive);
      $this->assertEquals($expect, $this->map->toArray());
    }

    /**
     * Tests merge() method
     *
     * @access  public
     */
    #[@test]
    function merge() {
      $this->testMerge(
        FALSE,
        array('color' => 'green', 'key' => 'value'),
        array('color' => 'red', 'key' => 'value', 'count' => 5)
      );
    }

    /**
     * Tests merge() method, using recursive behaviour.
     *
     * @access  public
     */
    #[@test]
    function mergeRecursive() {
      $this->testMerge(
        TRUE,
        array('color' => 'green', 'key' => 'value'),
        array('color' => array('green', 'red'), 'key' => 'value', 'count' => 5)
      );
    }
    
    /**
     * Tests merge() method when given anything besides an array or a Hashmap
     *
     * @access  public
     */
    #[@test, @expect('lang.IllegalArgumentException')]    
    function mergeWithIllegalArgument() {
      $this->map->merge(new Object());
    }

    /**
     * Tests swap() method
     *
     * @access  public
     */
    #[@test]
    function swap() {
      $this->map->put('color', 'purple');
      $this->map->put('price', 15);

      $this->assertTrue($this->map->swap('color', 'price'));
      $this->assertEquals(
        array('color' => 15, 'price' => 'purple'), 
        $this->map->toArray()
      );
    }

    /**
     * Tests swap() method
     *
     * @access  public
     */
    #[@test]
    function swapNonExistantKeys() {
      $this->map->put('color', 'purple');
      $this->map->put('price', 15);

      $this->assertFalse($this->map->swap('color', 'non-existant-key'));
      $this->assertFalse($this->map->swap('non-existant-key', 'color'));
    }

    /**
     * Tests flip() method
     *
     * @access  public
     */
    #[@test]
    function flip() {
      $this->map->put('color', 'purple');
      $this->map->put('price', 15);

      $this->assertTrue($this->map->flip());
      $this->assertEquals(
        array('purple' => 'color', 15 => 'price'), 
        $this->map->toArray()
      );
    }

    /**
     * Tests clear() method
     *
     * @access  public
     */
    #[@test]
    function clear() {
      $this->map->put('key', 'value');
      $this->map->clear();
      $this->assertTrue($this->map->isEmpty());
    }

    /**
     * Tests containsValue() method
     *
     * @access  public
     */
    #[@test]
    function containsValue() {
      $this->map->put('key', 'value');
      $this->assertTrue($this->map->containsValue($v= 'value'));
      $this->assertFalse($this->map->containsValue($v= 'non-existant-value'));
    }

    /**
     * Tests values() method
     *
     * @access  public
     */
    #[@test]
    function keys() {
      $this->map->put('one', 1);
      $this->map->put('two', 2);
      $this->assertEquals(array('one', 'two'), $this->map->keys());
    }

    /**
     * Tests values() method
     *
     * @access  public
     */
    #[@test]
    function values() {
      $this->map->put('one', 1);
      $this->map->put('two', 2);
      $this->assertEquals(array(1, 2), $this->map->values());
    }

    /**
     * Tests filter() method
     *
     * @access  public
     */
    #[@test]
    function filter() {
      $this->map->put('one', 1);
      $this->map->put('two', 2);
      $this->map->put('three', 3);
      $this->map->put('four', 4);
      $this->map->filter(create_function('$v', 'return 1 == $v % 2;'));
      $this->assertEquals(array('one' => 1, 'three' => 3), $this->map->toArray());
    }
 
    /**
     * Tests sort() method
     *
     * @access  public
     */
    #[@test]
    function sort() {
      $this->map->put('two', 2);
      $this->map->put('one', 1);
      $this->map->sort(SORT_NUMERIC);

      $this->assertEquals(
        array('one' => 1, 'two' => 2), 
        $this->map->toArray()
      );
    }

    /**
     * Tests rsort() method
     *
     * @access  public
     */
    #[@test]
    function rsort() {
      $this->map->put('one', 1);
      $this->map->put('two', 2);
      $this->map->rsort(SORT_NUMERIC);

      $this->assertEquals(
        array('two' => 2, 'one' => 1), 
        $this->map->toArray()
      );
    }

    /**
     * Tests usort() method
     *
     * @access  public
     */
    #[@test]
    function usort() {
      $this->map->put('one', 'One');
      $this->map->put('two', 'two');
      $this->map->put('eins', 'one');

      $this->map->usort(newinstance('util.Comparator', array(), '{
        function compare($a, $b) { 
          return strcasecmp($a, $b); 
        }
      }'));
      $this->assertEquals(
        array('one' => 'One', 'eins' => 'one', 'two' => 'two'), 
        $this->map->toArray()
      );
    }
  }
?>
