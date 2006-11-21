<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('net.xp_framework.tools.vm.unittest.migrate.AbstractRewriterTest');

  /**
   * Tests type names
   *
   * @purpose  Unit Test
   */
  class InstanceOfTest extends AbstractRewriterTest {

    /**
     * Tests is_a() when used with a variable and a string
     *
     * @access  public
     */
    #[@test]
    function variableAndString() {
      $this->assertRewritten('($a instanceof util.Date);', 'is_a($a, "Date");');
    }

    /**
     * Tests is_a() when used with a expression and a string
     *
     * @access  public
     */
    #[@test]
    function expressionAndString() {
      $this->assertRewritten('(get_class($this) instanceof util.Date);', 'is_a(get_class($this), "Date");');
    }
  }
?>
