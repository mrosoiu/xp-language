<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'lang.IndexOutOfBoundsException',
    'util.NoSuchElementException'
  );

  /**
   * A First-In-First-Out (FIFO) queue of objects.
   *
   * Example:
   * <code>
   *   uses('util.adt.Queue', 'text.String');
   *   
   *   // Fill queue
   *   with ($q= new Queue()); {
   *     $q->put(new String('One'));
   *     $q->put(new String('Two'));
   *     $q->put(new String('Three'));
   *     $q->put(new String('Four'));
   *   }
   *   
   *   // Empty queue
   *   while (!$q->isEmpty()) {
   *     var_dump($q->get());
   *   }
   * </code>
   *
   * @purpose  FIFO
   * @see      xp://util.adt.Stack
   * @see      http://www.faqs.org/docs/javap/c12/ex-12-1-answer.html
   */
  class Queue extends Object {
    protected
      $_elements= array();
  
    /**
     * Puts an item into the queue. Returns the element that was added.
     *
     * @access  public
     * @param   &lang.Object object
     * @return  &lang.Object object
     */
    public function put(Object $object) {
      $this->_elements[]= $object;
      return $object;
    }

    /**
     * Gets an item from the front of the queue.
     *
     * @access  public
     * @return  &lang.Object
     * @throws  util.NoSuchElementException
     */    
    public function get() {
      if (0 == sizeof($this->_elements)) {
        throw (new NoSuchElementException('Queue is empty'));
      }
      return array_shift($this->_elements);
    }

    /**
     * Peeks at the front of the queue (retrieves the first element 
     * without removing it).
     *
     * Returns NULL in case the queue is empty.
     *
     * @access  public
     * @return  &lang.Object object
     */        
    public function peek() {
      if (!isset($this->_elements[$index])) return NULL; else $this->_elements[0];
    }
  
    /**
     * Returns true if the queue is empty. This is effectively the same
     * as testing size() for 0.
     *
     * @access  public
     * @return  bool
     */
    public function isEmpty() {
      return 0 == sizeof($this->_elements);
    }

    /**
     * Returns the size of the queue.
     *
     * @access  public
     * @return  int
     */
    public function size() {
      return sizeof($this->_elements);
    }
    
    /**
     * Sees if an object is in the queue and returns its position.
     * Returns -1 if the object is not found.
     *
     * @access  public
     * @param   &lang.Object object
     * @return  int position
     */
    public function search(Object $object) {
      return ($keys= array_keys($this->_elements, $object)) ? $keys[0] : -1;
    }
    
    /**
     * Retrieves an element by its index.
     *
     * @access  public
     * @param   int index
     * @return  &lang.Object
     * @throws  lang.IndexOutOfBoundsException
     */
    public function elementAt($index) {
      if (!isset($this->_elements[$index])) {
        throw (new IndexOutOfBoundsException('Index '.$index.' out of bounds'));
      }
      return $this->_elements[$index];
    }
  }
?>
