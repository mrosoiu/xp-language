<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Iterator over the keys of a Hashmap object
   *
   * Usage code snippet:
   * <code>
   *   // ...
   *   for ($i= &$hash->iterator(); $i->hasNext(); ) {
   *     $key= $i->next();
   *     var_dump($key, $hash->get($key));
   *   }
   *   // ...
   * </code>
   *
   * @see      xp://util.Hashmap
   * @purpose  Iterator
   */
  class HashmapIterator extends Object {
    var
      $_hash    = NULL,
      $_key     = FALSE;
  
    /**
     * Constructor
     *
     * @access  public
     * @param   &array hash
     * @see     xp://util.Hashmap#iterator
     */
    function __construct(&$hash) {
      $this->_hash= &$hash;
      reset($this->_hash);
    }
  
    /**
     * Returns true if the iteration has more elements. (In other words, 
     * returns true if next would return an element rather than throwing 
     * an exception.)
     *
     * @access  public
     * @return  bool
     */
    function hasNext() {
      return !is_null($this->_key= key($this->_hash));
    }
    
    /**
     * Returns the next element in the iteration.
     *
     * @access  public
     * @return  &mixed
     * @throws  util.NoSuchElementException when there are no more elements
     */
    function &next() {
      if (is_null($this->_key)) {
        return throw(new NoSuchElementException('No more elements'));
      }
      next($this->_hash);
      return $this->_key;
    }
  } implements(__FILE__, 'util.Iterator');
?>
