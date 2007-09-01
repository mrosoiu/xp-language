<?php
/* This class is part of the XP framework
 *
 * $Id: StringTest.class.php 10171 2007-04-29 17:10:45Z friebe $ 
 */

  namespace net::xp_framework::unittest::core::types;

  ::uses(
    'unittest.TestCase',
    'lang.types.String'
  );

  /**
   * TestCase
   *
   * @see      xp://lang.types.String
   * @purpose  Unittest
   */
  class StringTest extends unittest::TestCase {

    /**
     * Setup this test. Forces input and output encoding to ISO-8859-1
     *
     */
    public function setUp() {
      iconv_set_encoding('input_encoding', 'ISO-8859-1');
      iconv_set_encoding('output_encoding', 'ISO-8859-1');
    }

    /**
     * Test a string with an incomplete multibyte character in it
     *
     */
    #[@test, @expect('lang.FormatException')]
    public function incompleteMultiByteCharacter() {
      new lang::types::String('�', 'UTF-8');
    }
  
    /**
     * Test a string with an illegal character in it
     *
     */
    #[@test, @expect('lang.FormatException')]
    public function illegalCharacter() {
      new lang::types::String('�', 'US-ASCII');
    }

    /**
     * Test
     *
     */
    #[@test]
    public function usAsciiString() {
      $str= new lang::types::String('Hello');
      $this->assertEquals('Hello', $str->getBytes());
      $this->assertEquals(5, $str->length());
    }

    /**
     * Test a string containing German umlauts
     *
     */
    #[@test]
    public function umlautString() {
      $str= new lang::types::String('H�llo');
      $this->assertEquals('Hällo', $str->getBytes());
      $this->assertEquals(5, $str->length());
    }

    /**
     * Test a string with UTF-8 in it
     *
     */
    #[@test]
    public function utf8String() {
      $this->assertEquals(
        new lang::types::String('Hällo', 'UTF-8'),
        new lang::types::String('H�llo', 'ISO-8859-1')
      );
    }

    /**
     * Test translatiom
     *
     */
    #[@test]
    public function transliteration() {
      $this->assertEquals(
        'Trenciansky kraj', 
        ::create(new lang::types::String('Trenčiansky kraj', 'UTF-8'))->toString()
      );
    }

    /**
     * Test indexOf() method
     *
     */
    #[@test]
    public function indexOf() {
      $str= new lang::types::String('H�llo');
      $this->assertEquals(1, $str->indexOf('�'));
      $this->assertEquals(1, $str->indexOf(new lang::types::String('�')));
      $this->assertEquals(-1, $str->indexOf(''));
      $this->assertEquals(-1, $str->indexOf('4'));
    }

    /**
     * Test contains() method
     *
     */
    #[@test]
    public function contains() {
      $str= new lang::types::String('H�llo');
      $this->assertTrue($str->contains('H'));
      $this->assertTrue($str->contains('�'));
      $this->assertTrue($str->contains('o'));
      $this->assertFalse($str->contains(''));
      $this->assertFalse($str->contains('4'));
    }

    /**
     * Test substring() method
     *
     */
    #[@test]
    public function substring() {
      $str= new lang::types::String('H�llo');
      $this->assertEquals(new lang::types::String('�llo'), $str->substring(1));
      $this->assertEquals(new lang::types::String('ll'), $str->substring(2, -1));
      $this->assertEquals(new lang::types::String('o'), $str->substring(-1, 1));
    }

    /**
     * Test startsWith() method
     *
     */
    #[@test]
    public function startsWith() {
      $str= new lang::types::String('www.m�ller.com');
      $this->assertTrue($str->startsWith('www.'));
      $this->assertFalse($str->startsWith('ww.'));
      $this->assertFalse($str->startsWith('m�ller'));
    }

    /**
     * Test startsWith() method
     *
     */
    #[@test]
    public function endsWith() {
      $str= new lang::types::String('www.m�ller.com');
      $this->assertTrue($str->endsWith('.com'));
      $this->assertTrue($str->endsWith('�ller.com'));
      $this->assertFalse($str->endsWith('.co'));
      $this->assertFalse($str->endsWith('m�ller'));
    }

    /**
     * Test replace() method
     *
     */
    #[@test]
    public function replace() {
      $str= new lang::types::String('www.m�ller.com');
      $this->assertEquals(new lang::types::String('m�ller'), $str->replace('www.')->replace('.com'));
      $this->assertEquals(new lang::types::String('muller'), $str->replace('�', 'u'));
    }
  }
?>
