<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.Date');

  /**
   * Helps constructing a date range
   *
   * If you want to limit your results to documents that were published 
   * within a specific date range, then you can use the "daterange:" 
   * query term to accomplish this. 
   *
   * The "daterange:" query term must be in the following format:
   * <pre>
   *   daterange:<start_date>-<end date> 
   * </pre>
   * where
   * <pre>
   *   <start_date> = Julian date indicating the start of the date range
   *   <end date> = Julian date indicating the end of the date range 
   * </pre>
   *
   * Example:
   * <code>
   *   uses('com.google.util.GoogleDateRange');
   *
   *   $query= 'Google';
   *   with ($range= &GoogleDateRange::forDates(
   *     Date::fromString('Dec 14 2003'), 
   *     Date::now())
   *   ); {
   *     $query.= ' '.$range->toString();
   *   }
   *
   *   var_dump($query);    // "Google daterange:2452988-2453035"
   * </code>
   * 
   * @see      http://www.google.com/apis/reference.html#2_2
   * @purpose  Helper class
   */
  class GoogleDateRange extends Object {
    var
      $start    = NULL,
      $end      = NULL;
      
    /**
     * Returns a date range for the given start and end dates
     *
     * @model   static
     * @access  public
     * @param   &util.Date start
     * @param   &util.Date end
     * @return  &com.google.util.GoogleDateRange
     */
    function &forDates(&$start, &$end) {
      $range= &new GoogleDateRange();
      $range->setStart($start);
      $range->setEnd($end);
      return $range;
    }

    /**
     * Set Start
     *
     * @access  public
     * @param   &util.Date start
     */
    function setStart(&$start) {
      $this->start= &$start;
    }

    /**
     * Get Start
     *
     * @access  public
     * @return  &util.Date
     */
    function &getStart() {
      return $this->start;
    }

    /**
     * Set End
     *
     * @access  public
     * @param   &util.Date end
     */
    function setEnd(&$end) {
      $this->end= &$end;
    }

    /**
     * Get End
     *
     * @access  public
     * @return  &util.Date
     */
    function &getEnd() {
      return $this->end;
    }
    
    /**
     * Converts a Date object to Julian daycount.
     *
     * The Julian date is calculated by the number of days since 
     * January 1, 4713 BC.
     *
     * Note: Returns zero (0) on failure.
     *
     * @model   static
     * @access  protected
     * @param   &util.Date date
     * @return  int
     */
    function dateToJulian(&$date) {
      with ($iyear= $date->getYear(), $imonth= $date->getMonth(), $iday= $date->getDay()); {
      
        // Check for invalid dates
        if (
          $iyear == 0 || $iyear < -4714 || 
          $imonth <= 0 || $imonth > 12 || 
          $iday <= 0 || $iday > 31
        ) {
          return 0;
        }
        
        // Check for dates before SDN 1 (Nov 25, 4714 B.C.)
        if ($iyear == -4714) {
          if ($imonth < 11) return 0;
          if ($imonth == 11 && $iday < 25) return 0;
        }
        
        // Make year always a positive number
        $year= $iyear + 4800 + ($iyear < 0);
        
        // Adjust the start of the year
        if ($imonth > 2) {
          $month= $imonth - 3;
        } else {
          $month= $imonth + 9;
          $year--;
        }
        
        return (int)(
          floor((floor($year / 100) * 146097) / 4) +
          floor((($year % 100) * 1461) / 4) +
          floor(($month * 153 + 2) / 5) +
          $iday -
          32045
        );
      }
    }

    /**
     * Creates string representation of this date range
     *
     * Example:
     * <pre>
     *   daterange:2452122-2452234
     * </pre>
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        'daterange:%d-%d',
        GoogleDateRange::dateToJulian($this->start),
        GoogleDateRange::dateToJulian($this->end)
      );
    }
  }
?>
