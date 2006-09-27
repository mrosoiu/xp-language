<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('net.xp_framework.tools.vm.unittest.emit.php5.AbstractEmitterTest');

  /**
   * Tests PHP5 emitter
   *
   * @purpose  Unit Test
   */
  class NumberEmitterTest extends AbstractEmitterTest {

    /**
     * Tests float numbers
     *
     * @access  public
     */
    #[@test]
    function floatNumbers() {
      foreach (array('1.0', '0.0', '0.5', '-5') as $declared) {
        $this->assertSourcecodeEquals(
          '$x= '.$declared.';',
          $this->emit('$x= '.$declared.';')
        );
      }
    }

    /**
     * Tests int numbers
     *
     * @access  public
     */
    #[@test]
    function intNumbers() {
      foreach (array('1', '0', '-5') as $declared) {
        $this->assertSourcecodeEquals(
          '$x= '.$declared.';',
          $this->emit('$x= '.$declared.';')
        );
      }
    }
  }
?>
