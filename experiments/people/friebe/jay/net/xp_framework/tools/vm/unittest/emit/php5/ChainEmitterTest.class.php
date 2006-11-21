<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('net.xp_framework.tools.vm.unittest.emit.php5.AbstractEmitterTest');

  /**
   * Tests PHP5 emitter
   *
   * @purpose  Unit Test
   */
  class ChainEmitterTest extends AbstractEmitterTest {

    /**
     * Tests chaining method calls. Note this works without translation
     * in PHP5!
     *
     * @access  public
     */
    #[@test]
    function chainedMethodCalls() {
      $this->assertSourcecodeEquals(
        '$o= new lang�Object(); echo $o->getClass()->getName();',
        $this->emit('$o= new lang.Object(); echo $o->getClass()->getName();')
      );
    }

    /**
     * Tests chaining method calls. Note this needs a wrapper function
     * to work in PHP5!
     *
     * @access  public
     */
    #[@test]
    function chainedMethodCallsAfterConstructor() {
      $this->assertSourcecodeEquals(
        'echo xp::create(new lang�Object())->getClass()->getName();',
        $this->emit('echo new lang.Object()->getClass()->getName();')
      );
    }

    /**
     * Tests chaining method calls with array offsets
     *
     * @access  public
     */
    #[@test]
    function chainedArrayOffsetAfterMethodCall() {
      $this->assertSourcecodeEquals(
        'echo xp::wraparray(xp::create(new lang�Object())->getClass()->getName())->backing[0];',
        $this->emit('echo new lang.Object()->getClass()->getName()[0];')
      );
    }

    /**
     * Tests chaining method calls with array offsets
     *
     * @access  public
     */
    #[@test]
    function chainedArrayOffsetsAfterMethodCall() {
      $this->assertSourcecodeEquals(
        'echo xp::wraparray(xp::create(new lang�Object())->getClass()->getName())->backing[0][0];',
        $this->emit('echo new lang.Object()->getClass()->getName()[0][0];')
      );
    }

    /**
     * Tests chaining method calls after an array offset
     *
     * @access  public
     */
    #[@test]
    function chainedMethodCallAfterArrayOffsets() {
      $this->assertSourcecodeEquals(
        'echo $this->trace[$i]->toString();',
        $this->emit('echo $this->trace[$i]->toString();')
      );
    }

    /**
     * Tests chaining members and methods, and after the constructor
     *
     * @access  public
     */
    #[@test]
    function chainedMethodsAndMembers() {
      $this->assertSourcecodeEquals(
        preg_replace('/\n\s*/', '', 'class main�Long extends lang�Object{
          public $number= NULL;

          public function __construct($initial= 0){
            $this->number= $initial; 
          }

          public function intValue(){
            return $this->number; 
          }
        }; 

        class main�Date extends lang�Object{
          public $stamp= NULL;

          public function __construct(){
            $this->stamp= new main�Long(time()); 
          }
        }; 

        class main�News extends lang�Object{
          public $date= NULL;

          public function __construct(){
            $this->date= new main�Date(); 
          }
        }; 

        echo date(\'r\', xp::create(new main�News())->date->stamp->intValue());
        '),
        $this->emit('class Long {
          public int $number;

          public __construct(int $initial= 0) {
            $this->number= $initial;
          }

          public int intValue() {
            return $this->number;
          }
        }

        class Date {
          public Long $stamp;

          public __construct() {
            $this->stamp= new Long(time());
          }
        }

        class News {
          public Date $date;

          public __construct() {
            $this->date= new Date();
          }
        }

        echo date("r", new News()->date->stamp->intValue());
        ')
      );
    }
  }
?>
