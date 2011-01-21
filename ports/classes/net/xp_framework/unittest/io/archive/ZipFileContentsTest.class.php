<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('net.xp_framework.unittest.io.archive.ZipFileTest', 'io.streams.Streams');

  /**
   * Base class for testing zip file contents
   *
   * @see   xp://net.xp_framework.unittest.io.archive.MalformedZipFileTest
   * @see   xp://net.xp_framework.unittest.io.archive.vendors.ZipFileVendorTest
   */
  abstract class ZipFileContentsTest extends ZipFileTest {

    /**
     * Returns entry content
     *
     * @param   io.archive.zip.ZipEntry entry
     * @return  string
     */
    protected function entryContent(ZipEntry $entry) {
      if ($entry->isDirectory()) {
        return NULL;
      } else {
        return (string)Streams::readAll($entry->getInputStream());
      }
    }

    /**
     * Returns an array of entries in a given zip file
     *
     * @param   io.archive.zip.ZipArchiveReader reader
     * @return  [:string] content
     */
    protected abstract function entriesWithContentIn(ZipArchiveReader $zip);

    /**
     * Tests reading an empty zip file
     *
     */
    #[@test]
    public function nofiles() {
      $this->assertEquals(
        array(),
        $this->entriesWithContentIn($this->archiveReaderFor('fixtures', 'nofiles'))
      );
    }

    /**
     * Tests reading a zipfile with one entry called "hello.txt" in its 
     * root directory.
     *
     */
    #[@test]
    public function onefile() {
      $this->assertEquals(
        array('hello.txt' => 'World'),
        $this->entriesWithContentIn($this->archiveReaderFor('fixtures', 'onefile'))
      );
    }

    /**
     * Tests reading a zipfile with one entry called "dir" in its 
     * root directory.
     *
     */
    #[@test]
    public function onedir() {
      $this->assertEquals(
        array('dir/' => NULL),
        $this->entriesWithContentIn($this->archiveReaderFor('fixtures', 'onedir'))
      );
    }

    /**
     * Tests reading a zipfile with two files inide its root directory
     *
     */
    #[@test]
    public function twofiles() {
      $this->assertEquals(
        array('one.txt' => 'Eins', 'two.txt' => 'Zwei'),
        $this->entriesWithContentIn($this->archiveReaderFor('fixtures', 'twofiles'))
      );
    }
  }
?>
