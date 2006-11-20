<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'unittest.TestCase',
    'net.xp_framework.unittest.core.Destroyable'
  );

  /**
   * Tests destructor functionality
   *
   * @purpose  Testcase
   */
  class DestructorTest extends TestCase {
    var
      $destroyed   = array(),
      $destroyable = NULL;
      
    /**
     * Callback for Destroyable class
     *
     * @access  public
     * @param   &lang.Object object
     */
    function onDestruction(&$object) {
      $this->destroyed[$object->hashCode()]++;
    }
    
    /**
     * Setup method. Creates the destroyable member and sets its 
     * callback to this test.
     *
     * @access  public
     */
    function setUp() {
      $this->destroyable= &new Destroyable();
      $this->destroyable->setCallback($this);
      $this->destroyed[$this->destroyable->hashCode()]= 0;
    }

    /**
     * Tests delete() function calls destructor
     *
     * @access  public
     */
    #[@test]
    function deleteCallsDestructor() {
      $hash= $this->destroyable->hashCode();
      delete($this->destroyable);
      $this->assertNull($this->destroyable);
      $this->assertEquals(1, $this->destroyed[$hash]);
    }
    
  } implements(__FILE__, 'net.xp_framework.unittest.core.DestructionCallback');
?>
