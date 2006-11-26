<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Parses output from a FTP LIST command from Windows FTP daemons.
   *
   * @test     xp://net.xp_framework.unittest.peer.WindowsFtpListParserTest
   * @see      xp://peer.ftp.FtpListParser
   * @purpose  FTP LIST parser implementation
   */
  class WindowsFtpListParser extends Object {

    /**
     * Parse raw listing entry.
     *
     * @access  public
     * @param   string raw a single line
     * @return  &peer.ftp.FtpEntry
     */
    function &entryFrom($raw) {
      preg_match(
        '/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +(<DIR>)?([0-9]+)? +(.+)/',
        $raw,
        $result
      );

      if ($result[7]) {
        $e= &new FtpDir($result[9]);
      } else {
        $e= &new FtpEntry($result[9]);
      }

      $e->setPermissions(0);
      $e->setNumlinks(0);
      $e->setUser(NULL);
      $e->setGroup(NULL);
      $e->setSize(intval($result[8]));
      $e->setDate(new Date(sprintf(
        '%02d/%02d/%02d %02d:%02d%02s', 
        $result[1], 
        $result[2], 
        $result[3], 
        $result[4], 
        $result[5], 
        $result[6]
      )));
      return $e;
    }
  
  } implements(__FILE__, 'peer.ftp.FtpListParser');
?>
