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
    public
      $name     = '';
      
    /**
     * Constructor
     *
     * @param   string name
     */
    public function __construct($name) {
      $this->name= $name;
    }

    /**
     * Get Name
     *
     * @return  string
     */
    public function getName() {
      return $this->name;
    }

    /**
     * Fail this test case
     *
     * @param   string reason
     * @param   mixed actual
     * @param   mixed expect
     * @return  bool FALSE
     */
    public function fail($reason, $actual, $expect) {
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
     * @param   mixed var
     * @param   string error default 'notbool'
     * @return  bool
     */
    public function assertBoolean($var, $error= 'notbool') {
      if (!is_bool($var)) {
        return $this->fail($error, 'bool', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value's type is float
     *
     * @param   mixed var
     * @param   string error default 'notfloat'
     * @return  bool
     */
    public function assertFloat($var, $error= 'notfloat') {
      if (!is_float($var)) {
        return $this->fail($error, 'float', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value's type is integer
     *
     * @param   mixed var
     * @param   string error default 'notinteger'
     * @return  bool
     */
    public function assertInteger($var, $error= 'notinteger') {
      if (!is_int($var)) {
        return $this->fail($error, 'int', xp::typeOf($var));
      }
      return TRUE;
    }

    /**
     * Assert that a value's type is string
     *
     * @param   mixed var
     * @param   string error default 'notstring'
     * @return  bool
     */
    public function assertString($var, $error= 'notstring') {
      if (!is_string($var)) {
        return $this->fail($error, 'string', xp::typeOf($var));
      }
      return TRUE;
    }

    /**
     * Assert that a value's type is null
     *
     * @param   mixed var
     * @param   string error default 'notnull'
     * @return  bool
     */
    public function assertNull($var, $error= 'notnull') {
      if (NULL !== $var) {
        return $this->fail($error, $var, NULL);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is an array
     *
     * @param   mixed var
     * @param   string error default 'notarray'
     * @return  bool
     */
    public function assertArray($var, $error= 'notarray') {
      if (!is_array($var)) {
        return $this->fail($error, 'array', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is an object
     *
     * @param   mixed var
     * @param   string error default 'notobject'
     * @return  bool
     */
    public function assertObject($var, $error= 'notobject') {
      if (!is_object($var)) {
        return $this->fail($error, 'object', xp::typeOf($var));
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is empty
     *
     * @param   mixed var
     * @return  bool
     * @param   string error default 'notempty'
     * @see     php://empty
     */
    public function assertEmpty($var, $error= 'notempty') {
      if (!empty($var)) {
        return $this->fail($error, '<empty>', $var);
      }
      return TRUE;
    }

    /**
     * Assert that a value is not empty
     *
     * @param   mixed var
     * @return  bool
     * @param   string error default 'empty'
     * @see     php://empty
     */
    public function assertNotEmpty($var, $error= 'empty') {
      if (empty($var)) {
        return $this->fail($error, '<not empty>', $var);
      }
      return TRUE;
    }
    
    /**
     * Compare two values
     *
     * @param   &mixed a
     * @param   &mixed b
     * @return  bool
     */
    protected function _compare($a, $b) {
      if (is_array($a)) {
        if (!is_array($b) || sizeof($a) != sizeof($b)) return FALSE;

        foreach (array_keys($a) as $key) {
          if (!$this->_compare($a[$key], $b[$key])) return FALSE;
        }
        return TRUE;
      } 
      
      return is('Generic', $a) ? $a->equals($b) : $a === $b;
    }

    /**
     * Assert that two values are equal
     *
     * @param   mixed expected
     * @param   mixed actual
     * @param   string error default 'notequal'
     * @return  bool
     */
    public function assertEquals($expected, $actual, $error= 'notequal') {
      if (!$this->_compare($expected, $actual)) {
        return $this->fail($error, $actual, $expected);
      }
      return TRUE;
    }
    
    /**
     * Assert that two values are not equal
     *
     * @param   mixed expected
     * @param   mixed actual
     * @param   string error default 'equal'
     * @return  bool
     */
    public function assertNotEquals($expected, $actual, $error= 'equal') {
      if ($this->_compare($expected, $actual)) {
        return $this->fail($error, $actual, $expected);
      }
      return TRUE;
    }

    /**
     * Assert that a value is true
     *
     * @param   mixed var
     * @param   string error default 'nottrue'
     * @return  bool
     */
    public function assertTrue($var, $error= 'nottrue') {
      if (TRUE !== $var) {
        return $this->fail($error, $var, TRUE);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value is false
     *
     * @param   mixed var
     * @param   string error default 'notfalse'
     * @return  bool
     */
    public function assertFalse($var, $error= 'notfalse') {
      if (FALSE !== $var) {
        return $this->fail($error, $var, FALSE);
      }
      return TRUE;
    }
    
    /**
     * Assert that a value matches a given pattern
     *
     * @param   mixed var
     * @param   string pattern
     * @param   string error default 'nomatches'
     * @return  bool
     * @see     php://preg_match
     */
    public function assertMatches($var, $pattern, $error= 'nomatches') {
      if (!preg_match($pattern, $var)) {
        return $this->fail($error, $pattern, $var);
      }
      return TRUE;
    }

    /**
     * Assert that a string contains a substring
     *
     * @param   mixed var
     * @param   string needle
     * @param   string error default 'notcontained'
     * @return  bool
     */
    public function assertContains($var, $needle, $error= 'notcontained') {
      if (!strstr($var, $needle)) {
        return $this->fail($error, $pattern, $var);
      }
      return TRUE;
    }
    
    /**
     * Assert that a given object is of a specified class
     *
     * @param   &lang.Object var
     * @param   string name
     * @param   string error default 'notequal'
     * @return  bool
     */
    public function assertClass($var, $name, $error= 'notequal') {
      if (!is('Generic', $var)) {
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
     * @param   &lang.Object var
     * @param   string name
     * @param   string error default 'notsubclass'
     * @return  bool
     */
    public function assertSubclass($var, $name, $error= 'notsubclass') {
      if (!is('Generic', $var)) {
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
     * @param   array list
     * @param   mixed var
     * @param   string error default 'notinlist'
     * @return  bool
     */
    public function assertIn($list, $var, $error= 'notinlist') {
      if (is('Generic', $var)) {
        $result= array_filter($list, array($var, 'equals'));
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
     * @throws  unittest.PrerequisitesNotMetError
     */
    public function setUp() { }
    
    /**
     * Tear down this test case. Overwrite in subclasses.
     *
     */
    public function tearDown() { }
    
    /**
     * Run this test case.
     *
     * @param   &unittest.TestResult result
     * @return  bool success
     * @throws  lang.MethodNotImplementedException
     */
    public function run($result) {
      $class= $this->getClass();
      $method= $class->getMethod($this->name);

      if (!$method) {
        throw(new MethodNotImplementedException(
          'Method does not exist', $this->name
        ));
      }

      // Check for @expect
      $expected= NULL;
      if ($method->hasAnnotation('expect')) {
        try {
          $expected= XPClass::forName($method->getAnnotation('expect'));
        } catch (Exception $e) {
          throw($e);
        }
      }
      
      // Check for @limit
      $eta= 0;
      if ($method->hasAnnotation('limit')) {
        $eta= $method->getAnnotation('limit', 'time');
      }

      $timer= new Timer();
      $timer->start();

      // Setup test
      try {
        $this->setUp();
      } catch (PrerequisitesNotMetError $e) {
        $timer->stop();
        $result->setSkipped($this, $e, $timer->elapsedTime());
        return FALSE;
      } catch (AssertionFailedError $e) {
        $timer->stop();
        $result->setFailed($this, $e, $timer->elapsedTime());
        return FALSE;
      }

      // Run test
      try {
        $method->invoke($this, NULL);
      } catch (Exception $e) {
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
        $e= new AssertionFailedError(
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
