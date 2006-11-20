<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('unittest.TestCase');

  /**
   * Driver test
   *
   * @purpose  Unit Test
   */
  class AbstractDriverTest extends TestCase {
  
    /**
     * Returns driver name in subclasses, following the form:
     * <pre>
     *   scheme://string 
     * </pre>
     * where scheme is one of the following:
     * <ul>
     *   <li>ext - tests that PHP extension is available via extension_loaded()</li>
     * </ul>
     *
     * @model   abstract
     * @access  protected
     * @return  string
     */
    function driverName() { }
    
    /**
     * Tests driver is available in current PHP setup
     *
     * @access  public
     */
    #[@test]
    function driverAvailable() {
      extract(parse_url($this->driverName()));
      switch ($scheme) {
        case 'ext': {
          $ok= extension_loaded($host);
          break;
        }
        
        case 'pdo': {   // Being forward-compatible to PHP X (where X > 4):)
          $ok= extension_loaded('pdo') && in_array($host, PDO::availableDrivers());
          break;
        }

        default: {
          return throw(new PrerequisitesNotMetError('Test error, unknown scheme "'.$scheme.'"'));
        }
      }
      
      $ok || $this->fail('Driver "'.$this->driverName().'" not loaded', FALSE, TRUE);
    } 
  }
?>
