<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Iterator over a resultset
   *
   * @test     xp://net.xp_framework.unittest.rdbms.DataSetTest
   * @see      xp://rdbms.Peer
   * @purpose  Iterator
   */
  class ResultIterator extends Object {
    var
      $_rs         = NULL,
      $_identifier = '',
      $_record     = NULL;

    /**
     * Constructor
     *
     * @access  public
     * @param   &rdbms.ResultSet rs
     * @param   string identifier
     * @see     xp://rdbms.Peer#iteratorFor
     */
    function __construct(&$rs, $identifier) {
      $this->_rs= &$rs;
      $this->_identifier= $identifier;
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

      // Check to see if we have fetched a record previously. In this case,
      // short-cuircuit this to prevent hasNext() from forwarding the result
      // pointer every time we call it.
      if ($this->_record) return TRUE;

      $this->_record= &$this->_rs->next();
      return !empty($this->_record);
    }
    
    /**
     * Returns the next element in the iteration.
     *
     * @access  public
     * @return  &rdbms.DataSet
     * @throws  util.NoSuchElementException when there are no more elements
     */
    function &next() {
      if (NULL === $this->_record) {
        $this->_record= &$this->_rs->next();
        // Fall through
      }
      if (FALSE === $this->_record) {
        return throw(new NoSuchElementException('No more elements'));
      }
      
      // Create an instance and set the _record member to NULL so that
      // hasNext() will fetch the next record.
      $instance= &new $this->_identifier($this->_record);
      $this->_record= NULL;
      return $instance;
    }
  } implements(__FILE__, 'util.Iterator');
?>
