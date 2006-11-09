<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * A composite of multiple collections
   *
   * Example (all files in /home and /usr):
   * <code>
   *   $collection= &new CollectionComposite(array(
   *     new FileCollection('/home'),
   *     new FileCollection('/usr')
   *   ));
   *   $collection->open();
   *   while (NULL !== ($element= &$collection->next())) {
   *     Console::writeLine('- ', $element->toString());
   *   }
   *   $collection->close();
   * </code>
   *
   * @see      http://xp-framework.info/xml/xp.en_US/news/view?129
   * @test     xp://net.xp_framework.unittest.io.collections.CollectionCompositeTest 
   * @purpose  Collection implementation
   */
  class CollectionComposite extends Object {
    var
      $collections = array();
    
    var
      $_current    = 0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   io.collections.IOCollection[] collections
     * @throws  lang.IllegalArgumentException if collections is an empty array
     */
    function __construct($collections) {
      if (empty($collections)) {
        return throw(new IllegalArgumentException('Collections may not be empty'));
      }
      $this->collections= $collections;
    }
    
    /**
     * Open this collection
     *
     * @access  public
     */
    function open() { 
      $this->collections[0]->open();
    }

    /**
     * Rewind this collection (reset internal pointer to beginning of list)
     *
     * @access  public
     */
    function rewind() {
      do {
        $this->collections[$this->_current]->rewind(); 
      } while ($this->_current-- > 0);
    }
  
    /**
     * Retrieve next element in collection. Return NULL if no more entries
     * are available
     *
     * @access  public
     * @return  &io.collection.IOElement
     */
    function &next() {
      do { 
        if (NULL !== ($element= &$this->collections[$this->_current]->next())) return $element;
        
        // End of current collection, close it and continue with next collection
        // In case the end of collections has been reached, return NULL
        $this->collections[$this->_current]->close();
        if (++$this->_current >= sizeof($this->collections)) {
          $this->_current--;
          return NULL;
        }
        $this->collections[$this->_current]->open();
      } while (1);
    }

    /**
     * Close this collection
     *
     * @access  public
     */
    function close() { 
      do {
        $this->collections[$this->_current]->close(); 
      } while ($this->_current-- > 0);
    }
    
  } uses('io.collections.IOCollection');
?>
