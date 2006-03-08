<?php
/* This class is part of the XP framework's experiments
 *
 * $Id: OpcodeArray.class.php 6551 2006-02-05 16:54:55Z friebe $ 
 */

  /**
   * (Insert class' description here)
   *
   * @see      reference
   * @purpose  purpose
   */
  class OpcodeArray extends Object {
    var
      $opcodes = array(),
      $offset  = 0,
      $size    = 0;

    function add($opcode, $arguments) {
      $this->opcodes[]= array($opcode, $arguments);
      $this->size++;
    }
    
    function dump($i) {
      Console::writeLinef(
        'Opline %d [%s]: { %s }',
        $i,
        $this->opcodes[$i][0],
        implode(', ', array_map(array('xp', 'stringOf'), $this->opcodes[$i][1]))
      );
    }
  }
?>
