<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'unittest.TestCase',
    'unittest.TestSuite',
    'net.xp_framework.unittest.tests.SimpleTestCase'
  );

  /**
   * Test TestSuite class methods
   *
   * @see      xp://unittest.TestSuite
   * @purpose  Unit Test
   */
  class SuiteTest extends TestCase {
    public
      $suite= NULL;
      
    /**
     * Setup method. Creates a new test suite.
     *
     */
    public function setUp() {
      $this->suite= new TestSuite();
    }

    /**
     * Tests a test suite is initially empty
     *
     */    
    #[@test]
    public function initallyEmpty() {
      $this->assertEquals(0, $this->suite->numTests());
    }    

    /**
     * Tests adding a test
     *
     */    
    #[@test]
    public function addingATest() {
      $this->suite->addTest($this);
      $this->assertEquals(1, $this->suite->numTests());
    }    

    /**
     * Tests adding a test
     *
     */    
    #[@test, @expect('lang.IllegalArgumentException')]
    public function addNonTest() {
      $this->suite->addTest(new Object());
    }    

    /**
     * Tests adding a test class
     *
     */    
    #[@test]
    public function addingATestClass() {
      $ignored= $this->suite->addTestClass(XPClass::forName('SimpleTestCase'));
      $this->assertEmpty($ignored);
      for ($i= 0, $s= $this->suite->numTests(); $i < $s; $i++) {
        $this->assertSubclass($this->suite->testAt($i), 'unittest.TestCase');
      }
    }    

    /**
     * Tests adding a test class
     *
     */    
    #[@test, @expect('lang.IllegalArgumentException')]
    public function addingANonTestClass() {
      $this->suite->addTestClass(XPClass::forName('Object'));
    }    

    /**
     * Tests clearing tests
     *
     */    
    #[@test]
    public function clearingTests() {
      $this->suite->addTest($this);
      $this->assertEquals(1, $this->suite->numTests());
      $this->suite->clearTests();
      $this->assertEquals(0, $this->suite->numTests());
    }
    
    /**
     * Tests running a single test
     *
     */    
    #[@test]
    public function runningASingleSucceedingTest() {
      $r= $this->suite->runTest(new SimpleTestCase('succeeds'));
      $this->assertClass($r, 'unittest.TestResult') &&
      $this->assertEquals(1, $r->runCount(), 'runCount') &&
      $this->assertEquals(1, $r->successCount(), 'successCount') &&
      $this->assertEquals(0, $r->failureCount(), 'failureCount') &&
      $this->assertEquals(0, $r->skipCount(), 'skipCount');
    }    

    /**
     * Tests running a single test
     *
     */    
    #[@test]
    public function runningASingleFailingTest() {
      $r= $this->suite->runTest(new SimpleTestCase('fails'));
      $this->assertClass($r, 'unittest.TestResult') &&
      $this->assertEquals(1, $r->runCount(), 'runCount') &&
      $this->assertEquals(0, $r->successCount(), 'successCount') &&
      $this->assertEquals(1, $r->failureCount(), 'failureCount') &&
      $this->assertEquals(0, $r->skipCount(), 'skipCount');
    }    
  }
?>
