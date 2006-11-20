<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Parses output from a FTP LIST command from Un*x FTP daemons.
   *
   * @test     xp://net.xp_framework.unittest.peer.DefaultFtpListParserTest
   * @see      xp://peer.ftp.FtpListParser
   * @purpose  FTP LIST parser implementation
   */
  class DefaultFtpListParser extends Object {

    /**
     * Parse raw listing entry.
     *
     * @access  public
     * @param   string raw a single line
     * @return  &peer.ftp.FtpEntry
     */
    function &entryFrom($raw) {
      sscanf(
        $raw, 
        '%s %d %s %s %d %s %d %[^ ] %[^$]',
        $permissions,
        $numlinks,
        $user,
        $group,
        $size,
        $month,
        $day,
        $date,
        $filename
      );
      
      if ('d' == $permissions{0}) {
        $e= &new FtpDir($filename);
      } else {
        $e= &new FtpEntry($filename);
      }

      $d= &new Date($month.' '.$day.' '.(strstr($date, ':') ? date('Y').' '.$date : $date));

      // Check for "recent" file which are specified "HH:MM" instead
      // of year for the last 6 month (as specified in coreutils/src/ls.c)
      if (strstr($date, ':')) {
        $now= &Date::now();
        if ($d->getMonth() > $now->getMonth()) $d->year--;
      }

      $e->setPermissions(substr($permissions, 1));
      $e->setNumlinks($numlinks);
      $e->setUser($user);
      $e->setGroup($group);
      $e->setSize($size);
      $e->setDate($d);
      return $e;
    }
  
  } implements(__FILE__, 'peer.ftp.FtpListParser');
?>
