<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'util.cmd.Command',
    'xp.scriptlet.Runner'
  );

  $package= 'net.xp_framework.unittest.scriptlet';
  
  /**
   * TestCase
   *
   * @purpose  Unittest
   */
  class net�xp_framework�unittest�scriptlet�RunnerTest extends TestCase {
  
    #[@test]
    public function findApplication() {
      $map= new Hashmap(array(
        '/some/url/' => 'app1',
        '/other/url' => 'app2',
        '/'          => 'app3'
      ));
      
      $this->assertEquals('app2', xp�scriptlet�Runner::findApplication($map, '/other/url/with/appended.html'));
      $this->assertEquals('app1', xp�scriptlet�Runner::findApplication($map, '/some/url/'));
      $this->assertEquals('app1', xp�scriptlet�Runner::findApplication($map, '/some/url/below/'));
      $this->assertEquals('app3', xp�scriptlet�Runner::findApplication($map, '/just/anything/falls/back'));
    }
    
    
  }
?>