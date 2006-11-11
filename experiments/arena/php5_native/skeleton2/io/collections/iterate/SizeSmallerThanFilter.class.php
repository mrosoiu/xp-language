<?php
/* This class is part of the XP framework
 *
 * $Id: SizeSmallerThanFilter.class.php 8185 2006-10-16 10:24:01Z friebe $
 */

  uses('io.collections.iterate.AbstractSizeComparisonFilter');

  /**
   * Size comparison filter
   *
   * @purpose  Iteration Filter
   */
  class SizeSmallerThanFilter extends AbstractSizeComparisonFilter {

    /**
     * Accepts an element
     *
     * @access  public
     * @param   &io.collections.IOElement element
     * @return  bool
     */
    public function accept(&$element) {
      return $element->getSize() < $this->size;
    }
  }
?>
