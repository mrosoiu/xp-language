<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.Collection', 'unittest.coverage.Fragment');

  /**
   * Represents an Block
   *
   * @purpose  Helper class
   */
  class Block extends Object implements Fragment {
    public
      $code        = '',
      $start       = 0,
      $end         = 0,
      $expressions = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   unittest.coverage.Expression[] expressions
     * @param   int start the first line
     * @param   int end the last line
     */
    public function __construct($code, $expressions, $start, $end) {
      $this->code= $code;
      $this->start= $start;
      $this->end= $end;
      $this->expressions= &Collection::forClass('Fragment');
      $this->expressions->addAll($expressions);
    }
    
    /**
     * Checks if a specified object is equal to this object.
     *
     * @access  public
     * @param   &lang.Object block
     * @return  bool
     */
    public function equals(&$block) {
      return (
        is('Block', $block) && 
        $this->start == $block->start &&
        $this->end == $block->end &&
        $this->code == $block->code &&
        $this->expressions->equals($block->expressions)
      );
    }

    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'@({'.$this->code.' {'.$this->expressions->toString().'}} at lines '.$this->start.' - '.$this->end.')';
    }

  } 
?>
