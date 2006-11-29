<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('unittest.TestCase');

  /**
   * Tests destructor functionality
   *
   * @purpose  Testcase
   */
  class CloningTest extends TestCase {

    /**
     * Tests cloning of xp::null() which shouldn't work
     *
     * @access  public
     */
    #[@test, @expect('lang.NullPointerException')]
    function cloningOfNulls() {
      clone(xp::null());
    }

    /**
     * Tests cloning of non-objects which shouldn't work
     *
     * @access  public
     */
    #[@test, @expect('lang.CloneNotSupportedException')]
    function cloningOfNonObjects() {
      clone(6100);
    }

    /**
     * Tests cloning of an object without a __clone interceptor
     *
     * @access  public
     */
    #[@test]
    function cloneOfObject() {
      $original= &new Object();
      $this->assertFalse($original == clone($original));
    }

    /**
     * Tests cloning of an object with a __clone interceptor
     *
     * @access  public
     */
    #[@test]
    function cloneInterceptorCalled() {
      $original= &newinstance('lang.Object', array(), '{
        var $cloned= FALSE;

        function __clone() {
          $this->cloned= TRUE;
        }
      }');
      $this->assertFalse($original->cloned);
      $clone= &clone($original);
      $this->assertFalse($original->cloned);
      $this->assertTrue($clone->cloned);
    }

    /**
     * Tests cloning of an object whose __clone interceptor throws a 
     * CloneNotSupportedException
     *
     * @access  public
     */
    #[@test, @expect('lang.CloneNotSupportedException')]
    function cloneInterceptorThrowsException() {
      clone(newinstance('lang.Object', array(), '{
        function __clone() {
          throw(new CloneNotSupportedException("I am *UN*Cloneable"));
        }
      }'));
    }
  }
?>
