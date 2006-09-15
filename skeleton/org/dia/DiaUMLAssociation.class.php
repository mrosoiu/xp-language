<?php
/*
 *
 * $Id:$
 */

  uses(
    'org.dia.DiaUMLConnection',
    'org.dia.DiaRole'
  );

  class DiaUMLAssociation extends DiaUMLConnection {

    /**
     * Constructor of an UML realization
     *
     * @access  public
     */
    function __construct() {
      parent::__construct('UML - Association', 1);
    }

    /**
     * Initializes the object with default values
     * 
     * @access  public
     */
    function initialize() {
      $this->setName('__noname__');

      // add essencial nodes
      $this->set('connections', new DiaConnections());
      $this->set('ends', new DiaAttribute('ends'));
      $this->setRoleA(new DiaRole());
      $this->setRoleB(new DiaRole());
      $this->set('orth_points', new DiaAttribute('orth_points'));
      $this->set('orth_orient', new DiaAttribute('orth_orient'));

      // default flags
      $this->setDirection(0);

      // positioning information
      $this->setPosition(array(0, 0));
      $this->setBoundingBox(array(array(0, 0), array(1, 1)));
      $this->setOrthAutoroute(TRUE);
    }

    /**
     * Returns the direction of the association (0= none, 1= A-to-B, 2= B-to-A)
     *
     * @access  public
     * @return  int
     */
    function getDirection() {
      return $this->getChildValue('direction');
    }

    /**
     * Set the direction of the Association, either none (0) from A to B (1) or
     * from B to A (2)
     *
     * @access  public
     * @param   int dir
     */
    #[@fromDia(xpath= 'dia:attribute[@name="direction"]/dia:enum/@val', value= 'int')]
    function setDirection($dir) {
      $this->setEnum('direction', $dir);
    }

    /**
     * Returns the left side (Role) of the association
     *
     * @access  public
     * @return  &org.dia.DiaRole
     */
    function &getRoleA() {
      $Ends= &$this->getChild('ends');
      return $Ends->getChild('A');
    }

    /**
     * Sets the left side (Role) of the association
     *
     * @access  public
     * @param   &org.dia.DiaRole Role
     */
    #[@fromDia(xpath= 'dia:attribute[@name="ends"]/dia:composite[position()=1]', class= 'org.dia.DiaRole')]
    function setRoleA(&$Role) {
      $Ends= &$this->getChild('ends');
      $Ends->set('A', $Role);
    }

    /**
     * Returns the left side (Role) of the association
     *
     * @access  public
     * @return  &org.dia.DiaRole
     */
    function &getRoleB() {
      $Ends= &$this->getChild('ends');
      return $Ends->getChild('B');
    }

    /**
     * Set the right side (Role) of the association
     *
     * @access  public
     * @param   &org.dia.DiaRole Role
     */
    #[@fromDia(xpath= 'dia:attribute[@name="ends"]/dia:composite[position()=2]', class= 'org.dia.DiaRole')]
    function setRoleB(&$Role) {
      $Ends= &$this->getChild('ends');
      $Ends->set('B', $Role);
    }

    /**
     * Set the ID and connection point of the object where the line begins
     *
     * @param   string id The diagram object ID
     * @param   int connpoint default 0 The connection point of the object
     */
    function beginAt($id, $connpoint= 0) {
      parent::endAt($id, $connpoint);
    }

    /**
     * Set the ID and connection point of the object where the line ends
     *
     * @param   string id The diagram object ID
     * @param   int connpoint default 5 The connection point of the object
     */
    function endAt($id, $connpoint= 5) {
      parent::beginAt($id, $connpoint);
    }
  }
?>
