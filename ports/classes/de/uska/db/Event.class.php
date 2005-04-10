<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('rdbms.DataSet');
 
  /**
   * Class wrapper for table event, database uska
   * (Auto-generated on Sun, 10 Apr 2005 13:37:17 +0200 by alex)
   *
   * @purpose  Datasource accessor
   */
  class Event extends DataSet {
    var
      $event_id           = 0,
      $team_id            = 0,
      $name               = '',
      $description        = NULL,
      $target_date        = NULL,
      $deadline           = NULL,
      $max_attendees      = NULL,
      $req_attendees      = NULL,
      $allow_guests       = NULL,
      $event_type_id      = 0,
      $changedby          = '',
      $lastchange         = NULL;

    /**
     * Static initializer
     *
     * @model   static
     * @access  public
     */
    function __static() { 
      with ($peer= &Event::getPeer()); {
        $peer->setTable('uska.event');
        $peer->setConnection('uskadb');
        $peer->setIdentity('event_id');
        $peer->setPrimary(array('event_id'));
        $peer->setTypes(array(
          'event_id'            => '%d',
          'team_id'             => '%d',
          'name'                => '%s',
          'description'         => '%s',
          'target_date'         => '%s',
          'deadline'            => '%s',
          'max_attendees'       => '%d',
          'req_attendees'       => '%d',
          'allow_guests'        => '%d',
          'event_type_id'       => '%d',
          'changedby'           => '%s',
          'lastchange'          => '%s'
        ));
      }
    }  
  
    /**
     * Retrieve associated peer
     *
     * @access  public
     * @return  &rdbms.Peer
     */
    function &getPeer() {
      return Peer::forName(__CLASS__);
    }
  
    /**
     * Gets an instance of this object by index "PRIMARY"
     *
     * @access  static
     * @param   int event_id
     * @return  &de.uska.db.Event object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByEvent_id($event_id) {
      $peer= &Event::getPeer();
      return array_shift($peer->doSelect(new Criteria(array('event_id', $event_id, EQUAL))));
    }

    /**
     * Gets an instance of this object by index "target_date"
     *
     * @access  static
     * @param   util.Date target_date
     * @return  &de.uska.db.Event[] object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByTarget_date($target_date) {
      $peer= &Event::getPeer();
      return $peer->doSelect(new Criteria(array('target_date', $target_date, EQUAL)));
    }

    /**
     * Gets an instance of this object by index "team_id"
     *
     * @access  static
     * @param   int team_id
     * @return  &de.uska.db.Event[] object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByTeam_id($team_id) {
      $peer= &Event::getPeer();
      return $peer->doSelect(new Criteria(array('team_id', $team_id, EQUAL)));
    }

    /**
     * Gets an instance of this object by index "event_type_id"
     *
     * @access  static
     * @param   int event_type_id
     * @return  &de.uska.db.Event[] object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByEvent_type_id($event_type_id) {
      $peer= &Event::getPeer();
      return $peer->doSelect(new Criteria(array('event_type_id', $event_type_id, EQUAL)));
    }

    /**
     * Retrieves event_id
     *
     * @access  public
     * @return  int
     */
    function getEvent_id() {
      return $this->event_id;
    }
      
    /**
     * Sets event_id
     *
     * @access  public
     * @param   int event_id
     * @return  int the previous value
     */
    function setEvent_id($event_id) {
      return $this->_change('event_id', $event_id);
    }

    /**
     * Retrieves team_id
     *
     * @access  public
     * @return  int
     */
    function getTeam_id() {
      return $this->team_id;
    }
      
    /**
     * Sets team_id
     *
     * @access  public
     * @param   int team_id
     * @return  int the previous value
     */
    function setTeam_id($team_id) {
      return $this->_change('team_id', $team_id);
    }

    /**
     * Retrieves name
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }
      
    /**
     * Sets name
     *
     * @access  public
     * @param   string name
     * @return  string the previous value
     */
    function setName($name) {
      return $this->_change('name', $name);
    }

    /**
     * Retrieves description
     *
     * @access  public
     * @return  string
     */
    function getDescription() {
      return $this->description;
    }
      
    /**
     * Sets description
     *
     * @access  public
     * @param   string description
     * @return  string the previous value
     */
    function setDescription($description) {
      return $this->_change('description', $description);
    }

    /**
     * Retrieves target_date
     *
     * @access  public
     * @return  util.Date
     */
    function getTarget_date() {
      return $this->target_date;
    }
      
    /**
     * Sets target_date
     *
     * @access  public
     * @param   util.Date target_date
     * @return  util.Date the previous value
     */
    function setTarget_date($target_date) {
      return $this->_change('target_date', $target_date);
    }

    /**
     * Retrieves deadline
     *
     * @access  public
     * @return  util.Date
     */
    function getDeadline() {
      return $this->deadline;
    }
      
    /**
     * Sets deadline
     *
     * @access  public
     * @param   util.Date deadline
     * @return  util.Date the previous value
     */
    function setDeadline($deadline) {
      return $this->_change('deadline', $deadline);
    }

    /**
     * Retrieves max_attendees
     *
     * @access  public
     * @return  int
     */
    function getMax_attendees() {
      return $this->max_attendees;
    }
      
    /**
     * Sets max_attendees
     *
     * @access  public
     * @param   int max_attendees
     * @return  int the previous value
     */
    function setMax_attendees($max_attendees) {
      return $this->_change('max_attendees', $max_attendees);
    }

    /**
     * Retrieves req_attendees
     *
     * @access  public
     * @return  int
     */
    function getReq_attendees() {
      return $this->req_attendees;
    }
      
    /**
     * Sets req_attendees
     *
     * @access  public
     * @param   int req_attendees
     * @return  int the previous value
     */
    function setReq_attendees($req_attendees) {
      return $this->_change('req_attendees', $req_attendees);
    }

    /**
     * Retrieves allow_guests
     *
     * @access  public
     * @return  int
     */
    function getAllow_guests() {
      return $this->allow_guests;
    }
      
    /**
     * Sets allow_guests
     *
     * @access  public
     * @param   int allow_guests
     * @return  int the previous value
     */
    function setAllow_guests($allow_guests) {
      return $this->_change('allow_guests', $allow_guests);
    }

    /**
     * Retrieves event_type_id
     *
     * @access  public
     * @return  int
     */
    function getEvent_type_id() {
      return $this->event_type_id;
    }
      
    /**
     * Sets event_type_id
     *
     * @access  public
     * @param   int event_type_id
     * @return  int the previous value
     */
    function setEvent_type_id($event_type_id) {
      return $this->_change('event_type_id', $event_type_id);
    }

    /**
     * Retrieves changedby
     *
     * @access  public
     * @return  string
     */
    function getChangedby() {
      return $this->changedby;
    }
      
    /**
     * Sets changedby
     *
     * @access  public
     * @param   string changedby
     * @return  string the previous value
     */
    function setChangedby($changedby) {
      return $this->_change('changedby', $changedby);
    }

    /**
     * Retrieves lastchange
     *
     * @access  public
     * @return  util.Date
     */
    function getLastchange() {
      return $this->lastchange;
    }
      
    /**
     * Sets lastchange
     *
     * @access  public
     * @param   util.Date lastchange
     * @return  util.Date the previous value
     */
    function setLastchange($lastchange) {
      return $this->_change('lastchange', $lastchange);
    }
  }
?>