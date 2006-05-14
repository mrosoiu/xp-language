<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.collections.HashProvider');

  /**
   * An object that maps keys to values. A map cannot contain duplicate 
   * keys; each key can map to at most one value. 
   *
   * @see      xp://util.collections.HashProvider
   * @purpose  Interface
   */
  class Map extends Interface {
    
    /**
     * Associates the specified value with the specified key in this map.
     * If the map previously contained a mapping for this key, the old 
     * value is replaced by the specified value.
     * Returns previous value associated with specified key, or NULL if 
     * there was no mapping for the specified key.
     *
     * @access  public
     * @param   &lang.Object key
     * @param   &lang.Object value
     * @return  &lang.Object the previous value associated with the key
     */
    function &put(&$key, &$value) { }

    /**
     * Returns the value to which this map maps the specified key. 
     * Returns NULL if the map contains no mapping for this key.
     *
     * @access  public
     * @param   &lang.Object key
     * @return  &lang.Object the value associated with the key
     */
    function &get(&$key) { }
    
    /**
     * Removes the mapping for this key from this map if it is present.
     * Returns the value to which the map previously associated the key, 
     * or null if the map contained no mapping for this key.
     *
     * @access  public
     * @param   &lang.Object key
     * @return  &lang.Object the previous value associated with the key
     */
    function &remove(&$key) { }
    
    /**
     * Removes all mappings from this map.
     *
     * @access  public
     */
    function clear() { }

    /**
     * Returns the number of key-value mappings in this map
     *
     * @access  public
     */
    function size() { }

    /**
     * Returns true if this map contains no key-value mappings. 
     *
     * @access  public
     */
    function isEmpty() { }
    
    /**
     * Returns true if this map contains a mapping for the specified key.
     *
     * @access  public
     * @param   &lang.Object key
     * @return  bool
     */
    function containsKey(&$key) { }

    /**
     * Returns true if this map maps one or more keys to the specified value. 
     *
     * @access  public
     * @param   &lang.Object value
     * @return  bool
     */
    function containsValue(&$value) { }

    /**
     * Returns a hashcode for this map
     *
     * @access  public
     * @return  string
     */
    function hashCode() { }
    
    /**
     * Returns true if this map equals another map.
     *
     * @access  public
     * @param   &lang.Object cmp
     * @return  bool
     */
    function equals(&$cmp) { }

  }
?>
