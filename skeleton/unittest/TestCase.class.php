<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'util.profiling.Timer',
    'unittest.AssertionFailedError',
    'unittest.PrerequisitesNotMetError',
    'lang.MethodNotImplementedException'
  );

  /**
   * Test case
   *
   * @see      php://assert
   * @purpose  Base class
   */
  class TestCase extends Object {
    var
      $name     = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     */
    function __construct($name) {
      $this->name= $name;
    }

    /**
     * Get Name
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }

    /**
     * Fail this test case
     *
     * @access  public
     * @param   string reason
     * @param   mixed actual
     * @param   mixed expect
     * @return  bool FALSE
     */
    function fail($reason, $actual, $expect) {
      throw(new AssertionFailedError(
        $reason, 
        $actual,
        $expect
      ));
      return FALSE;
    }
    
    /**
     * Assert that a value's type is boolean
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notbool'
     * @return  bool
     */
    function assertBoolean($var, $error= 'notbool') {
      if (!is_bool($var)) {
        return $this->fail($error, 'bool', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value's type is float
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notfloat'
     * @return  bool
     */
    function assertFloat($var, $error= 'notfloat') {
      if (!is_float($var)) {
        return $this->fail($error, 'float', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value's type is integer
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notinteger'
     * @return  bool
     */
    function assertInteger($var, $error= 'notinteger') {
      if (!is_int($var)) {
        return $this->fail($error, 'int', xp::typeOf($var));
      }
      return TRUE;
    }

    /**
     * Assert that a value's type is string
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notstring'
     * @return  bool
     */
    function assertString($var, $error= 'notstring') {
      if (!is_string($var)) {
        return $this->fail($error, 'string', xp::typeOf($var));
      }
      return TRUE;
    }

    /**
     * Assert that a value's type is null
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notnull'
     * @return  bool
     */
    function assertNull($var, $error= 'notnull') {
      if (NULL !== $var) {
        return $this->fail($error, $var, NULL);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is an array
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notarray'
     * @return  bool
     */
    function assertArray($var, $error= 'notarray') {
      if (!is_array($var)) {
        return $this->fail($error, 'array', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is an object
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notobject'
     * @return  bool
     */
    function assertObject(&$var, $error= 'notobject') {
      if (!is_object($var)) {
        return $this->fail($error, 'object', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is empty
     *
     * @access  public
     * @param   mixed var
     * @return  bool
     * @param   string error default 'notempty'
     * @see     php://empty
     */
    function assertEmpty($var, $error= 'notempty') {
      if (!empty($var)) {
        return $this->fail($error, '<empty>', $var);
      }
      return TRUE;
    }

    /**
     * Assert that a value is not empty
     *
     * @access  public
     * @param   mixed var
     * @return  bool
     * @param   string error default 'empty'
     * @see     php://empty
     */
    function assertNotEmpty($var, $error= 'empty') {
      if (empty($var)) {
        return $this->fail($error, '<not empty>', $var);
      }
      return TRUE;
    }
    
    /**
     * Compare two values
     *
     * @access  public
     * @param   &mixed a
     * @param   &mixed b
     * @return  bool
     */
    function _compare(&$a, &$b) {
      if (is_array($a)) {
        if (!is_array($b) || sizeof($a) != sizeof($b)) return FALSE;

        foreach (array_keys($a) as $key) {
          if (!$this->_compare($a[$key], $b[$key])) return FALSE;
        }
        return TRUE;
      } 
      
      return is_a($a, 'Object') ? $a->equals($b) : $a === $b;
    }

    /**
     * Assert that two values are equal
     *
     * @access  public
     * @param   mixed expected
     * @param   mixed actual
     * @param   string error default 'notequal'
     * @return  bool
     */
    function assertEquals($expected, $actual, $error= 'notequal') {
      if (!$this->_compare($expected, $actual)) {
        return $this->fail($error, $actual, $expected);
      }
      return TRUE;
    }
    
    /**
     * Assert that two values are not equal
     *
     * @access  public
     * @param   mixed expected
     * @param   mixed actual
     * @param   string error default 'equal'
     * @return  bool
     */
    function assertNotEquals($expected, $actual, $error= 'equal') {
      if ($this->_compare($expected, $actual)) {
        return $this->fail($error, $actual, $expected);
      }
      return TRUE;
    }

    /**
     * Assert that a value is true
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'nottrue'
     * @return  bool
     */
    function assertTrue($var, $error= 'nottrue') {
      if (TRUE !== $var) {
        return $this->fail($error, $var, TRUE);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is false
     *
     * @access  public
     * @param   mixed var
     * @param   string error default 'notfalse'
     * @return  bool
     */
    function assertFalse($var, $error= 'notfalse') {
      if (FALSE !== $var) {
        return $this->fail($error, $var, FALSE);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value matches a given pattern
     *
     * @access  public
     * @param   mixed var
     * @param   string pattern
     * @param   string error default 'nomatches'
     * @return  bool
     * @see     php://preg_match
     */
    function assertMatches($var, $pattern, $error= 'nomatches') {
      if (!preg_match($pattern, $var)) {
        return $this->fail($error, $pattern, $var);
      }
      return TRUE;
    }

    /**
     * Assert that a string contains a substring
     *
     * @access  public
     * @param   mixed var
     * @param   string needle
     * @param   string error default 'notcontained'
     * @return  bool
     */
    function assertContains($var, $needle, $error= 'notcontained') {
      if (!strstr($var, $needle)) {
        return $this->fail($error, $pattern, $var);
      }
      return TRUE;
    }
    
    /**
     * Assert that a given object is of a specified class
     *
     * @access  public
     * @param   &lang.Object var
     * @param   string name
     * @param   string error default 'notequal'
     * @return  bool
     */
    function assertClass(&$var, $name, $error= 'notequal') {
      if (!is_a($var, 'Object')) {
        return $this->fail($error, $pattern, $var);
      }
      if ($var->getClassName() !== $name) {
        return $this->fail($error, $name, $var->getClassName());
      }
      return TRUE;
    }

    /**
     * Assert that a given object is a subclass of a specified class
     *
     * @access  public
     * @param   &lang.Object var
     * @param   string name
     * @param   string error default 'notsubclass'
     * @return  bool
     */
    function assertSubclass(&$var, $name, $error= 'notsubclass') {
      if (!is_a($var, 'Object')) {
        return $this->fail($error, $pattern, $var);
      }
      if (!is($name, $var)) {
        return $this->fail($error, $name, $var->getClassName());
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is contained in a list
     *
     * @access  public
     * @param   array list
     * @param   mixed var
     * @param   string error default 'notinlist'
     * @return  bool
     */
    function assertIn($list, $var, $error= 'notinlist') {
      if (is_a($var, 'Object')) {
        $result= array_filter($list, array(&$var, 'equals'));
        $contained= !empty($result);
      } else {
        $contained= in_array($var, $list, TRUE);
      }
      
      if (!$contained) {
        return $this->fail($error, $list, $var);
      }
      return TRUE;
    }
    
    /**
     * Set up this test. Overwrite in subclasses. Throw a 
     * PrerequisitesNotMetError to indicate this case should be
     * skipped.
     *
     * @access  public
     * @throws  unittest.PrerequisitesNotMetError
     */
    function setUp() { }
    
    /**
     * Tear down this test case. Overwrite in subclasses.
     *
     * @access  public
     */
    function tearDown() { }
    
    /**
     * Run this test case.
     *
     * @access  public
     * @param   &unittest.TestResult result
     * @return  bool success
     * @throws  lang.MethodNotImplementedException
     */
    function run(&$result) {
      $class= &$this->getClass();
      $method= &$class->getMethod($this->name);

      if (!$method) {
        return throw(new MethodNotImplementedException(
          'Method does not exist', $this->name
        ));
      }

      // Check for @expect
      $expected= NULL;
      if ($method->hasAnnotation('expect')) {
        try(); {
          $expected= &XPClass::forName($method->getAnnotation('expect'));
        } if (catch('Exception', $e)) {
          return throw($e);
        }
      }
      
      // Check for @limit
      $eta= 0;
      if ($method->hasAnnotation('limit')) {
        $eta= $method->getAnnotation('limit', 'time');
      }

      $timer= &new Timer();
      $timer->start();

      // Setup test
      try(); {
        $this->setUp();
      } if (catch('PrerequisitesNotMetError', $e)) {
        $timer->stop();
        $result->setSkipped($this, $e, $timer->elapsedTime());
        return FALSE;
      } if (catch('AssertionFailedError', $e)) {
        $timer->stop();
        $result->setFailed($this, $e, $timer->elapsedTime());
        return FALSE;
      }

      // Run test
      try(); {
        $method->invoke($this, NULL);
      } if (catch('Exception', $e)) {
        $timer->stop();

        // Was that an expected exception?
        if ($expected && $expected->isInstance($e)) {
          $r= (!$eta || $timer->elapsedTime() <= $eta 
            ? $result->setSucceeded($this, $timer->elapsedTime())
            : $result->setFailed($this, new AssertionFailedError('Timeout', sprintf('%.3f', $timer->elapsedTime()), sprintf('%.3f', $eta)), $timer->elapsedTime())
          );
          $this->tearDown();
          xp::gc();
          return $r;
        }

        $result->setFailed($this, $e, $timer->elapsedTime());
        $this->tearDown();
        return FALSE;
      }

      $timer->stop();
      $this->tearDown();

      // Check expected exception
      if ($expected) {
        $e= &new AssertionFailedError(
          'Expected exception not caught',
          ($e ? $e->getClassName() : NULL),
          $method->getAnnotation('expect')
        );
        $result->setFailed($this, $e, $timer->elapsedTime());
        return FALSE;
      }
      
      $r= (!$eta || $timer->elapsedTime() <= $eta 
        ? $result->setSucceeded($this, $timer->elapsedTime())
        : $result->setFailed($this, new AssertionFailedError('Timeout', sprintf('%.3f', $timer->elapsedTime()), sprintf('%.3f', $eta)), $timer->elapsedTime())
      );
      return $r;
    }
  }
?>
