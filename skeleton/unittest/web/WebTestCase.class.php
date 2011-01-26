<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'unittest.web.Form',
    'peer.http.HttpConnection',
    'peer.http.HttpConstants',
    'text.regex.Pattern',
    'io.streams.Streams',
    'xml.XPath',
    'scriptlet.Cookie'
  );

  /**
   * TestCase for web sites
   *
   * @see      xp://unittest.TestCase
   * @test     xp://net.xp_framework.unittest.tests.WebTestCaseTest
   * @purpose  Integration tests
   */
  abstract class WebTestCase extends TestCase {
    protected
      $conn              = NULL,
      $response          = NULL,
      $cookies           = array(),
      $persistentHeaders = array();

    private
      $dom      = NULL,
      $xpath    = NULL;
      
    /**
     * Get connection
     *
     * @param   string url
     * @return  peer.http.HttpConnection
     */
    protected function getConnection($url= NULL) {
      if (NULL === $url) {
        throw new IllegalArgumentException($this->getClassName().' requires a URL as its argument');
      }
      return new HttpConnection($url);
    }

    /**
     * Set up this test case. Creates connection.
     *
     */
    public function __construct($name, $url= NULL) {
      parent::__construct($name);
      $this->conn= $this->getConnection($url);
    }
    
    /**
     * Sets a header which is sent on every request.
     */
    public function setPersistentHeader($name, $value) {
      $this->persistentHeaders[$name]= $value;
    }
    
    /**
     * Returns a DOM object for this response's contents. Lazy/Cached.
     *
     * @return  php.DOMDocument
     */
    public function getDom() {
      if (NULL === $this->dom) {
        $this->dom= new DOMDocument();
        @$this->dom->loadHTMLFile(Streams::readableUri($this->response->getInputStream()));
      }
      return $this->dom;
    }

    /**
     * Returns an XPath object on this response's DOM. Lazy/Cached.
     *
     * @return  xml.XPath
     */
    public function getXPath() {
      if (NULL === $this->xpath) {
        $this->xpath= new XPath($this->getDom());
      }
      return $this->xpath;
    }
    
    /**
     * Returns base
     *
     * @return  string
     */
    public function getBase() {
      return $this->conn->getUrl()->getPath('/');
    }
    
    /**
     * Perform a request
     *
     * @param   string method
     * @param   string params
     * @return  peer.http.HttpResponse
     */
    protected function doRequest($method, $params) {
      $request= $this->conn->create(new HttpRequest());
      $request->setMethod($method);
      $request->setParameters($params);

      //set headers specified for this web test
      foreach ($this->persistentHeaders as $name => $value) {
       $request->setHeader($name, $value);
      }

      // Check if we have cookies for this domain
      $host= $this->conn->getUrl()->getHost();
      if (isset($this->cookies[$host]) && 0 < sizeof($this->cookies[$host])) {
        $cookies= '';
        foreach ($this->cookies[$host] as $cookie) {
          $cookies.= $cookie->getHeadervalue().'; ';
        }
        $request->setHeader('Cookie', substr($cookies, 0, -2));
      }
      return $this->conn->send($request);
    }
  
    /**
     * Navigate to a relative URL 
     *
     * @param   string relative
     * @param   string params
     * @throws  unittest.AssertionFailedError  
     */
    public function beginAt($relative, $params= NULL, $method= HttpConstants::GET) {
      $this->dom= $this->xpath= NULL;
      $this->conn->getUrl()->setPath($relative);
      try {
        $this->response= $this->doRequest($method, $params);
        
        // If we get a cookie, store it for this domain and reuse it in 
        // subsequent requests. If cookies are used for sessioning, we 
        // would be creating new sessions with every request otherwise!
        foreach ((array)$this->response->header('Set-Cookie') as $str) {
          $cookie= Cookie::parse($str);
          $this->cookies[$this->conn->getUrl()->getHost()][$cookie->getName()]= $cookie;
        }
      } catch (XPException $e) {
        $this->response= xp::null();
        $this->fail($relative, $e, NULL);
      }
    }
    
    /**
     * Navigate to a given URL
     *
     * @param   string target
     * @param   string params
     * @param   string method
     * @throws  unittest.AssertionFailedError  
     */
    public function navigateTo($target, $params= NULL, $method= HttpConstants::GET) {
      if (strstr($target, '://')) {
        $url= new URL($target);
        $this->conn= $this->getConnection(sprintf(
          '%s://%s%s/',
          $url->getScheme(),
          $url->getHost(),
          -1 === $url->getPort(-1) ? '' : ':'.$url->getPort()
        ));
        $params ? $url->setParams($params) : '';
        $this->beginAt($url->getPath(), $url->getParams(), $method);
      } else if ('' !== $target && '/' === $target{0}) {
        $this->beginAt($target, $params, $method);
      } else {
        $base= $this->getBase();
        $this->beginAt(substr($base, 0, strrpos($base, '/')).'/'.$target, $params, $method);
      }
    }

    /**
     * Follow redirect from either the location or the refresh header.
     * Ignoring the http-equiv meta tag "refresh"
     *
     * @param   int assertStatus
     * @param   string assertBase
     * @throws  unittest.AssertionFailedError
     */
    public function followRedirect($assertStatus= NULL, $assertBase= NULL) {
      // redirect to location header
      if ($location= $this->response->getHeader('Location')) {
        $this->navigateTo($location);

      // redirect to refresh header
      } elseif ($refresh= $this->response->getHeader('Refresh')) {
        // the content of the refresh header in general looks like
        // "0;URL=http://foo.bar/baz"
        $this->navigateTo(substr($refresh, stripos($refresh, 'url=') + 4));

      // no redirect target set
      } else {
        $this->fail('No target for redirect found.', NULL, 'Http header "Location" or "Refresh"');
      }

      if (NULL !== $assertStatus) {
        $this->assertStatus($assertStatus);
      }

      if (NULL !== $assertBase) {
        $this->assertEquals($assertBase, $this->getBase(), 'Redirected to unexpected base.');
      }
    }

    /**
     * Navigate to the page a link with a specified id points to
     *
     * @param   string id
     * @throws  unittest.AssertionFailedError  
     */
    protected function clickLink($id) {
      $node= $this->getXPath()->query('//a[@id = "'.$id.'"]')->item(0);
      $this->assertNotEquals(NULL, $node);
      $this->navigateTo($node->getAttribute('href'));
    }

    /**
     * Navigate to the page a link with a specified text points to
     *
     * @param   string text
     * @throws  unittest.AssertionFailedError  
     */
    protected function clickLinkWithText($text) {
      $node= $this->getXPath()->query('//a[text() = "'.$text.'"]')->item(0);
      $this->assertNotEquals(NULL, $node);
      $this->navigateTo($node->getAttribute('href'));
    }
    
    /**
     * Fail this test case
     *
     * @param   string reason
     * @param   var actual
     * @param   var expect
     */
    public function fail($reason, $actual, $expect) {
      parent::fail('@'.$this->conn->getUrl()->getURL().': '.$reason, $actual, $expect);
    }

    /**
     * Assert a HTTP status code
     *
     * @param   int status
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertStatus($status, $message= 'not_equals') {
      $this->assertEquals($status, $this->response->getStatusCode(), $message);
    }

    /**
     * Assert a HTTP status code
     *
     * @param   int[] status
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertStatusIn($list, $message= 'not_equals') {
      $sc= $this->response->getStatusCode();
      if (!in_array($sc, $list)) {
        $this->fail($message, $sc, '['.implode(', ', $list).']');
      }
    }

    /**
     * Assert the current URL equals a specified URL
     *
     * @param   peer.URL url
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertUrlEquals(URL $url, $message= 'not_equals') {
      $this->assertEquals($this->conn->getUrl(), $url, $message);
    }

    /**
     * Assert a the "Content-Type" HTTP header's value equals the specified content-type
     *
     * @param   string ctype
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertContentType($ctype, $message= 'not_equals') {
      $this->assertEquals($ctype, $this->response->getHeader('Content-Type'), $message);
    }

    /**
     * Assert a HTTP header key / value pair
     *
     * @param   string header
     * @param   string value
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertHeader($header, $value, $message= 'not_equals') {
      $this->assertEquals($value, $this->response->getHeader($header), $message);
    }

    /**
     * Assert an element is present
     *
     * @param   string id
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertElementPresent($id, $message= 'not_present') {
      $node= $this->getXPath()->query('//*[@id = "'.$id.'"]')->item(0);
      $this->assertNotEquals(NULL, $node, $message);
    }

    /**
     * Assert an element is not present
     *
     * @param   string id
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertElementNotPresent($id, $message= 'present') {
      $node= $this->getXPath()->query('//*[@id = "'.$id.'"]')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }
    
    /**
     * Assert a text is present
     *
     * @param   string text
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertTextPresent($text, $message= 'not_present') {
      $node= $this->getXPath()->query('//*[contains(text(), "'.$text.'")]')->item(0);
      $this->assertNotEquals(NULL, $node->textContent, $message);
    }

    /**
     * Assert a text is not present
     *
     * @param   string text
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertTextNotPresent($text, $message= 'present') {
      $node= $this->getXPath()->query('//*[contains(text(), "'.$text.'")]')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }

    /**
     * Assert a text is present
     *
     * @param   text.regex.Pattern pattern
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertTextPatternPresent(Pattern $pattern, $message= 'not_present') {
      $this->assertNotEquals(
        MatchResult::$EMPTY, 
        $pattern->match($this->getDom()->documentElement->textContent), 
        $message
      );
    }

    /**
     * Assert a text is not present
     *
     * @param   text.regex.Pattern pattern
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertTextPatternNotPresent(Pattern $pattern, $message= 'matched') {
      $this->assertEquals(
        MatchResult::$EMPTY, 
        $pattern->match($this->getDom()->documentElement->textContent), 
        $message
      );
    }

    /**
     * Assert an image is present
     *
     * @param   string src
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertImagePresent($src, $message= 'not_present') {
      $node= $this->getXPath()->query('//img[@src = "'.$src.'"]')->item(0);
      $this->assertNotEquals(NULL, $node, $message);
    }

    /**
     * Assert an image is not present
     *
     * @param   string src
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertImageNotPresent($src, $message= 'present') {
      $node= $this->getXPath()->query('//img[@src = "'.$src.'"]')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }

    /**
     * Assert a link to a specified URL is present
     *
     * @param   string url
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertLinkPresent($url, $message= 'not_present') {
      $node= $this->getXPath()->query('//a[@href = "'.$url.'"]')->item(0);
      $this->assertNotEquals(NULL, $node, $message);
    }

    /**
     * Assert a link to a specified URL is not present
     *
     * @param   string url
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertLinkNotPresent($url, $message= 'present') {
      $node= $this->getXPath()->query('//a[@href = "'.$url.'"]')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }
    
    /**
     * Assert a link with a specific text is present
     *
     * @param   string text
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertLinkPresentWithText($text, $message= 'not_present') {
      $node= $this->getXPath()->query('//a[text() = "'.$text.'"]')->item(0);
      $this->assertNotEquals(NULL, $node, $message);
    }

    /**
     * Assert a link with a specific text is not present
     *
     * @param   string text
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertLinkNotPresentWithText($text, $message= 'present') {
      $node= $this->getXPath()->query('//a[text() = "'.$text.'"]')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }

    /**
     * Assert a form is present
     *
     * @param   string name default NULL
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertFormPresent($name= NULL, $message= 'not_present') {
      $node= $this->getXPath()->query($name ? '//form[@name = "'.$name.'"]' : '//form')->item(0);
      $this->assertNotEquals(NULL, $node, $message);
    }

    /**
     * Assert a form is not present
     *
     * @param   string name default NULL
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertFormNotPresent($name= NULL, $message= 'present') {
      $node= $this->getXPath()->query($name ? '//form[@name = "'.$name.'"]' : '//form')->item(0);
      $this->assertEquals(NULL, $node, $message);
    }

    /**
     * Get form
     *
     * @param   string name default NULL
     * @return  unittest.web.Form
     * @throws  unittest.AssertionFailedError  
     */
    public function getForm($name= NULL) {
      $node= $this->getXPath()->query($name ? '//form[@name = "'.$name.'"]' : '//form')->item(0);
      if (NULL === $node) {
        $this->fail('Failed to locate a form named "'.$name.'"', NULL, '[form]');
      }
      return new unittest�web�Form($this, $node);
    }

    /**
     * Assert on the HTML title element
     *
     * @param   string title
     * @param   string message
     * @throws  unittest.AssertionFailedError  
     */
    public function assertTitleEquals($title, $message= 'not_equals') {
      $text= $this->getXPath()->query('//title/text()')->item(0);
      $this->assertEquals($title, trim($text->data), $message);
    }

    /**
     * Assert a cookie is present
     *
     * @param   string name
     * @throws  unittest.AssertionFailedError
     */
    protected function assertCookiePresent($name) {
      $domain= $this->conn->getUrl()->getHost();
      $this->assertTrue(isset($this->cookies[$domain][$name]), xp::stringOf($this->cookies));
    }

    /**
     * Gets a cookie
     *
     * @param   string name
     * @return  scriptlet.Cookie
     */
    protected function getCookie($name) {
      $domain= $this->conn->getUrl()->getHost();
      if (!isset($this->cookies[$domain][$name])) {
        $this->fail('Failed to locate a cookie named "'.$name.'"', NULL, '[cookie]');
      }
      return $this->cookies[$domain][$name];
    }
  }
?>
