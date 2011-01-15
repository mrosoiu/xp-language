<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'io.archive.zip.RandomAccessZipReaderImpl', 
    'io.archive.zip.SequentialZipReaderImpl', 
    'io.archive.zip.ZipEntries',
    'io.archive.zip.ZipIterator'
  );

  /**
   * Read from a zip file
   *
   * Usage in foreach
   * ----------------
   * <code>
   *   $z= ZipFile::open(new FileInputStream(new File('dist.zip')));
   *   foreach ($reader->entries() as $entry) {
   *     // ...
   *   }
   * </code>
   *
   * Usage with iterator
   * -------------------
   * <code>
   *   $z= ZipFile::open(new FileInputStream(new File('dist.zip')));
   *   $it= $z->iterator();
   *   while ($it->hasNext()) {
   *     $entry= $it->next();
   *     // ...
   *   }
   * </code>
   *
   * @test    xp://net.xp_framework.unittest.io.archive.ZipFileEntriesTest
   * @test    xp://net.xp_framework.unittest.io.archive.ZipFileIteratorTest
   * @see     xp://io.archive.zip.ZipArchive#open
   */
  class ZipArchiveReader extends Object {
    protected
      $impl = NULL;

    /**
     * Creation constructor
     *
     * @param   io.streams.InputStream stream
     */
    public function __construct(InputStream $stream) {
      if ($stream instanceof Seekable) {
        $this->impl= new RandomAccessZipReaderImpl($stream);
      } else {
        $this->impl= new SequentialZipReaderImpl($stream);
      }
    }

    /**
     * Returns a list of all entries in this zip file
     *
     * @return  io.archive.zip.ZipEntries
     */
    public function entries() {
      return new ZipEntries($this->impl);
    }

    /**
     * Returns an iterator of all entries in this zip file
     *
     * @return  io.archive.zip.ZipIterator
     */
    public function iterator() {
      return new ZipIterator($this->impl);
    }
  }
?>
