<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('rdbms.DataSet');
 
  /**
   * Class wrapper for table event_attendee, database uska
   * (Auto-generated on Sat, 09 Apr 2005 12:04:42 +0200 by alex)
   *
   * @purpose  Datasource accessor
   */
  class EventAttendee extends DataSet {
    var
      $event_id           = 0,
      $player_id          = 0,
      $attend             = 0,
      $offers_seats       = NULL,
      $needs_driver       = NULL,
      $lastchange         = NULL,
      $changedby          = '';

    /**
     * Static initializer
     *
     * @model   static
     * @access  public
     */
    function __static() { 
      with ($peer= &EventAttendee::getPeer()); {
        $peer->setTable('uska.event_attendee');
        $peer->setConnection('uskadb');
        $peer->setPrimary(array('event_id', 'player_id'));
        $peer->setTypes(array(
          'event_id'            => '%d',
          'player_id'           => '%d',
          'attend'              => '%d',
          'offers_seats'        => '%d',
          'needs_driver'        => '%d',
          'lastchange'          => '%s',
          'changedby'           => '%s'
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
     * Gets an instance of this object by index "event_id_2"
     *
     * @access  static
     * @param   int event_id
     * @param   int player_id
     * @return  &de.uska.db.EventAttendee object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByEvent_idPlayer_id($event_id, $player_id) {
      $peer= &EventAttendee::getPeer();
      return array_shift($peer->doSelect(new Criteria(
        array('event_id', $event_id, EQUAL),
        array('player_id', $player_id, EQUAL)
      )));
    }

    /**
     * Gets an instance of this object by index "event_id"
     *
     * @access  static
     * @param   int event_id
     * @return  &de.uska.db.EventAttendee[] object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByEvent_id($event_id) {
      $peer= &EventAttendee::getPeer();
      return $peer->doSelect(new Criteria(array('event_id', $event_id, EQUAL)));
    }

    /**
     * Gets an instance of this object by index "player_id"
     *
     * @access  static
     * @param   int player_id
     * @return  &de.uska.db.EventAttendee[] object
     * @throws  rdbms.SQLException in case an error occurs
     */
    function &getByPlayer_id($player_id) {
      $peer= &EventAttendee::getPeer();
      return $peer->doSelect(new Criteria(array('player_id', $player_id, EQUAL)));
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
     * Retrieves player_id
     *
     * @access  public
     * @return  int
     */
    function getPlayer_id() {
      return $this->player_id;
    }
      
    /**
     * Sets player_id
     *
     * @access  public
     * @param   int player_id
     * @return  int the previous value
     */
    function setPlayer_id($player_id) {
      return $this->_change('player_id', $player_id);
    }

    /**
     * Retrieves attend
     *
     * @access  public
     * @return  int
     */
    function getAttend() {
      return $this->attend;
    }
      
    /**
     * Sets attend
     *
     * @access  public
     * @param   int attend
     * @return  int the previous value
     */
    function setAttend($attend) {
      return $this->_change('attend', $attend);
    }

    /**
     * Retrieves offers_seats
     *
     * @access  public
     * @return  int
     */
    function getOffers_seats() {
      return $this->offers_seats;
    }
      
    /**
     * Sets offers_seats
     *
     * @access  public
     * @param   int offers_seats
     * @return  int the previous value
     */
    function setOffers_seats($offers_seats) {
      return $this->_change('offers_seats', $offers_seats);
    }

    /**
     * Retrieves needs_driver
     *
     * @access  public
     * @return  int
     */
    function getNeeds_driver() {
      return $this->needs_driver;
    }
      
    /**
     * Sets needs_driver
     *
     * @access  public
     * @param   int needs_driver
     * @return  int the previous value
     */
    function setNeeds_driver($needs_driver) {
      return $this->_change('needs_driver', $needs_driver);
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
  }
?>