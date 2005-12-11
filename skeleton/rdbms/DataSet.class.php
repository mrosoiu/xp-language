<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('rdbms.ConnectionManager', 'rdbms.Peer', 'rdbms.Criteria');

  /**
   * A dataset represents a row of data selected from a database. Dataset 
   * classes usually provide getters and setters for every field in 
   * addition to insert(), update() and delete() methods and one or more 
   * static methods are provided to retrieve datasets from the database. 
   *
   * Note: All of these methods will rely on an instance of 
   * rdbms.ConnectionManager having been setup with a suitable connection. 
   * This way, there is no need to pass a connection instance to every
   * single method.
   *
   * For example, a table containing news  might provide a getByDate() 
   * method which returns an array of news objects and a getByNewsId() 
   * method returning one object.
   *
   * The basic ways to use the abovementioned example class would be:
   *
   * 1) Retrieve a news object
   * <code>
   *   try(); {
   *     $news= &News::getByNewsId($id);
   *   } if (catch('SQLException', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *
   *   echo $news->toString();
   * </code>
   *
   * 2) Create a new entry
   * <code>
   *   with ($n= &new News()); {
   *     $n->setCategoryId($cat);
   *     $n->setTitle('Welcome');
   *     $n->setBody(NULL);
   *     $n->setAuthor('Timm');
   *     $n->setCreatedAt(Date::now());
   *
   *     try(); {
   *       $n->insert();
   *     } if (catch('SQLException', $e)) {
   *       $e->printStackTrace();
   *       exit(-1);
   *     }
   *
   *     echo $n->toString();
   *   }
   * </code>
   *
   * 3) Modify a news object
   * <code>
   *   try(); {
   *     if ($news= &News::getByNewsId($id)) {
   *       $news->setCaption('Good news, everyone!');
   *       $news->setAuthor('Hubert Farnsworth');
   *       $news->update();
   *     }
   *   } if (catch('SQLException', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *
   *   echo $news->toString();
   * </code>
   *
   * Note: Fields are created as public members (aka "implicit public").
   *
   * @test     xp://net.xp_framework.unittest.rdbms.DataSetTest
   * @see      xp://rdbms.Peer
   * @see      xp://rdbms.ConnectionManager
   * @purpose  Base class
   */
  class DataSet extends Object {
    var
      $_new         = TRUE,
      $_changed     = array();
    
    /**
     * Constructor. Supports the array syntax, where an associative
     * array is passed to the constructor, the keys being the member
     * variables and the values the member's values.
     *
     * @access  public
     * @param   array params default NULL
     */
    function __construct($params= NULL) {
      if (is_array($params)) {
        foreach (array_keys($params) as $key) {
          $k= substr(strrchr('#'.$key, '#'), 1);
          $this->{$k}= &$params[$key];
        }
        $this->_new= FALSE;
      }
    }
    
    /**
     * Retrieve associated peer
     *
     * @model   abstract
     * @access  public
     * @return  &rdbms.Peer
     */
    function &getPeer() { }

    /**
     * Changes a value by a specified key and returns the previous value.
     *
     * @access  protected
     * @param   string key
     * @param   &mixed value
     * @return  &mixed previous value
     */
    function &_change($key, &$value) {
      $this->_changed[$key]= &$value;
      $previous= &$this->{$key};
      $this->{$key}= &$value;
      return $previous;
    }
    
    /**
     * Returns an array of fields that were changed suitable for passing
     * to Peer::doInsert() and Peer::doUpdate()
     *
     * @access  public
     * @return  array
     */
    function changes() {
      return $this->_changed;
    }

    /**
     * Returns whether this record is new
     *
     * @access  public
     * @return  bool
     */    
    function isNew() {
      return $this->_new;
    }
    
    /**
     * Creates a string representation of this dataset. In this default
     * implementation, it will look like the following:
     *
     * <pre>
     *   de.thekid.db.News(0.86699200 1086297326)@{
     *     [newsId         PK,I] 76288
     *     [categoryId         ] 12
     *     [caption            ] 'Hello'
     *     [body               ] NULL
     *     [author             ] 'Timm'
     *     [createdAt          ] Thu,  3 Jun 2004 22:26:15 +0200
     *   }
     * </pre>
     *
     * Note: Keys with a leading "_" will be omitted from the list, they
     * indicate "protected" members.
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $peer= &$this->getPeer();
            
      // Retrieve types from peer and figure out the maximum length 
      // of a key which will be used for the key "column". The minimum
      // width of this column is 20 characters.
      $max= 0xF;
      foreach (array_keys($peer->types) as $key) {
        $max= max($max, strlen($key));
      }
      $fmt= '  [%-'.$max.'s %2s%2s] %s';
      
      // Build string representation.
      $s= $this->getClassName().'@('.$this->hashCode()."){\n";
      foreach (array_keys($peer->types) as $key) {
        $s.= sprintf(
          $fmt, 
          $key,
          (in_array($key, $peer->primary) ? 'PK' : ''), 
          ($key == $peer->identity ? ',I' : ''),
          (is_a($this->$key, 'Object') 
            ? $this->$key->toString()
            : var_export($this->$key, 1)
          )
        )."\n";
      }
      return $s.'}';
    }

    /**
     * Update this object in the database by specified criteria
     *
     * @model   final
     * @access  public
     * @return  mixed identity value if applicable, else NULL
     * @throws  rdbms.SQLException in case an error occurs
     */  
    function doInsert() {
      $peer= &$this->getPeer();
      if ($id= $peer->doInsert($this->_changed)) {
      
        // Set identity value if requested. We do not use the _change()
        // method here since the primary key is not supposed to appear
        // in the list of changed attributes
        $this->{$peer->identity}= $id;
      }
      $this->_changed= array();
      return $id;
    }
  
    /**
     * Update this object in the database by specified criteria
     *
     * @model   final
     * @access  public
     * @param   &rdbms.Criteria criteria
     * @return  int number of affected rows
     * @throws  rdbms.SQLException in case an error occurs
     */  
    function doUpdate(&$criteria) {
      $peer= &$this->getPeer();
      $affected= $peer->doUpdate($this->_changed, $criteria);
      $this->_changed= array();
      return $affected;
    }

    /**
     * Delete this object from the database by specified criteria
     *
     * @model   final
     * @access  public
     * @param   &rdbms.Criteria criteria
     * @return  int number of affected rows
     * @throws  rdbms.SQLException in case an error occurs
     */  
    function doDelete(&$criteria) {
      $peer= &$this->getPeer();
      $affected= $peer->doDelete($criteria);
      $this->_changed= array();
      return $affected;
    }
    
    /**
     * Insert this dataset (create a new row in the table).
     *
     * @access  public
     * @return  mixed identity value if applicable, else NULL
     * @throws  rdbms.SQLException
     */
    function insert() {
      try(); {
        $identity= $this->doInsert();
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      $this->_new= FALSE;
      return $identity;
    }

    /**
     * Update this dataset (change an existing row in the table). 
     * Updates the record by using the primary key(s) as criteria.
     *
     * @access  public
     * @return  int affected rows
     * @throws  rdbms.SQLException
     */
    function update() {
      if (empty($this->_changed)) return 0;

      $peer= &$this->getPeer();
      if (empty($peer->primary)) {
        return throw(new SQLStateException('No primary key'));
      }
      $criteria= &new Criteria();
      foreach ($peer->primary as $key) {
        $criteria->add($key, $this->{$key}, EQUAL);
      }
      $affected= $peer->doUpdate($this->_changed, $criteria);
      $this->_changed= array();
      return $affected;
    }
    
    /**
     * Save this dataset. Inserts if this dataset is new (that means: Has been
     * created by new DataSetName()) and updates if it has been retrieved by
     * the database (by means of doSelect(), getBy...() or iterators).
     *
     * @access  public
     * @return  mixed identity value if applicable, else NULL
     * @throws  rdbms.SQLException
     */
    function save() {
      $peer= &$this->getPeer();
      
      try(); {
        $this->_new ? $this->insert() : $this->update();
      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return $this->{$peer->identity};
    }

    /**
     * Delete this dataset (remove the corresponding row from the table). 
     * Does nothing in this default implementation and may be overridden 
     * in subclasses where it makes sense.
     *
     * @access  public
     * @return  int affected rows
     * @throws  rdbms.SQLException
     */
    function delete() { 
      $peer= &$this->getPeer();
      if (empty($peer->primary)) {
        return throw(new SQLStateException('No primary key'));
      }
      $criteria= &new Criteria();
      foreach ($peer->primary as $key) {
        $criteria->add($key, $this->{$key}, EQUAL);
      }
      $affected= $peer->doDelete($criteria);
      $this->_changed= array();
      return $affected;
    }
  }
?>
