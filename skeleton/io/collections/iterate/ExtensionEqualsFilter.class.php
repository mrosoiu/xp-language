<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('io.collections.iterate.IterationFilter');

  /**
   * Extension filter
   *
   * @purpose  Iteration Filter
   */
  class ExtensionEqualsFilter extends Object implements IterationFilter {
    public
      $extension= '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string extension the file extension to compare to
     */
    public function __construct($extension) {
      $this->extension= '.'.ltrim($extension, '.');
    }
  
    /**
     * Accepts an element
     *
     * @access  public
     * @param   &io.collections.IOElement element
     * @return  bool
     */
    public function accept(&$element) {
      return $this->extension == substr($element->getURI(), -1 * strlen($this->extension));
    }

    /**
     * Creates a string representation of this iterator
     *
     * @access  public
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'("'.$this->compare.'")';
    }
  
  } 
?>
