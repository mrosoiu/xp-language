<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'io.streams.TextReader',
    'io.streams.MemoryInputStream'
  );

  /**
   * TestCase
   *
   * @see      xp://io.streams.TextReader
   */
  class TextReaderTest extends TestCase {
  
    /**
     * Returns a text reader for a given input string.
     *
     * @param   string str
     * @param   string charset
     * @return  io.streams.TextReader
     */
    protected function newReader($str, $charset= NULL) {
      return new TextReader(new MemoryInputStream($str), $charset);
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readOne() {
      $this->assertEquals('H', $this->newReader('Hello')->read(1));
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readOneUtf8() {
      $this->assertEquals('�', $this->newReader('Übercoder', 'utf-8')->read(1));
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readLength() {
      $this->assertEquals('Hello', $this->newReader('Hello')->read(5));
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readLengthUtf8() {
      $this->assertEquals('�bercoder', $this->newReader('Übercoder', 'utf-8')->read(9));
    }

    /**
     * Test reading
     *
     */
    #[@test, @expect('lang.FormatException')]
    public function readBrokenUtf8() {
      $this->newReader('Hello �', 'utf-8')->read(0x1000);
    }

    /**
     * Test reading
     *
     */
    #[@test, @expect('lang.FormatException')]
    public function readMalformedUtf8() {
      $this->newReader('Hello �bercoder', 'utf-8')->read(0x1000);
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readingDoesNotContinueAfterBrokenCharacters() {
      $r= $this->newReader("Hello �bercoder\n".str_repeat('*', 512), 'utf-8');
      try {
        $r->read(1);
        $this->fail('No exception caught', NULL, 'lang.FormatException');
      } catch (FormatException $expected) {
        // OK
      }
      $this->assertNull($r->read(512));
    }

    /**
     * Test reading "'�:ina" which contains two characters not convertible
     * to iso-8859-1, our internal encoding.
     *
     * @see     http://de.wikipedia.org/wiki/China (the word in the first square brackets on this page).
     */
    #[@test]
    public function readUnconvertible() {
      $this->assertEquals('�ina', $this->newReader('ˈçiːna', 'utf-8')->read());
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function read() {
      $this->assertEquals('Hello', $this->newReader('Hello')->read());
    }

    /**
     * Test reading. Warning: This test "knows" the internal chunk size is 512 bytes.
     *
     */
    #[@test]
    public function chunkLengthWithUtf8() {
      $chunk= str_repeat('x', 511);
      $this->assertEquals($chunk.'�', $this->newReader($chunk.'Ü', 'utf-8')->read(512));
    }

    /**
     * Test reading a source returning encoded bytes only (no US-ASCII inbetween!)
     *
     */
    #[@test]
    public function encodedBytesOnly() {
      $this->assertEquals(
        str_repeat('�', 1024), 
        $this->newReader(str_repeat('Ü', 1024), 'utf-8')->read(1024)
      );
    }

    /**
     * Test reading after EOF
     *
     */
    #[@test]
    public function readAfterEnd() {
      $r= $this->newReader('Hello');
      $this->assertEquals('Hello', $r->read(5));
      $this->assertNull($r->read());
    }

    /**
     * Test reading after EOF
     *
     */
    #[@test]
    public function readMultipleAfterEnd() {
      $r= $this->newReader('Hello');
      $this->assertEquals('Hello', $r->read(5));
      $this->assertNull($r->read());
      $this->assertNull($r->read());
    }

    /**
     * Test reading after EOF
     *
     */
    #[@test]
    public function readLineAfterEnd() {
      $r= $this->newReader('Hello');
      $this->assertEquals('Hello', $r->read(5));
      $this->assertNull($r->readLine());
    }

    /**
     * Test reading after EOF
     *
     */
    #[@test]
    public function readLineMultipleAfterEnd() {
      $r= $this->newReader('Hello');
      $this->assertEquals('Hello', $r->read(5));
      $this->assertNull($r->readLine());
      $this->assertNull($r->readLine());
    }

    /**
     * Test reading
     *
     */
    #[@test]
    public function readZero() {
      $this->assertEquals('', $this->newReader('Hello')->read(0));
    }
        
    /**
     * Test reading lines separated by "\n"
     *
     */
    #[@test]
    public function readLinesSeparatedByLineFeed() {
      $r= $this->newReader("Hello\nWorld");
      $this->assertEquals('Hello', $r->readLine());
      $this->assertEquals('World', $r->readLine());
      $this->assertNull($r->readLine());
    }
        
    /**
     * Test reading lines separated by "\r"
     *
     */
    #[@test]
    public function readLinesSeparatedByCarriageReturn() {
      $r= $this->newReader("Hello\rWorld");
      $this->assertEquals('Hello', $r->readLine());
      $this->assertEquals('World', $r->readLine());
      $this->assertNull($r->readLine());
    }
        
    /**
     * Test reading lines separated by "\r\n"
     *
     */
    #[@test]
    public function readLinesSeparatedByCRLF() {
      $r= $this->newReader("Hello\r\nWorld");
      $this->assertEquals('Hello', $r->readLine());
      $this->assertEquals('World', $r->readLine());
      $this->assertNull($r->readLine());
    }

    /**
     * Test reading an empty line
     *
     */
    #[@test]
    public function readEmptyLine() {
      $r= $this->newReader("Hello\n\nWorld");
      $this->assertEquals('Hello', $r->readLine());
      $this->assertEquals('', $r->readLine());
      $this->assertEquals('World', $r->readLine());
      $this->assertNull($r->readLine());
    }

    /**
     * Test reading lines
     *
     */
    #[@test]
    public function readLinesUtf8() {
      $r= $this->newReader("Über\nCoder", 'utf-8');
      $this->assertEquals('�ber', $r->readLine());
      $this->assertEquals('Coder', $r->readLine());
      $this->assertNull($r->readLine());
    }
  }
?>
