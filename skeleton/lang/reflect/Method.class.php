<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.reflect.Routine');

  /**
   * Represents a class method
   *
   * @see   xp://lang.XPClass
   * @see   xp://lang.reflect.Routine
   * @test  xp://net.xp_framework.unittest.reflection.MethodsTest
   * @test  xp://net.xp_framework.unittest.reflection.ReflectionTest
   */
  class Method extends Routine {

    /**
     * Invokes the underlying method represented by this Method object, 
     * on the specified object with the specified parameters.
     *
     * Example:
     * <code>
     *   $method= XPClass::forName('lang.Object')->getMethod('toString');
     *
     *   var_dump($method->invoke(new Object()));
     * </code>
     *
     * Example (passing arguments)
     * <code>
     *   $method= XPClass::forName('lang.types.String')->getMethod('concat');
     *
     *   var_dump($method->invoke(new String('Hello'), array('World')));
     * </code>
     *
     * Example (static invokation):
     * <code>
     *   $method= XPClass::forName('util.log.Logger')->getMethod('getInstance');
     *
     *   var_dump($method->invoke(NULL));
     * </code>
     *
     * @param   lang.Object obj
     * @param   var[] args default array()
     * @return  var
     * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
     * @throws  lang.IllegalAccessException in case the method is not public or if it is abstract
     * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked method
     */
    public function invoke($obj, $args= array()) {
      if (NULL !== $obj && !($obj instanceof $this->_class)) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument is not a %s class (%s)',
          xp::nameOf($this->_class),
          xp::typeOf($obj)
        ));
      }
      
      // Check modifiers. If caller is an instance of this class, allow
      // protected method invocation (which the PHP reflection API does 
      // not).
      $m= $this->_reflect->getModifiers();
      if ($m & MODIFIER_ABSTRACT) {
        throw new IllegalAccessException(sprintf(
          'Cannot invoke abstract %s::%s',
          $this->_class,
          $this->_reflect->getName()
        ));
      }
      if (!($m & MODIFIER_PUBLIC)) {
        if (!$this->accessible) {
          $t= debug_backtrace();
          if ($t[1]['class'] !== $this->_class) {
            $scope= new ReflectionClass($t[1]['class']);
            if (!$scope->isSubclassOf($this->_class)) {
              throw new IllegalAccessException(sprintf(
                'Cannot invoke %s %s::%s from scope %s',
                Modifiers::stringOf($this->getModifiers()),
                $this->_class,
                $this->_reflect->getName(),
                $t[1]['class']
              ));
            }
          }
        }
        
        try {
          if ($this->_reflect->isStatic()) {
            return call_user_func(array($this->_class, '__callStatic'), "\7".$this->_reflect->getName(), (array)$args);
          } else {
            return call_user_func_array(array($obj, "\7".$this->_reflect->getName()), (array)$args);
          }
        } catch (SystemExit $e) {
          throw $e;
        } catch (Throwable $e) {
          throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName().'() ~ '.$e->getMessage(), $e);
        }
      }

      try {
        return $this->_reflect->invokeArgs($obj, (array)$args);
      } catch (SystemExit $e) {
        throw $e;
      } catch (Throwable $e) {
        throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName().'() invocation failed', $e);
      } catch (ReflectionException $e) {

        // This should never occur, we checked everything beforehand...
        throw new TargetInvocationException($this->_class.'::'.$this->_reflect->getName().'() invocation failed', new XPException($e->getMessage()));
      }
    }
  }
?>
