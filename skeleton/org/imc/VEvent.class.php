<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('util.Date');

  /**
   * VEvent
   * 
   * @see      xp://org.imc.VCalendar
   * @purpose  Represent a single event
   */
  class VEvent extends Object {
    var
      $date         = NULL,
      $starts       = NULL,
      $ends         = NULL,
      $summary      = '',
      $location     = '',
      $description  = '',
      $attendee     = array(),
      $organizer    = '';

    /**
     * Set Date
     *
     * @access  public
     * @param   &util.Date date
     */
    function setDate(&$date) {
      $this->date= &$date;
    }

    /**
     * Get Date
     *
     * @access  public
     * @return  &util.Date
     */
    function &getDate() {
      return $this->date;
    }

    /**
     * Set Starts
     *
     * @access  public
     * @param   &util.Date starts
     */
    function setStarts(&$starts) {
      $this->starts= &$starts;
    }

    /**
     * Get Starts
     *
     * @access  public
     * @return  &util.Date
     */
    function &getStarts() {
      return $this->starts;
    }

    /**
     * Set Ends
     *
     * @access  public
     * @param   &util.Date ends
     */
    function setEnds(&$ends) {
      $this->ends= &$ends;
    }

    /**
     * Get Ends
     *
     * @access  public
     * @return  &util.Date
     */
    function &getEnds() {
      return $this->ends;
    }

    /**
     * Set Summary
     *
     * @access  public
     * @param   string summary
     */
    function setSummary($summary) {
      $this->summary= $summary;
    }

    /**
     * Get Summary
     *
     * @access  public
     * @return  string
     */
    function getSummary() {
      return $this->summary;
    }

    /**
     * Set Location
     *
     * @access  public
     * @param   string location
     */
    function setLocation($location) {
      $this->location= $location;
    }

    /**
     * Get Location
     *
     * @access  public
     * @return  string
     */
    function getLocation() {
      return $this->location;
    }

    /**
     * Set Description
     *
     * @access  public
     * @param   string description
     */
    function setDescription($description) {
      $this->description= $description;
    }

    /**
     * Get Description
     *
     * @access  public
     * @return  string
     */
    function getDescription() {
      return $this->description;
    }

    /**
     * Add attendee
     *
     * @access  public
     * @param   mixed[] attendee
     */
    function addAttendee($attendee) {
      $this->attendee[]= $attendee;
    }

    /**
     * Get attendees
     *
     * @access  public
     * @return  mixed[]
     */
    function getAttendees() {
      return $this->attendee;
    }

    /**
     * Set Organizer
     *
     * @access  public
     * @param   string organizer
     */
    function setOrganizer($organizer) {
      $this->organizer= $organizer;
    }

    /**
     * Get Organizer
     *
     * @access  public
     * @return  string
     */
    function getOrganizer() {
      return $this->organizer;
    }

    /**
     * Export function helper
     *
     * @access  private
     * @param   string key
     * @param   mixed value
     * @return  string exported
     */    
    function _export($key, $value) {
      if (is_a ($value, 'Date')) {
        // Convert date into string
        $value= $value->toString ('Ymd').'T'.$value->toString ('His').'Z';
      } else if (is_object ($value)) {
        foreach (get_object_vars ($value) as $pkey => $pvalue) {
          if ('_value' == $pkey) continue;
          
          // Append parameters
          $key.= ';'.strtoupper ($pkey).'='.$pvalue;
        }
        $value= $value->_value;
      }

      // Escape string, encode it to UTF8    
      return ($key.':'.strtr(utf8_encode ($value), array (
        ','   => '\,',
        "\n"  => '\n'
      ))."\n");
    }
    
    /**
     * Returns the string representation of this event
     *
     * @access  public
     * @return  string event
     */    
    function export() {
      $ret = $this->_export ('BEGIN',       'VEVENT');
      $ret.= $this->_export ('LOCATION',    $this->getLocation());
      $ret.= $this->_export ('DTSTAMP',     $this->getDate());
      $ret.= $this->_export ('DTSTART',     $this->getStarts());
      $ret.= $this->_export ('DTEND',       $this->getEnds());
      $ret.= $this->_export ('DESCRIPTION', $this->getDescription());
      $ret.= $this->_export ('SUMMARY',     $this->getSummary());
      $ret.= $this->_export ('ORGANIZER',   $this->getOrganizer());
      
      // Append all attendees
      foreach ($this->getAttendees() as $a) {
        $ret.= $this->_export ('ATTENDEE', $a);
      }
      
      // Append alarm if existant
      if (isset ($this->alarm)) {
        // TODO: Encapsulate this into an own class
        $ret.= $this->_export ('BEGIN',       'VALARM');
        $ret.= $this->_export ('ACTION',      $this->alarm['action']);
        $ret.= $this->_export ('DESCRIPTION', $this->alarm['description']);
        $ret.= $this->_export ('TRIGGER',     $this->alarm['trigger']);
        $ret.= $this->_export ('END',         'VALARM');
      }
      
      $ret.= $this->_export ('END',         'VEVENT');
      
      return $ret;
    }
  }
?>
