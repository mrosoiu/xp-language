<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'net.xp_framework.unittest.io.collections.AbstractCollectionTest',
    'net.xp_framework.unittest.io.collections.NullFilter',
    'io.collections.iterate.IOCollectionIterator',
    'io.collections.iterate.FilteredIOCollectionIterator',
    'io.collections.iterate.AccessedAfterFilter',
    'io.collections.iterate.AccessedBeforeFilter',
    'io.collections.iterate.CreatedAfterFilter',
    'io.collections.iterate.CreatedBeforeFilter',
    'io.collections.iterate.FilteredIOCollectionIterator',
    'io.collections.iterate.IterationFilter',
    'io.collections.iterate.ModifiedAfterFilter',
    'io.collections.iterate.ModifiedBeforeFilter',
    'io.collections.iterate.NameMatchesFilter',
    'io.collections.iterate.NameEqualsFilter',
    'io.collections.iterate.ExtensionEqualsFilter',
    'io.collections.iterate.SizeBiggerThanFilter',
    'io.collections.iterate.SizeEqualsFilter',
    'io.collections.iterate.SizeSmallerThanFilter',
    'io.collections.iterate.AllOfFilter',
    'io.collections.iterate.AnyOfFilter'
  );

  /**
   * Unit tests for IOCollectionIterator class
   *
   * @see      xp://io.collections.IOCollectionIterator
   * @purpose  Unit test
   */
  class IOCollectionIteratorTest extends AbstractCollectionTest {
    
    /**
     * Test IOCollectionIterator
     *
     * @access  public
     */
    #[@test]
    function iteration() {
      for ($it= &new IOCollectionIterator($this->fixture), $i= 0; $it->hasNext(); $i++) {
        $e= &$it->next();
        $this->assertTrue(is('io.collections.IOElement', $e));
      }
      $this->assertEquals($this->sizes[$this->fixture->getURI()], $i);
    }

    /**
     * Test IOCollectionIterator
     *
     * @access  public
     */
    #[@test]
    function recursiveIteration() {
      for ($it= &new IOCollectionIterator($this->fixture, TRUE), $i= 0; $it->hasNext(); $i++) {
        $e= &$it->next();
        $this->assertTrue(is('io.collections.IOElement', $e));
      }
      $this->assertEquals($this->total, $i);
    }

    /**
     * Helper method
     *
     * @access  protected
     * @param   io.collections.iterate.Filter filter
     * @param   bool recursive default FALSE
     * @return  string[] an array of the elements' URIs
     */
    function filterFixtureWith(&$filter, $recursive= FALSE) {
      $elements= array();
      for (
        $it= &new FilteredIOCollectionIterator($this->fixture, $filter, $recursive);
        $it->hasNext(); 
      ) {
        $e= &$it->next();
        $this->assertTrue(is('io.collections.IOElement', $e));
        $elements[]= $e->getURI();
      }
      return $elements;
    }

    /**
     * Test FilteredIOCollectionIterator
     *
     * @access  public
     */
    #[@test]
    function filteredIteration() {
      $this->assertEquals(
        $this->sizes[$this->fixture->getURI()],
        sizeof($this->filterFixtureWith(new NullFilter(), FALSE))
      );
    }

    /**
     * Test FilteredIOCollectionIterator
     *
     * @access  public
     */
    #[@test]
    function filteredRecursiveIteration() {
      $this->assertEquals(
        $this->total,
        sizeof($this->filterFixtureWith(new NullFilter(), TRUE))
      );
    }

    /**
     * Test NameMatchesFilter
     *
     * @see     xp://io.collections.iterate.NameMatchesFilter
     * @access  public
     */
    #[@test]
    function nameMatches() {
      $this->assertEquals(
        array('first.txt', 'second.txt'), 
        $this->filterFixtureWith(new NameMatchesFilter('/\.txt$/'), FALSE)
      );
    }

    /**
     * Test NameMatchesFilter
     *
     * @see     xp://io.collections.iterate.NameMatchesFilter
     * @access  public
     */
    #[@test]
    function nameMatchesRecursive() {
      $this->assertEquals(
        array('first.txt', 'second.txt', 'sub/IMG_6100.txt'), 
        $this->filterFixtureWith(new NameMatchesFilter('/\.txt$/'), TRUE)
      );
    }

    /**
     * Test NameEqualsFilter
     *
     * @see     xp://io.collections.iterate.NameMatchesFilter
     * @access  public
     */
    #[@test]
    function nameEquals() {
      $this->assertEquals(
        array(), 
        $this->filterFixtureWith(new NameEqualsFilter('__xp__.php'), FALSE)
      );
    }

    /**
     * Test NameEqualsFilter
     *
     * @see     xp://io.collections.iterate.NameMatchesFilter
     * @access  public
     */
    #[@test]
    function nameEqualsRecursive() {
      $this->assertEquals(
        array('sub/sec/__xp__.php'), 
        $this->filterFixtureWith(new NameEqualsFilter('__xp__.php'), TRUE)
      );
    }

    /**
     * Test extensionEqualsFilter
     *
     * @see     xp://io.collections.iterate.extensionMatchesFilter
     * @access  public
     */
    #[@test]
    function extensionEquals() {
      $this->assertEquals(
        array(), 
        $this->filterFixtureWith(new ExtensionEqualsFilter('.php'), FALSE)
      );
    }

    /**
     * Test extensionEqualsFilter
     *
     * @see     xp://io.collections.iterate.extensionMatchesFilter
     * @access  public
     */
    #[@test]
    function extensionEqualsRecursive() {
      $this->assertEquals(
        array('sub/sec/lang.base.php', 'sub/sec/__xp__.php'), 
        $this->filterFixtureWith(new extensionEqualsFilter('.php'), TRUE)
      );
    }
    
    /**
     * Test SizeEqualsFilter
     *
     * @see     xp://io.collections.iterate.SizeEqualsFilter
     * @access  public
     */
    #[@test]
    function zeroBytes() {
      $this->assertEquals(
        array('zerobytes.png'), 
        $this->filterFixtureWith(new SizeEqualsFilter(0), FALSE)
      );
    }

    /**
     * Test SizeBiggerThanFilter
     *
     * @see     xp://io.collections.iterate.SizeBiggerThanFilter
     * @access  public
     */
    #[@test]
    function bigFiles() {
      $this->assertEquals(
        array('sub/IMG_6100.jpg'), 
        $this->filterFixtureWith(new SizeBiggerThanFilter(500000), TRUE)
      );
    }

    /**
     * Test SizeBiggerThanFilter
     *
     * @see     xp://io.collections.iterate.SizeBiggerThanFilter
     * @access  public
     */
    #[@test]
    function smallFiles() {
      $this->assertEquals(
        array('second.txt', 'zerobytes.png'), 
        $this->filterFixtureWith(new SizeSmallerThanFilter(500), TRUE)
      );
    }

    /**
     * Test AccessedAfterFilter
     *
     * @see     xp://io.collections.iterate.AccessedAfterFilter
     * @access  public
     */
    #[@test]
    function accessedAfter() {
      $this->assertEquals(
        array('first.txt', 'second.txt', 'sub/sec/lang.base.php', 'sub/sec/__xp__.php'), 
        $this->filterFixtureWith(new AccessedAfterFilter(new Date('Oct  1  2006')), TRUE)
      );
    }

    /**
     * Test AccessedBeforeFilter
     *
     * @see     xp://io.collections.iterate.AccessedBeforeFilter
     * @access  public
     */
    #[@test]
    function accessedBefore() {
      $this->assertEquals(
        array('third.jpg', 'zerobytes.png'), 
        $this->filterFixtureWith(new AccessedBeforeFilter(new Date('Dec 14  2004')), TRUE)
      );
    }

    /**
     * Test ModifiedAfterFilter
     *
     * @see     xp://io.collections.iterate.ModifiedAfterFilter
     * @access  public
     */
    #[@test]
    function modifiedAfter() {
      $this->assertEquals(
        array('sub/sec/lang.base.php', 'sub/sec/__xp__.php'), 
        $this->filterFixtureWith(new ModifiedAfterFilter(new Date('Oct  7  2006')), TRUE)
      );
    }

    /**
     * Test ModifiedBeforeFilter
     *
     * @see     xp://io.collections.iterate.ModifiedBeforeFilter
     * @access  public
     */
    #[@test]
    function modifiedBefore() {
      $this->assertEquals(
        array('third.jpg', 'zerobytes.png'), 
        $this->filterFixtureWith(new ModifiedBeforeFilter(new Date('Dec 14  2004')), TRUE)
      );
    }

    /**
     * Test CreatedAfterFilter
     *
     * @see     xp://io.collections.iterate.CreatedAfterFilter
     * @access  public
     */
    #[@test]
    function createdAfter() {
      $this->assertEquals(
        array('sub/sec/__xp__.php'), 
        $this->filterFixtureWith(new CreatedAfterFilter(new Date('Jul  1  2006')), TRUE)
      );
    }

    /**
     * Test CreatedBeforeFilter
     *
     * @see     xp://io.collections.iterate.CreatedBeforeFilter
     * @access  public
     */
    #[@test]
    function createdBefore() {
      $this->assertEquals(
        array('sub/sec/lang.base.php'), 
        $this->filterFixtureWith(new CreatedBeforeFilter(new Date('Feb 22  2002')), TRUE)
      );
    }

    /**
     * Test AllOfFilter
     *
     * @see     xp://io.collections.iterate.AllOfFilter
     * @access  public
     */
    #[@test]
    function allOf() {
      $this->assertEquals(
        array('third.jpg'), 
        $this->filterFixtureWith(new AllOfFilter(array(
          new ModifiedBeforeFilter(new Date('Dec 14  2004')),
          new ExtensionEqualsFilter('jpg')
        )), TRUE)
      );
    }

    /**
     * Test AnyOfFilter
     *
     * @see     xp://io.collections.iterate.AnyOfFilter
     * @access  public
     */
    #[@test]
    function anyOf() {
      $this->assertEquals(
        array('first.txt', 'second.txt', 'zerobytes.png', 'sub/IMG_6100.txt'), 
        $this->filterFixtureWith(new AnyOfFilter(array(
          new SizeSmallerThanFilter(500),
          new ExtensionEqualsFilter('txt')
        )), TRUE)
      );
    }
  }
?>
