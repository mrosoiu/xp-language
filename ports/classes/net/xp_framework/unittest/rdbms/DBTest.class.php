<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'rdbms.DriverManager', 
    'unittest.TestCase',
    'net.xp_framework.unittest.rdbms.mock.MockConnection'
  );

  define('MOCK_CONNECTION_CLASS', 'net.xp_framework.unittest.rdbms.mock.MockConnection');

  /**
   * Test rdbms API
   *
   * @purpose  Unit Test
   */
  class DBTest extends TestCase {
    var
      $conn = NULL;
 
     /**
     * Static initializer
     *
     * @model   static
     * @access  public
     */  
    function __static() {
      DriverManager::register('mock', XPClass::forName(MOCK_CONNECTION_CLASS));
    }
     
    /**
     * Setup function
     *
     * @access  public
     */
    function setUp() {
      $this->conn= &DriverManager::getConnection('mock://mock/MOCKDB');
      $this->assertEquals(0, $this->conn->flags & DB_AUTOCONNECT);
    }
    
    /**
     * Tear down function
     *
     * @access  public
     */
    function tearDown() {
      $this->conn->close();
    }

    /**
     * Asserts a query works
     *
     * @access  protected
     * @throws  unittest.AssertionFailedError
     */
    function assertQuery() {
      $version= '$Revision$';
      $this->conn->setResultSet(new MockResultSet(array(array('version' => $version))));
      if (
        ($r= &$this->conn->query('select %s as version', $version)) &&
        ($this->assertSubclass($r, 'rdbms.ResultSet')) && 
        ($field= $r->next('version'))
      ) $this->assertEquals($field, $version);
    }

    /**
     * Test database connect
     *
     * @access  public
     */
    #[@test]
    function connect() {
      $result= $this->conn->connect();
      $this->assertTrue($result);
    }

    /**
     * Test database connect throws an SQLConnectException in case it fails
     *
     * @access  public
     */
    #[@test, @expect('rdbms.SQLConnectException')]
    function connectFailure() {
      $this->conn->makeConnectFail('Unknown server');
      $this->conn->connect();
    }
    
    /**
     * Test database select
     *
     * @access  public
     */
    #[@test]
    function select() {
      $this->conn->connect();
      $this->assertQuery();
    }

    /**
     * Test an SQLStateException is thrown if a query is performed on a
     * not yet connect()ed connection object.
     *
     * @access  public
     */
    #[@test, @expect('rdbms.SQLStateException')]
    function queryOnUnConnected() {
      $this->conn->query('select 1');   // Not connected
    }

    /**
     * Test an SQLStateException is thrown if a query is performed on a
     * disconnect()ed connection object.
     *
     * @access  public
     */
    #[@test, @expect('rdbms.SQLStateException')]
    function queryOnDisConnected() {
      $this->conn->connect();
      $this->assertQuery();
      $this->conn->close();
      $this->conn->query('select 1');   // Not connected
    }

    /**
     * Test an SQLConnectionClosedException is thrown if the connection
     * has been lost.
     *
     * @see     rfc://0058
     * @access  public
     */
    #[@test, @expect('rdbms.SQLConnectionClosedException')]
    function connectionLost() {
      $this->conn->connect();
      $this->assertQuery();
      $this->conn->letServerDisconnect();
      $this->conn->query('select 1');   // Not connected
    }

    /**
     * Test an SQLStateException is thrown if a query is performed on a
     * connection thas is not connected due to connect() failure.
     *
     * @access  public
     */
    #[@test, @expect('rdbms.SQLStateException')]
    function queryOnFailedConnection() {
      $this->conn->makeConnectFail('Access denied');
      try(); {
        $this->conn->connect();
      } if (catch('SQLConnectException', $ignored)) { }

      $this->conn->query('select 1');   // Previously failed to connect
    }

    /**
     * Test an SQLStatementFailedException is thrown when a query fails.
     *
     * @access  public
     */
    #[@test, @expect('rdbms.SQLStatementFailedException')]
    function statementFailed() {
      $this->conn->connect();
      $this->conn->makeQueryFail('Deadlock', 1205);
      $this->conn->query('select 1');
    }
  }
?>
