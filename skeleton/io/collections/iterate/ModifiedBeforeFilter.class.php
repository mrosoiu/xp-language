<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('io.collections.iterate.AbstractDateComparisonFilter');

  /**
   * Date comparison filter
   *
   * @purpose  Iteration Filter
   */
  class ModifiedBeforeFilter extends AbstractDateComparisonFilter {

    /**
     * Accepts an element
     *
     * @access  public
     * @param   &io.collections.IOElement element
     * @return  bool
     */
    public function accept(&$element) { 
      return ($cmp= &$element->lastModified()) && $cmp->isBefore($this->date);
    }
  }
?>
