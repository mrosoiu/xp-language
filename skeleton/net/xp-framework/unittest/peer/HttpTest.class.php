<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'peer.http.HttpConnection', 
    'util.profiling.unittest.TestCase'
  );

  /**
   * Test HTTP API
   *
   * Needs a counter part, a PHP script running under a webserver.
   * This script shall contain the following source code:
   * <code>
   *   $str= serialize(array(
   *     'headers'   => getallheaders(),
   *     'method'    => getenv('REQUEST_METHOD'),
   *     'uri'       => getenv('REQUEST_URI'),
   *     'request'   => $_REQUEST
   *   ));
   *   header('Content-type: application/x-php-serialized');
   *   header('Content-length: '.strlen($str));
   * 
   *   echo $str;
   * </code>
   *
   * @purpose  Unit Test
   */
  class HttpTest extends TestCase {
    var
      $conn = NULL,
      $uri  = '';
      
    /**
     * Constructor
     *
     * @access  publuc
     * @param   string name
     * @param   string uri
     */
    function __construct($name, $uri) {
      $this->uri= $uri;
      parent::__construct($name);
    }
      
    /**
     * Setup function
     *
     * @access  public
     * @throws  rdbms.DriverNotSupportedException
     */
    function setUp() {
      $this->conn= &new HttpConnection($this->uri);
    }
    
    /**
     * Private helper method
     *
     * @access  private
     * @param   string method
     * @param   bool expectingData
     * @return  array data
     */
    function _test($method, $expectingData) {
      try(); {
        $response= &$this->conn->request($method, array(
          'a'   => 'b',
          'b'   => 'c'
        ));
      } if (catch('Exception', $e)) {
        return $this->fail($e->getClassName(), $e->getStackTrace(), $method);
      }
      if (!$this->assertSubclass($response, 'peer.http.HttpResponse')) return;

      // Check headers
      $length= $response->getHeader('Content-length');
      $this->assertNotEmpty($length, 'contentlength.missing');
      $ctype= $response->getHeader('Content-type');
      $this->assertNotEmpty($length, 'contenttype.missing');
      
      $data= NULL;
      if ($expectingData) {
        $buf= $response->readData($length, TRUE);
        $this->assertEquals(strlen($buf), (int)$length, 'readdata');
        $data= unserialize($buf);
        $this->assertArray($data, 'data.corrupt');

        // Check return
        $this->assertArray($data['headers'], 'requestheaders.missing');
        $this->assertEquals($data['method'], $method, 'requestmethod');
        $this->assertArray($data['request'], 'querymissing');
        $this->assertEquals($data['request']['a'], 'b', 'query.datamissing');
        $this->assertEquals($data['request']['b'], 'c', 'query.datamissing');
      }
      
      return array($response->getHeaders(), $data);
    }
    
    /**
     * Test get method
     *
     * @access  public
     */
    function testGet() {
      return $this->_test(HTTP_GET, TRUE);
    }

    /**
     * Test post method
     *
     * @access  public
     */
    function testPost() {
      return $this->_test(HTTP_POST, TRUE);
    }

    /**
     * Test head method
     *
     * @access  public
     */
    function testHead() {
      return $this->_test(HTTP_HEAD, FALSE);
    }
  }
?>
