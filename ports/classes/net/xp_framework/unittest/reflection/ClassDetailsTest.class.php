<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('unittest.TestCase');

  define('APIDOC_TAG',        0x0001);
  define('APIDOC_VALUE',      0x0002);

  /**
   * Tests the class details gathering internals
   *
   * @see      xp://lang.XPClass#detailsForClass
   * @purpose  Unit test
   */
  class ClassDetailsTest extends TestCase {
  
    /**
     * Helper method that parses an apidoc comment and returns the matches
     *
     * @access  protected
     * @param   string comment
     * @return  array<string[]> matches
     * @throws  unittest.AssertionFailedError
     */
    function parseComment($comment) {
      $comment= trim($comment);
      if (!preg_match_all(
        '/@([a-z]+)\s*([^<\r\n]+<[^>]+>|[^\r\n ]+) ?([^\r\n ]+)? ?(default ([^\r\n ]+))?/', 
        $comment,
        $matches, 
        PREG_SET_ORDER
      )) {
        $this->fail('Could not parse comment', $actual= FALSE, $expect= TRUE);
        return;
      }

      // Set these to empty values
      $annotations= array();
      $name= NULL;  
      
      // Initialize details array
      $details= array(
        DETAIL_MODIFIERS    => 0,
        DETAIL_ARGUMENTS    => array(),
        DETAIL_RETURNS      => 'void',
        DETAIL_THROWS       => array(),
        DETAIL_COMMENT      => trim(preg_replace('/\n\s*\* ?/', "\n", "\n".substr(
          $comment, 
          4,                              // "/**\n"
          strpos($comment, '* @')- 2      // position of first details token
        ))),
        DETAIL_ANNOTATIONS  => $annotations,
        DETAIL_NAME         => $name
      );
      
      foreach ($matches as $match) {
        switch ($match[1]) {
          case 'access':
          case 'model':
            $details[DETAIL_MODIFIERS] |= constant('MODIFIER_'.strtoupper($match[2]));
            break;

          case 'param':
            $details[DETAIL_ARGUMENTS][]= &new Argument(
              isset($match[3]) ? $match[3] : 'param',
              $match[2],
              isset($match[4]),
              isset($match[4]) ? $match[5] : NULL
            );
            break;

          case 'return':
            $details[DETAIL_RETURNS]= $match[2];
            break;

          case 'throws': 
            $details[DETAIL_THROWS][]= $match[2];
            break;
        }
      }
      
      return $details;
    }
    
    /**
     * Tests the parseComment() helper
     *
     * @access  public
     */
    #[@test, @expect('unittest.AssertionFailedError')]
    function testParseComment() {
      $this->parseComment('NOT-A-COMMENT');
    }
    
    /**
     * Protected helper method
     *
     * @access  protected
     * @param   int modifiers
     * @param   string comment
     * @return  bool
     * @throws  unittest.AssertionFailedError
     */
    function assertAccessFlags($modifiers, $comment) {
      if (!($details= $this->parseComment($comment))) return;
      return $this->assertEquals($modifiers, $details[DETAIL_MODIFIERS]);
    }
    
    /**
     * Tests separation of the comment from the "tags part".
     *
     * @access  public
     */
    #[@test]
    function commentString() {
      $details= $this->parseComment('
        /**
         * A protected method
         *
         * Note: Not compatible with PHP 4.1.2!
         *
         * @access  protected
         * @param   string param1
         */
      ');
      $this->assertEquals(
        "A protected method\n\nNote: Not compatible with PHP 4.1.2!",
        $details[DETAIL_COMMENT]
      );
    }

    /**
     * Tests comment is empty when no comment is available in apidoc
     *
     * @access  public
     */
    #[@test]
    function noCommentString() {
      $details= $this->parseComment('
        /**
         * @access  protected
         */
      ');
      $this->assertEquals(
        '',
        $details[DETAIL_COMMENT]
      );
    }
    
    /**
     * Tests parsing of the "access" tag
     *
     * @access  public
     */
    #[@test]
    function publicAccess() {
      $this->assertAccessFlags(MODIFIER_PUBLIC, '
        /**
         * A public method
         *
         * @access  public
         */
      ');
    }

    /**
     * Tests parsing of the "access" tag
     *
     * @access  public
     */
    #[@test]
    function protectedAccess() {
      $this->assertAccessFlags(MODIFIER_PROTECTED, '
        /**
         * A protected method
         *
         * @access  protected
         */
      ');
    }

    /**
     * Tests parsing of the "access" tag
     *
     * @access  public
     */
    #[@test]
    function privateAccess() {
      $this->assertAccessFlags(MODIFIER_PRIVATE, '
        /**
         * A private method
         *
         * @access  private
         */
      ');
    }

    /**
     * Tests parsing of the "access" tag
     *
     * @access  public
     */
    #[@test]
    function staticAccess() {
      $this->assertAccessFlags(MODIFIER_PUBLIC | MODIFIER_STATIC, '
        /**
         * A public method
         *
         * @model   static
         * @access  public
         */
      ');
    }

    /**
     * Tests parsing of the "param" tag with a scalar parameter
     *
     * @access  public
     */
    #[@test]
    function scalarParameter() {
      $details= $this->parseComment('
        /**
         * A protected method
         *
         * @access  protected
         * @param   string param1
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('param1', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('string', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
      }
    }

    /**
     * Tests parsing of the "param" tag with an array parameter
     *
     * @access  public
     */
    #[@test]
    function arrayParameter() {
      $details= $this->parseComment('
        /**
         * Another protected method
         *
         * @access  protected
         * @param   string[] param1
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('param1', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('string[]', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
      }
    }

    /**
     * Tests parsing of the "param" tag with an object parameter
     *
     * @access  public
     */
    #[@test]
    function objectParameter() {
      $details= $this->parseComment('
        /**
         * Yet another protected method
         *
         * @access  protected
         * @param   &util.Date param1
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('param1', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('util.Date', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertTrue($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
      }
    }

    /**
     * Tests parsing of the "param" tag with a parameter with default value
     *
     * @access  public
     */
    #[@test]
    function defaultParameter() {
      $details= $this->parseComment('
        /**
         * A private method
         *
         * @access  private
         * @param   int param1 default 1
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('param1', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('int', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertTrue($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
        $this->assertEquals('1', $details[DETAIL_ARGUMENTS][0]->getDefault());
      }
    }
    
    /**
     * Tests parsing of the "param" tag with an generic parameter
     *
     * @access  public
     */
    #[@test]
    function genericArrayParameter() {
      $details= $this->parseComment('
        /**
         * Final protected method
         *
         * @model   final
         * @access  protected
         * @param   array<string, string> map
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('map', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('array<string, string>', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
      }
    }

    /**
     * Tests parsing of the "param" tag with an generic parameter
     *
     * @access  public
     */
    #[@test]
    function genericObjectParameter() {
      $details= $this->parseComment('
        /**
         * Abstract protected method
         *
         * @model   abstract
         * @access  protected
         * @param   &lang.Collection<&lang.Object> param1
         */
      ');
      if ($this->assertClass($details[DETAIL_ARGUMENTS][0], 'lang.reflect.Argument')) {
        $this->assertEquals('param1', $details[DETAIL_ARGUMENTS][0]->getName());
        $this->assertEquals('lang.Collection<&lang.Object>', $details[DETAIL_ARGUMENTS][0]->getType());
        $this->assertFalse($details[DETAIL_ARGUMENTS][0]->isOptional());
        $this->assertTrue($details[DETAIL_ARGUMENTS][0]->isPassedByReference());
      }
    }
    
    /**
     * Tests parsing of the "throws" tag
     *
     * @access  public
     */
    #[@test]
    function throwsList() {
      $details= $this->parseComment('
        /**
         * Test method
         *
         * @throws  lang.IllegalArgumentException
         * @throws  lang.IllegalAccessException
         */
      ');
      $this->assertEquals('lang.IllegalArgumentException', $details[DETAIL_THROWS][0]);
      $this->assertEquals('lang.IllegalAccessException', $details[DETAIL_THROWS][1]);
    }
 
     /**
     * Tests parsing of the "return" tag
     *
     * @access  public
     */
    #[@test]
    function returnType() {
      $details= $this->parseComment('
        /**
         * Test method
         *
         * @return  int
         */
      ');
      $this->assertEquals('int', $details[DETAIL_RETURNS]);
    }
 }
?>
