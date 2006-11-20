<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'unittest.TestCase',
    'peer.ftp.DefaultFtpListParser',
    'peer.ftp.FtpDir',
    'peer.ftp.FtpEntry',
    'util.Date'
  );

  /**
   * Tests default list parser
   *
   * @see      xp://peer.ftp.DefaultFtpListParser
   * @purpose  Unit Test
   */
  class DefaultFtpListParserTest extends TestCase {
    
    /**
     * Setup this testcase
     *
     * @access  public
     */
    function setUp() {
      $this->fixture= &new DefaultFtpListParser();
    }
    
    /**
     * Test directory
     *
     * @access  public
     */
    #[@test]
    function dotDirectory() {
      $e= &$this->fixture->entryFrom('drwx---r-t 37 p159995 ftpusers 4096 Apr 4 20:16 .');

      $this->assertSubclass($e, 'peer.ftp.FtpDir') &&
      $this->assertEquals('.', $e->getName()) &&
      $this->assertEquals(37, $e->getNumlinks()) &&
      $this->assertEquals('p159995', $e->getUser()) &&
      $this->assertEquals('ftpusers', $e->getGroup()) &&
      $this->assertEquals(4096, $e->getSize()) &&
      $this->assertEquals(new Date('04.04.'.date('Y').' 20:16'), $e->getDate()) &&
      $this->assertEquals(704, $e->getPermissions());
    }

    /**
     * Test file
     *
     * @access  public
     */
    #[@test]
    function regularFile() {
      $e= &$this->fixture->entryFrom('-rw----r-- 1 p159995 ftpusers 415 May 23 2000 write.html');

      $this->assertSubclass($e, 'peer.ftp.FtpEntry') &&
      $this->assertEquals('write.html', $e->getName()) &&
      $this->assertEquals(1, $e->getNumlinks()) &&
      $this->assertEquals('p159995', $e->getUser()) &&
      $this->assertEquals('ftpusers', $e->getGroup()) &&
      $this->assertEquals(415, $e->getSize()) &&
      $this->assertEquals(new Date('23.05.2000'), $e->getDate()) &&
      $this->assertEquals(604, $e->getPermissions());
    }

    /**
     * Test file
     *
     * @access  public
     */
    #[@test]
    function whitespaceInFileName() {
      $e= &$this->fixture->entryFrom('-rw----r-- 1 p159995 ftpusers 415 May 23 2000 answer me.html');

      $this->assertSubclass($e, 'peer.ftp.FtpEntry') &&
      $this->assertEquals('answer me.html', $e->getName()) &&
      $this->assertEquals(1, $e->getNumlinks()) &&
      $this->assertEquals('p159995', $e->getUser()) &&
      $this->assertEquals('ftpusers', $e->getGroup()) &&
      $this->assertEquals(415, $e->getSize()) &&
      $this->assertEquals(new Date('23.05.2000'), $e->getDate()) &&
      $this->assertEquals(604, $e->getPermissions());
    }
  }
?>
