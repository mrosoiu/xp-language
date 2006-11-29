<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.Date', 'util.TimeZone');

  /**
   * Parses dates
   *
   * <code>
   *   uses('text.parser.DateParser');
   *
   *   $d= &DateParser::parse('13.02');
   *   echo $d->toString();
   *    
   *   $d= &DateParser::parse('200401190957+0100');
   *   echo $d->toString();
   *
   *   $d= &DateParser::parse('20040414101045Z');
   *   echo $d->toString();
   *
   * </code>
   *
   * @see      http://www.gnu.org/software/tar/manual/html_chapter/tar_7.html
   * @see      http://ldap.akbkhome.com/syntax/1%2E3%2E6%2E1%2E4%2E1%2E1466%2E115%2E121%2E1%2E24.html
   * @purpose  Parser
   */
  class DateParser extends Object {

    /**
     * Get "fully qualified" year. For one and two digit years, returns
     * the current century *plus* the given number.
     *
     * @model   static
     * @access  public
     * @param   int year
     * @return  int
     */
    function yearFor($year) {
      if (strlen((int)$year) <= 2) {
        return (int)floor(date('Y') / 100) * 100 + $year;
      }
      return $year;
    }

    /**
     * Parse a date
     *
     * @model   static
     * @access  public
     * @param   string s
     * @return  &util.Date
     * @throws  lang.FormatException in case the date could not be parsed
     */
    function &parse($s) {
      if (empty($s)) {
      
        // Border case
        return throw(new FormatException('Cannot parse empty string'));
      } else if (preg_match('/^([0-9]+)\.([0-9]+)(\.([0-9]+))? ?([0-9]+)?:?([0-9]+)?:?([0-9]+)?/', $s, $matches)) {
      
        // German date format
        $stamp= Date::mktime(
          isset($matches[5]) ? $matches[5] : 0, 
          isset($matches[6]) ? $matches[6] : 0, 
          isset($matches[7]) ? $matches[7] : 0, 
          $matches[2], 
          $matches[1], 
          isset($matches[4]) ? DateParser::yearFor($matches[4]) : date('Y')
        );
      } else if (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})?(Z|([+-]\d{4}))?$/', $s, $matches)) {
      
        // Generalized date format
        $stamp= Date::mktime(
          $matches[4],
          $matches[5],
          (isset($matches[6]) ? $matches[6] : 0),
          $matches[2],
          $matches[3],
          DateParser::yearFor($matches[1])
        );
        
        // If a timezone information has been given, try to convert timestamp into local time
        if (!empty($matches[7])) {
          try(); {
            if (
              ($tz= &new TimeZone($matches[7])) &&
              ($lc= &TimeZone::getLocal())
            ) {
            
              $date= &$lc->convertDate(new Date($stamp), $tz);
              $stamp= $date->getTime();
            }
          } if (catch('IllegalArgumentException', $e)) {
          
            // Ignore, do not modify timestamp...
          }
        }
      } else {
      
        // FIXME: strtotime() returns -1 on failure, but also for the date
        // Jan 01 1970 00:59:59 (in case the underlying OS supports negative
        // timestamps). Unfortunately, no warnings are issued whatsoever, so
        // there is no way to find out if this is the case or if the function 
        // actually failed.
        //
        // I would consider this a bug in PHP (the function should return FALSE
        // or at least raise a warning), but for now, we will have to live 
        // with it.
        if (-1 === ($stamp= strtotime(strtolower($s)))) {
          return throw(new FormatException('Could not parse "'.$s.'"'));
        }
      }
      
      return new Date($stamp);
    }
  }
?>
