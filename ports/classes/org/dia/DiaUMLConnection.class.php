<?php
/*
 *
 * $Id:$
 */

  uses(
    'org.dia.DiaObject',
    'org.dia.DiaAttribute',
    'org.dia.DiaConnections',
    'org.dia.DiaConnection'
  );

  /**
   * Base class for all kinds of UML connections (dependency, generalization,
   * realization, association, ...)
   */
  class DiaUMLConnection extends DiaObject {

    var
      $conn_assoc= array(0 => 'begin', 1 => 'end');

    /**
     * Initialize this UMLConnection with default values
     * 
     * @access  public
     */
    function initialize() {
      // default values
      $this->setName('__noname__');
      $this->setStereotype(NULL);
      //$this->setDirection();

      // add essencial nodes
      $this->set('connections', new DiaConnections());
      $this->set('orth_points', new DiaAttribute('orth_points'));
      $this->set('orth_orient', new DiaAttribute('orth_orient'));

      // default flags

      // positioning information defaults
      $this->setPosition(array(0, 0));
      $this->setBoundingBox(array(array(0, 0), array(1, 1)));
      $this->setOrthAutoroute(TRUE);

      // default colors
      $this->setTextColor('#000000');
      $this->setLineColor('#000000');
    }

    /**
     * Returns the connection points of the connection
     *
     * @access  public
     * @return  org.dia.DiaConnection[]
     */
    function getConnections() {
      $conns['begin']= $this->getChild('begin');
      $conns['end']= $this->getChild('end');
      return $conns;
    }

    /**
     * Add either the 'begin' or 'end' connection
     *
     * @access  public
     * @param   &org.dia.DiaConnection Conn
     */
    #[@fromDia(xpath= 'dia:connections/dia:connection', class= 'org.dia.DiaConnection')]
    function addConnection(&$Conn) {
      $Conns= &$this->getChild('connections');
      $Conns->set($this->conn_assoc[$Conn->getHandle()], $Conn);
    }


    /**
     * Returns all corner points of the connection
     *
     * @access  public
     * @return  org.dia.DiaPoint[]
     */
    function getOrthPoints() {
      $Points= &$this->getChild('orth_points');
      return $Points->getChildren();
    }

    /**
     * Adds a connection corner point
     *
     * @access  public
     * @param   array point
     */
    #[@fromDia(xpath= 'dia:attribute[@name="orth_points"]/dia:point/@val', value= 'array')]
    function addOrthPoint($point) {
      $Points= &$this->getChild('orth_points');
      $Points->addChild(new DiaPoint($point));
    }

    /**
     * Return all direction indicators of the connection
     *
     * @access  public
     * @return  org.dia.DiaEnum[]
     */
    function getOrthOrients() {
      $Orient= &$this->getChild('orth_orient');
      return $Orient->getChildren();
    }

    /**
     * Adds a connection direction indicator for corner points
     *
     * @access  public
     * @param   int direction
     */
    #[@fromDia(xpath= 'dia:attribute[@name="orth_orient"]/dia:enum/@val', value= 'int')]
    function addOrthOrient($direction) {
      $Orient= &$this->getChild('orth_orient');
      $Orient->addChild(new DiaEnum($direction));
    }

    /** 
     * Get the autorouting flag of the connection
     *
     * @access  public
     * @return  bool
     */
    function getOrthAutoroute() {
      return $this->getChildValue('autoroute');
    }

    /**
     * Sets the autorouting flag for the connection
     *
     * @access  public
     * @param   bool autoroute
     */
    #[@fromDia(xpath= 'dia:attribute[@name="orth_autoroute"]/dia:boolean/@val', value= 'boolean')]
    function setOrthAutoroute($autoroute) {
      $this->setBoolean('autoroute', $autoroute);
    }

    /**
     * Set the ID of the object where the line begins
     *
     * HINT: UML generalization and realizes 'begins' at the object depended-on
     *
     * @param   string id
     * @param   int connpoint default 0
     */
    function beginAt($id, $connpoint= 0) {
      $Conns= &$this->getChild('connections');
      $Conn= &new DiaConnection(0);
      $Conn->setTo($id);
      $Conn->setConnection($connpoint);
      $Conns->set('begin', $Conn);
    }

    /**
     * Set the ID of the object where line ends
     *
     * HINT: UML generalization and realizes 'ends' at the depending object
     *
     * @param   string id
     * @param   int connpoint default 0
     */
    function endAt($id, $connpoint= 0) {
      $Conns= &$this->getChild('connections');
      $Conn= &new DiaConnection(1);
      $Conn->setTo($id);
      $Conn->setConnection($connpoint);
      $Conns->set('end', $Conn);
    }

  }
?>
