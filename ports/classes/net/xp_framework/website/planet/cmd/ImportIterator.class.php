<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.XPIterator', 'de.schlund.lucene.DocumentValue');

  /**
   * Returns documents to be indexed
   *
   * @purpose  Specialized iterator implementation
   */
  interface ImportIterator extends XPIterator {
  
    /**
     * Return type
     *
     * @return  string type
     */
    public function getType();
  
  }
?>
