<?php
/*
 *
 * $Id:$
 */
  uses(
    'org.dia.DiaElement'
  );

  /**
   * Represents a 'dia:connection' node
   */
  class DiaConnection extends DiaElement {

    var
      $node_name= 'dia:connection',
      $value= array();

    /**
     * Constructor
     *
     * @access  public
     * @param   int handle default 0
     */
    function __construct($handle= 0) {
      $this->handle= $handle;
      $this->initialize();
    }

    /**
     * Initializes this connection with default values
     *
     * @access  public
     */
    function initialize() {
      if (!isset($this->handle)) $this->handle= 0;
      $this->to= '00';
      $this->conn= 0;
    }

    /**
     * Returns the handle of the connection
     *
     * @access  public
     * @return  int
     */
    function getHandle() {
      return $this->handle;
    }

    /**
     * Sets the 'handle' of the connection. In general the handle=0 is the
     * start of the line and handle=1 is the end, but this depends on the
     * according UML object (dependency, generalization, ...)
     *
     * @access  public
     * @param   int handle
     */
    #[@fromDia(xpath= '@handle', value= 'int')]
    function setHandle($handle) {
      $this->handle= $handle;
    }

    /**
     * Returns the ID of the connected object
     *
     * @access  public
     * @return  string
     */
    function getTo() {
      return $this->to;
    }

    /**
     * Sets the ID of the object the connection is attached to
     *
     * @access  public
     * @param   string to
     */
    #[@fromDia(xpath= '@to', value= 'string')]
    function setTo($to) {
      $this->to= $to;
    }

    /**
     * Returns the connection point number where this connection is attached to
     * the object
     *
     * @access  public
     * @return  int
     */
    function getConnection() {
      return $this->conn;
    }

    /**
     * Sets the connection point of the object. The connection points are
     * sequentially numbered starting from top-left, always going from left to
     * right.
     *
     * @access  public
     * @param   int conn
     */
    #[@fromDia(xpath= '@connection', value= 'int')]
    function setConnection($conn) {
      $this->conn= $conn;
    }

    /**
     * Returns the XML representation of this object
     *
     * @access  public
     * @return  &xml.Node
     */
    function &getNode() {
      $Node= &parent::getNode();
      $Node->setAttribute('handle', $this->handle);
      $Node->setAttribute('to', $this->to);
      $Node->setAttribute('connection', $this->conn);
      return $Node;
    }
  }
?>
