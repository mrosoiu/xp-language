<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'text.csv.Quoting'
  );

  /**
   * TestCase
   *
   * @see      xp://text.csv.Quoting
   */
  class QuotingTest extends TestCase {
  
    /**
     * Test ';' is quoted in default strategy
     *
     */
    #[@test]
    public function delimiterQuotedInDefault() {
      $this->assertTrue(text�csv�Quoting::$DEFAULT->necessary(';', ';', '"'), 'DEFAULT');
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary(';', ';', '"'), 'EMPTY');
    }

    /**
     * Test '"' is quoted in default strategy
     *
     */
    #[@test]
    public function quoteQuotedInDefault() {
      $this->assertTrue(text�csv�Quoting::$DEFAULT->necessary('"', ';', '"'), 'DEFAULT');
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary('"', ';', '"'), 'EMPTY');
    }

    /**
     * Test Mac-style new line is quoted in default strategy
     *
     */
    #[@test]
    public function macNewLinesQuotedInDefault() {
      $this->assertTrue(text�csv�Quoting::$DEFAULT->necessary("\r", ';', '"'), 'DEFAULT');
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary("\r", ';', '"'), 'EMPTY');
    }

    /**
     * Test Un*x-style new line is quoted in default strategy
     *
     */
    #[@test]
    public function unixNewLinesQuotedInDefault() {
      $this->assertTrue(text�csv�Quoting::$DEFAULT->necessary("\n", ';', '"'), 'DEFAULT');
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary("\r", ';', '"'), 'EMPTY');
    }

    /**
     * Test Windows-style new line is quoted in default strategy
     *
     */
    #[@test]
    public function windowsNewLinesQuotedInDefault() {
      $this->assertTrue(text�csv�Quoting::$DEFAULT->necessary("\r\n", ';', '"'), 'DEFAULT');
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary("\r\n", ';', '"'), 'EMPTY');
    }

    /**
     * Test empty values are not quoted in the default strategy
     *
     */
    #[@test]
    public function emptyIsNotQuotedInDefault() {
      $this->assertFalse(text�csv�Quoting::$DEFAULT->necessary('', ';', '"'));
    }

    /**
     * Test empty values are not quoted in the default strategy
     *
     */
    #[@test]
    public function emptyIsQuotedInQuoteEmpty() {
      $this->assertTrue(text�csv�Quoting::$EMPTY->necessary('', ';', '"'));
    }

    /**
     * Test any of the above are quoted in the "always" strategy
     *
     */
    #[@test]
    public function anythingIsQuotedInAlways() {
      foreach (array('', ';', '"', "\r", "\n", "\r\n", 'A', 'Hello') as $value) {
        $this->assertTrue(text�csv�Quoting::$ALWAYS->necessary($value, ';', '"'), $value);
      }
    }

    /**
     * Test creating a quoting strategy that never quotes anything. This 
     * is for unittesting purposes only, such a strategy would not make
     * sense in real-life situations!
     *
     */
    #[@test]
    public function never() {
      $never= newinstance('text.csv.QuotingStrategy', array(), '{
        public function necessary($value, $delimiter, $quote) {
          return FALSE;
        }
      }');
      foreach (array('', ';', '"', "\r", "\n", "\r\n", 'A', 'Hello') as $value) {
        $this->assertFalse($never->necessary($value, ';', '"'), $value);
      }
    }
  }
?>
