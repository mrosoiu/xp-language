<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'text.parser.VFormatParser', 
    'org.imc.VEvent', 
    'org.imc.VTimezone',
    'util.Date'
  );

  // Identifier
  define('VCAL_ID',             'VCALENDAR');

  // Methods
  define('VCAL_METHOD_REQUEST', 'REQUEST');
  define('VCAL_METHOD_PUBLISH', 'PUBLISH');

  /**
   * VCalendar
   * 
   * <quote>
   * vCalendar defines a transport and platform-independent format for exchanging 
   * calendaring and scheduling information in an easy, automated, and consistent 
   * manner. It captures information about event and "to-do" items that are
   * normally used by applications such as a personal information managers (PIMs) 
   * and group schedulers. Programs that use vCalendar can exchange important data 
   * about events so that you can schedule meetings with anyone who has a vCalendar-
   * aware program.
   * </quote>
   *
   * Example [Free/Busy information]
   * <pre>
   * BEGIN:VCALENDAR
   * CALSCALE:GREGORIAN
   * PRODID:-//Ximian//NONSGML Evolution Calendar//EN
   * VERSION:2.0
   * METHOD:PUBLISH
   * BEGIN:VFREEBUSY
   * ORGANIZER;CN=3DTimm Friebe:friebe@php3.de
   * DTSTART:20030223T000000Z
   * DTEND:20030406T000000Z
   * FREEBUSY;FBTYPE=3DBUSY:20030223T100000Z/20030223T113000Z
   * UID:20030223T140522Z-260-0-1-20@friebes.net
   * DTSTAMP:20030223T140523Z
   * END:VFREEBUSY
   * END:VCALENDAR
   * </pre>
   *
   * Example [Simple calendar entry]
   * <pre>
   * BEGIN:VCALENDAR
   * CALSCALE:GREGORIAN
   * PRODID:-//Ximian//NONSGML Evolution Calendar//EN
   * VERSION:2.0
   * METHOD:PUBLISH
   * BEGIN:VTIMEZONE
   * TZID:/softwarestudio.org/Olson_20011030_5/Europe/Berlin
   * X-LIC-LOCATION:Europe/Berlin
   * BEGIN:DAYLIGHT
   * TZOFFSETFROM:+0100
   * TZOFFSETTO:+0200
   * TZNAME:CEST
   * DTSTART:19700329T020000
   * RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3
   * END:DAYLIGHT
   * BEGIN:STANDARD
   * TZOFFSETFROM:+0200
   * TZOFFSETTO:+0100
   * TZNAME:CET
   * DTSTART:19701025T030000
   * RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=10
   * END:STANDARD
   * END:VTIMEZONE
   * BEGIN:VEVENT
   * UID:20030223T140445Z-260-0-1-14@friebes.net
   * DTSTAMP:20030223T140544Z
   * DTSTART;TZID=/softwarestudio.org/Olson_20011030_5/Europe/Berlin:
   *  20030223T110000
   * DTEND;TZID=/softwarestudio.org/Olson_20011030_5/Europe/Berlin:
   *  20030223T123000
   * TRANSP:OPAQUE
   * SEQUENCE:2
   * SUMMARY:Test
   * LOCATION:Home
   * DESCRIPTION:Wop\,\n\nw??p.\,\n\nwiorsss
   * CLASS:PUBLIC
   * LAST-MODIFIED:20030223T140504Z
   * ORGANIZER;CN=Timm Friebe:MAILTO:friebe@php3.de
   * END:VEVENT
   * END:VCALENDAR
   * </pre>
   *
   * @see      rfc://2445
   * @see      rfc://2446
   * @see      rfc://2447
   * @see      http://www.imc.org/pdi/
   * @see      http://www.imc.org/pdi/pdiproddev.html
   * @purpose  Handle vCalendar
   */
  class VCalendar extends Object {
    var
      $uid      = '',
      $method   = '',
      $events   = array();

    /**
     * Set UID
     *
     * @access  public
     * @param   string uid
     */
    function setUID($uid) {
      $this->uid= $uid;
    }
    
    /**
     * Get UID
     *
     * @access  public
     * @return  string uid
     */    
    function getUID() {
      return $this->uid;
    }

    /**
     * Set Method
     *
     * @access  public
     * @param   string method
     */
    function setMethod($method) {
      $this->method= $method;
    }

    /**
     * Get Method
     *
     * @access  public
     * @return  string
     */
    function getMethod() {
      return $this->method;
    }

    /**
     * Add Event
     *
     * @access  public
     * @param   org.imc.VEvent event
     */
    function addEvent(&$event) {
      $this->events[]= &$event;
    }

    /**
     * Get Events
     *
     * @access  public
     * @return  &org.imc.VEvent[]
     */
    function &getEvents() {
      return $this->events;
    }
      
    /**
     * Parser callback
     *
     * @access  public
     * @param   array keys
     * @param   mixed value
     * @throws  lang.FormatException
     */
    function addProperty($keys, $value) {
      static $context= array();
      static $event;
      static $timezone;
            
      // Cascaded objects      
      if (0 == strcasecmp('BEGIN', $keys[0])) $context[]= strtolower($value);
      
      switch (implode('/', $context)) {
      
        // The calendar itself
        case '':
          switch ($keys[0]) {
            case 'METHOD':
              $this->method= $value;
              break;
          }
          break;
          
        // An event
        case 'vevent':
          switch ($keys[0]) {
            case 'BEGIN':
              $event= &new VEvent();
              break;
              
            case 'END':
              $this->events[]= &$event;
              break;
            
            case 'DTSTAMP':     // DTSTAMP:20030220T101358Z
              $event->date= &new Date(VFormatParser::decodeDate($value));
              break;
              
            case 'DTSTART':
              $event->starts= &new Date(VFormatParser::decodeDate($value));
              break;

            case 'DTEND':
              $event->ends= &new Date(VFormatParser::decodeDate($value));
              break;
            
            case 'SUMMARY':
              $event->summary= VFormatParser::decodeString($value);
              break;

            case 'LOCATION':
              $event->location= VFormatParser::decodeString($value);
              break;

            case 'DESCRIPTION':
              $event->description= VFormatParser::decodeString($value);
              break;
              
            case 'ATTENDEE':
              $event->attendee[]= $value;
              break;
              
            case 'ORGANIZER':
              $event->organizer= $value;
              break;
          }
          break;

        // An alarm for an event          
        case 'vevent/valarm':
          switch ($keys[0]) {
            case 'ACTION':
              $event->alarm['action']= $value;
              break;
              
            case 'DESCRIPTION':
              $event->alarm['description']= $value;
              break;
            
            case 'TRIGGER':     // TRIGGER;RELATED=START:-PT00H15M00S
              $event->alarm['trigger']= $value;
              break;
          }
        
        // A timezone
        case 'vtimezone':
          switch ($keys[0]) {
            case 'BEGIN':
              $timezone= &new VTimezone();
              break;
            
            case 'END':
              $this->timezone= &$timezone;
              break;
              
            case 'TZID':
              $timezone->setTzid($value);
              break;
            
            case 'LAST-MOD':
              $timezone->setLastMod(new Date(VFormatParser::decodeDate($value)));
              break;
            
            case 'TZURL':
              $timezone->setTZUrl(new URL ($value));
              break;
          }
        
        case 'vtimezone/daylight':
        case 'vtimezone/standard':
          $type= $context[sizeof($context)-1];
          
          switch ($keys[0]) {
            case 'BEGIN':
            case 'END':
              break;
            
            case 'DTSTART':
              $timezone->{$type}['dtstart']= &new Date(VFormatParser::decodeDate($value));
              break;
            
            case 'TZOFFSETTO':
              $timezone->{$type}['tzoffsetto']= $value;
              break;
            
            case 'TZOFFSETFROM':
              $timezone->{$type}['tzoffsetfrom']= $value;
              break;
            
            // TODO: Check those fields
            default:
              $timezone->{$type}[$keys[0]]= $value;
              break;
          }
      }
      
      if (0 == strcasecmp('END', $keys[0])) array_pop($context);
    }
        
    /**
     * Creata a vCalendar from a stream
     *
     * <code>
     *   try(); {
     *     $cal= &VCalendar::fromStream(new File('/tmp/test.ics'));
     *   } if (catch('Exception', $e)) {
     *     $e->printStackTrace();
     *     exit(-1);
     *   }
     *   
     *   var_dump($cal);
     * </code>
     *
     * @model   static
     * @access  public
     * @param   &io.Stream stream
     * @return  &org.imc.VCard
     */
    function &fromStream(&$stream) {
      $cal= &new VCalendar();
      
      $p= &new VFormatParser(VCAL_ID);
      $p->setDefaultHandler(array(&$cal, 'addProperty'));
      
      try(); {
        $p->parse($stream);
      } if (catch('Exception', $e)) {
        return throw($e);
      }
      
      return $cal;
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
      }

      // Escape string, encode it to UTF8    
      return ($key.':'.strtr(utf8_encode ($value), array (
        ','   => '\,',
        "\n"  => '\n'
      ))."\n");
    }
    
    /**
     * Returns the textual representation of this vCalendar
     *
     * <code>
     *   [...]
     *   $f= &new File('calendar.ics');
     *   $f->open(FILE_MODE_WRITE);
     *   $f->write($cal->export());
     *   $f->close();
     * </code>
     *
     * @access  public
     * @return  string
     */
    function export() {

      // First construct the calendar itself
      $ret = $this->_export ('BEGIN',     VCAL_ID);
      $ret.= $this->_export ('CALSCALE',  'GREGORIAN');
      $ret.= $this->_export ('PRODID',    '-//XP//XP Framework Calendar//EN');
      $ret.= $this->_export ('VERSION',   $this->getVersion());
      $ret.= $this->_export ('METHOD',    $this->getMethod());

      // Export timezone
      $ret.= $this->timezone->export ();
      
      // Enter all contained elements
      foreach (array_keys ($this->events) as $idx) {
        $ret.= $this->events[$idx]->export();
      }
      
      // Close the calendar
      $ret.= $this->_export ('END',       'VCALENDAR');
      return $ret;
    }
    
    /**
     * Returns the VCalendar version of this implementation
     *
     * @access  public
     * @return  string version
     */
    function getVersion() {
      return '2.0';
    }
  }
?>
