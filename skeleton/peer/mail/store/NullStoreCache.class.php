<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('peer.mail.store.StoreCache');

  /**
   * MailStore cache class which does not cache. In some situations,
   * e.g., when reading a huge mailfolder, caching would consume way
   * too much memory. Use this class if you to prevent any caching.
   *
   * @see      xp://peer.mail.StoreCache
   * @purpose  No cache
   */
  class NullStoreCache extends StoreCache {
    
    /**
     * Remove a key from cache
     *
     * @access  public
     * @param   string key
     */
    function remove($key) { }

    /**
     * Get a value from cache by key
     *
     * @access  public
     * @param   string key
     * @return  &mixed value or NULL to indicate the value doesn't exist
     */    
    function &get($key) {
      return NULL;
    }
  
    /**
     * Insert into / update in cache
     *
     * @access  public
     * @param   string key
     * @param   &mixed val
     */
    function put($key, &$val) { }
    
    /**
     * Check whether cache has a value by key
     *
     * @access  public
     * @param   string key
     * @return  bool TRUE if a value exists
     */
    function has($key) {
      return FALSE;
    }
  }
?>
