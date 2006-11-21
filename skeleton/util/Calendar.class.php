<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  define('CAL_SEC_HOUR',    3600);
  define('CAL_SEC_DAY',     86400);
  define('CAL_SEC_WEEK',    604800);
  
  define('CAL_DST_EU',      0x0000);
  define('CAL_DST_US',      0x0001);
  
  uses('util.Date');
  
  /**
   * Calendar class
   *
   * @model    static
   * @test     xp://net.xp_framework.unittest.DateTest
   * @purpose  Utility functions for date calculations
   */
  class Calendar extends Object {

    /**
     * Calculates start of DST (daylight savings time).
     *
     * This is the last Sunday of March for Europe, the first Sunday of 
     * April in the U.S.
     *
     * @model   static
     * @access  public
     * @param   int year default -1 Year, defaults to current year
     * @param   int method default CAL_DST_EU Method to calculate (CAL_DST_EU|CAL_DST_US)
     * @return  &util.Date
     */
    function &dstBegin($year= -1, $method= CAL_DST_EU) {
      if (-1 == $year) $year= date('Y');
      $i= 0;
      $ofs= ($method == CAL_DST_US) ? 1 : -1;
      do {
        $w= date('w', $m= mktime(0, 0, 0, 4, $i, $year));
        $i+= $ofs;
      } while ($w > 0);

      $d= &new Date($m);
      return $d;
    }
  
    /**
     * Calculates end of DST (daylight savings time)
     * This is the last Sunday of October
     *
     * @model   static
     * @access  public
     * @param   int year default -1 Year, defaults to current year
     * @return  &util.Date
     */
    function &dstEnd($year= -1) {
      if (-1 == $year) $year= date('Y');
      $i= 0;
      do {
        $w= date('w', $m= mktime(0, 0, 0, 11, $i--, $year));
      } while ($w > 0);

      $d= &new Date($m);
      return $d;
    }
    
    /**
     * Retrieve whether a given date object is in daylight savings time.
     *
     * @model   static
     * @access  public
     * @param   &util.Date date
     * @param   int method default CAL_DST_EU Method to calculate (CAL_DST_EU|CAL_DST_US)
     * @return  bool
     */
    function inDst(&$date, $method= CAL_DST_EU) {
      return (
        $date->isAfter(Calendar::dstBegin($date->getYear(), $method)) &&
        $date->isBefore(Calendar::dstEnd($date->getYear()))
      );
    }
  
    /**
     * Calculates the amount of workdays between to dates. Workdays are 
     * defined as Monday through Friday.
     *
     * This method takes an optional argument, an array of the following
     * form:
     *
     * <code>
     *   $holidays[gmmktime(...)]= TRUE;
     * </code>
     *
     * @access  public
     * @param   &util.Date start
     * @param   &util.Date end
     * @param   array holidays default array() holidays to be included in calculation
     * @return  int number of workdays
     */
    function workdays(&$start, &$end, $holidays= array()) {
      $s= $start->getTime();
      $e= $end->getTime();

      // For holidays, we have to compare to midnight
      // else, don't calculate this
      if (!empty($holidays)) $s-= $s % CAL_SEC_DAY;
      
      // Is there a more intelligent way of doing this?
      $diff= floor(($e - $s) / CAL_SEC_DAY);
      for ($i= $s; $i <= $e; $i+= CAL_SEC_DAY) {
        $diff-= ((date('w', $i)+ 6) % 7 > 4 || isset($holidays[$i]));
      }
      
      return $diff+ 1;
    }
    
    /**
     * Return midnight of a given date
     *
     * @model   static
     * @access  public
     * @param   &util.Date date
     * @return  &util.Date
     */
    function &midnight(&$date) {
      $d= &new Date(mktime(0, 0, 0, $date->mon, $date->mday, $date->year));
      return $d;
    }
    
    /**
     * Return beginning of month for a given date. E.g., given a date
     * 2003-06-08, the function will return 2003-06-01 00:00:00.
     *
     * @model   static
     * @access  public
     * @param   &util.Date date
     * @return  &util.Date
     */
    function &monthBegin(&$date) {
      $d= &new Date(mktime(0, 0, 0, $date->mon, 1, $date->year));
      return $d;
    }

    /**
     * Return end of month for a given date. E.g., given a date
     * 2003-06-08, the function will return 2003-06-30 23:59:59.
     *
     * @model   static
     * @access  public
     * @param   &util.Date date
     * @return  &util.Date
     */
    function &monthEnd(&$date) {
      $d= &new Date(mktime(23, 59, 59, $date->mon+ 1, 0, $date->year));
      return $d;
    }

    /**
     * Helper method for Calendar::week
     *
     * @model   static
     * @access  private
     * @param   int stamp
     * @param   int year
     * @return  int
     */
    function caldiff($stamp, $year) {
      $d4= mktime(0, 0, 0, 1, 4, $year);
      return floor(1.05 + ($stamp- $d4) / CAL_SEC_WEEK+ ((date('w', $d4)+ 6) % 7) / 7);
    }
  
    /**
     * Returns calendar week for a day
     *
     * @model   static
     * @access  public
     * @param   &util.Date date
     * @return  int calendar week
     * @see     http://www.salesianer.de/util/kalwoch.html 
     */
    function week(&$date) {
      $d= $date->getTime();
      $y= $date->year + 1;
      do {
        $w= Calendar::caldiff($d, $y);
        $y--;
      } while ($w < 1);

      return (int)$w;
    }
    
    /**
     * Get first of advent for given year
     *
     * @model   static
     * @access  public
     * @param   int year default -1 year, defaults to this year
     * @return  &util.Date for date of the first of advent
     * @see     http://www.salesianer.de/util/kalfaq.html
     */
    function &advent($year= -1) {
      if (-1 == $year) $year= date('Y');
     
      $s= mktime(0, 0, 0, 11, 26, $year);
      while (0 != date('w', $s)) {
        $s+= CAL_SEC_DAY;
      }
      
      $d= &new Date($s);
      return $d;
    }
    
    /**
     * Get easter date for given year
     *
     * @model   static
     * @access  public
     * @param   int year default -1 Year, defaults to this year
     * @return  &util.Date date for Easter date
     * @see     http://www.koenigsmuenster.de/rsk/epakte.htm
     * @see     http://www.salesianer.de/util/kalfaq.html
     * @see     php://easter-date#user_contrib
     */
    function &easter($year= -1) {
      if (-1 == $year) $year= date('Y');
      
      $g = $year % 19;
      $c = (int)($year / 100);
      $h = (int)($c - ($c / 4) - ((8*  $c + 13) / 25) + 19 * $g + 15) % 30;
      $i = (int)$h - (int)($h / 28) * (1 - (int)($h / 28)* (int)(29 / ($h+ 1)) * ((int)(21 - $g) / 11));
      $j = ($year + (int)($year / 4) + $i + 2 - $c + (int)($c / 4)) % 7;
      $l = $i - $j;
      $m = 3 + (int)(($l + 40) / 44);
      $d = $l + 28 - 31 * ((int)($m / 4));

      $d= &new Date(mktime(0, 0, 0, $m, $d, $year));
      return $d;
    }
    
    /**
     * Returns whether a year is a leap year
     *
     * @model   static
     * @access  protected
     * @param   int year
     * @return  bool TRUE if the given year is a leap year
     */
    function isLeapYear($year) {
      return $year % 400 == 0 || ($year > 1582 && $year % 100 == 0 ? FALSE : $year % 4 == 0);
    }
  }
?>
