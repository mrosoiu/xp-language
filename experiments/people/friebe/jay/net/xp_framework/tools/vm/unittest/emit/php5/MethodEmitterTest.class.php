<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('net.xp_framework.tools.vm.unittest.emit.php5.AbstractEmitterTest');

  /**
   * Tests PHP5 emitter
   *
   * @see      xp://net.xp_framework.tools.vm.unittest.emit.php5.MethodOverloadingEmitterTest
   * @purpose  Unit Test
   */
  class MethodEmitterTest extends AbstractEmitterTest {

    /**
     * Tests the simplest case
     *
     */
    #[@test]
    public function methodWithoutArguments() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello(){
            echo \'Hello\'; 
          }
        };'),
        $this->emit('class Test {
          public void sayHello() {
            echo "Hello";
          }
        }')
      );
    }

    /**
     * Tests empty method
     *
     */
    #[@test]
    public function emptyMethod() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello(){
          }
        };'),
        $this->emit('class Test {
          public void sayHello() {
          }
        }')
      );
    }

    /**
     * Tests abstract method
     *
     */
    #[@test]
    public function abstractMethod() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'abstract class main�Test extends lang�Object{
          abstract public function sayHello();
        };'),
        $this->emit('abstract class Test {
          abstract public void sayHello();
        }')
      );
    }

    /**
     * Tests a method with one string argument 
     *
     */
    #[@test]
    public function methodWithOneStringArgument() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello($name){
            echo \'Hello\', $name; 
          }
        };'),
        $this->emit('class Test {
          public void sayHello(string $name) {
            echo "Hello", $name;
          }
        }')
      );
    }

    /**
     * Tests a method with one string[] argument 
     *
     */
    #[@test]
    public function methodWithOneStringArrayArgument() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello($names){
            foreach ($names as $name) {
              echo \'Hello\', $name, \' \'; 
            }; 
          }
        };'),
        $this->emit('class Test {
          public void sayHello(string[] $names) {
            foreach ($names as $name) {
              echo "Hello", $name, " ";
            }
          }
        }')
      );
    }

    /**
     * Tests a method call
     *
     */
    #[@test]
    public function methodCall() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello($names){
            foreach ($names as $name) {
              echo \'Hello\', $name, \' \'; 
            }; 
          }
 
          public static function main(){
            xp::create(new main�Test())->sayHello(array(0 => \'Timm\', 1 => \'Alex\', )); 
          }
       };'),
        $this->emit('class Test {
          public void sayHello(string[] $names) {
            foreach ($names as $name) {
              echo "Hello", $name, " ";
            }
          }

          public static void main() {
            new Test()->sayHello(array("Timm", "Alex"));
          }
        }')
      );
    }

    /**
     * Tests a static method call
     *
     */
    #[@test]
    public function staticMethodCall() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public static function sayHello($names){
            foreach ($names as $name) {
              echo \'Hello\', $name, \' \'; 
            }; 
          }
 
          public static function main(){
            main�Test::sayHello(array(0 => \'Timm\', 1 => \'Alex\', )); 
          }
       };'),
        $this->emit('class Test {
          public static void sayHello(string[] $names) {
            foreach ($names as $name) {
              echo "Hello", $name, " ";
            }
          }

          public static void main() {
            Test::sayHello(array("Timm", "Alex"));
          }
        }')
      );
    }
    
    /**
     * Tests a method which contains a method-static variable
     *
     */
    #[@test]
    public function methodWithStaticVariable() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello(){
            static $cache= array(); 
            echo \'Hello\'; 
          }
        };'),
        $this->emit('class Test {
          public void sayHello() {
            static $cache= array();

            echo "Hello";
          }
        }')
      );
    }

    /**
     * Tests a method which contains a vararg argument
     *
     */
    #[@test]
    public function methodWithVarArgs() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sayHello(){
            $__a= func_get_args(); $names= array_slice($__a, 0);
            echo \'Hello \', implode(\', \', $names); 
          }
        };'),
        $this->emit('class Test {
          public void sayHello(string... $names) {
            echo "Hello ", implode(", ", $names);
          }
        }')
      );
    }

    /**
     * Tests a method which contains a vararg argument after a regular argument
     *
     */
    #[@test]
    public function methodWithArgsAndVarArgs() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Test extends lang�Object{
          public function sprintf($format){
            $__a= func_get_args(); $args= array_slice($__a, 1);
            return vsprintf($format, $args); 
          }
        };'),
        $this->emit('class Test {
          public string sprintf(string $format, mixed... $args) {
            return vsprintf($format, $args); 
          }
        }')
      );
    }

    /**
     * Tests a method which contains an arg after a vararg argument
     *
     */
    #[@test, @expect('lang.FormatException')]
    public function varArgMustBeLastArg() {
      $this->emit('class Test {
        public string sprintf(string $format, mixed $args..., bool $return= FALSE) {
          return vsprintf($format, $args); 
        }
      }');
    }

    /**
     * Tests a method which contains default arguments
     *
     */
    #[@test]
    public function methodWithDefaultArgs() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�XPClass extends lang�Object{
          public static function forName($name, $cl= NULL){
            if (NULL==$cl){ $cl= main�ClassLoader::getDefault(); }; 
            return $cl->loadClass($name); 
          }
        }; 
        var_dump(main�XPClass::forName(\'Test\', NULL));'),
        $this->emit('class XPClass {
          public static XPClass forName($name, $cl= NULL) throws ClassNotFoundException {
            if (NULL == $cl) $cl= ClassLoader::getDefault();
            return $cl->loadClass($name);
          }
        }
        var_dump(XPClass::forName("Test"));')
      );
    }
  }
?>
