<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'peer.URL',
    'peer.http.HttpConstants',
    'scriptlet.HttpScriptletRequest',
    'scriptlet.HttpScriptletResponse',
    'scriptlet.HttpScriptletException',
    'scriptlet.HttpSessionInvalidException',
    'scriptlet.HttpSession'
  );
  
  /**
   * Scriptlets are the counterpart to Java's Servlets - as one might
   * have guessed from their name. Scriptlets, in comparison to Java
   * servlets, are terminated at the end of a request, their resources
   * freed and (non-persistent) connections, files etc. closed. 
   * Scriptlets are not a 1:1 implementation of Servlets though one
   * might find a lot of similarities!
   * 
   * This class is the base class for your application and really does
   * nothing except for providing you whith a simple way of creating
   * dynamic web pages. 
   *
   * For the beginning, in your class extending this one, simply override
   * the <pre>doGet()</pre> method and put any source there to be executed 
   * on a HTTP GET request.
   *
   * Example:
   * <code>
   *   uses('scriptlet.HttpScriptlet');
   * 
   *   class MyScriptlet extends HttpScriptlet {
   *     function doGet(&$request, &$response) {
   *       $response->write('Hello World');
   *     }
   *   }
   * </code>
   *
   * <code>
   *   uses('foo.bar.MyScriptlet');
   *
   *   $s= &new MyScriptlet();
   *   try(); {
   *     $s->init();
   *     $response= &$s->process();
   *   } if (catch('HttpScriptletException', $e)) {
   *     // Retrieve standard "Internal Server Error"-Document
   *     $response= &$e->getResponse(); 
   *   }
   * 
   *   $response->sendHeaders();
   *   $response->sendContent();
   * 
   *   $s->finalize();
   * </code>
   */
  class HttpScriptlet extends Object {
    var
      $sessionURIFormat = '%1$s://%2$s%3$s/%6$s?%s&psessionid=%7$s';
    
    /**
     * Create a request object. Override this method to define
     * your own request object
     *
     * @access  protected
     * @return  &scriptlet.HttpScriptletRequest
     */
    function &_request() {
      return new HttpScriptletRequest();
    }
    
    /**
     * Create a session object. Override this method to define
     * your own session object
     *
     * @access  protected
     * @return  &scriptlet.HttpSession
     */
    function &_session() {
      return new HttpSession();
    }
    
    /**
     * Create a response object. Override this method to define
     * your own response object
     *
     * @access  protected
     * @return  &scriptlet.HttpScriptletResponse
     */
    function &_response() {
      return new HttpScriptletResponse();
    }
    
    /**
     * Initialize session
     *
     * @access  protected
     * @param   &scriptlet.HttpScriptletRequest request
     */
    function handleSessionInitialization(&$request) {
      $request->session->initialize($request->getSessionId());
    }

    /**
     * Handle the case when we find the given session invalid. By default, 
     * we create a new session and therefore gracefully handle this case.
     *
     * This function must return TRUE if the scriptlet is supposed to 
     * continue processing the request.
     *
     * @access  protected
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @return  bool continue
     */
    function handleInvalidSession(&$request, &$response) {
      return $request->session->initialize(NULL);
    }

    /**
     * Handle the case when session initialization fails. By default, we 
     * just return an error for this, a derived class may choose to 
     * gracefully handle this case.
     *
     * This function must return TRUE if the scriptlet is supposed to 
     * continue processing the request.
     *
     * @access  protected
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @return  bool continue
     */
    function handleSessionInitializationError(&$request, &$response) {
      return FALSE;
    }
    
    /**
     * Decide whether a session is needed. Returns FALSE in this
     * implementation.
     *
     * @access  protected
     * @param   &scriptlet.HttpScriptletRequest request
     * @return  bool
     */
    function needsSession(&$request) {
      return FALSE;
    }
    
    /**
     * Handles the different HTTP methods. Supports GET, POST and
     * HEAD - other HTTP methods pose security risks if not handled
     * properly and are used very uncommly anyway.
     *
     * If you want to support these methods, override this method - 
     * make sure you call <pre>parent::_handleMethod($request)</pre>
     * so that the request object gets set up correctly before any
     * of your source is executed
     *
     * @see     rfc://2616
     * @access  protected
     * @param   &scriptlet.HttpScriptletRequest request
     * @return  string class method (one of doGet, doPost, doHead)
     */
    function handleMethod(&$request) {
      switch ($request->method) {
        case HTTP_POST:
          $request->setData($GLOBALS['HTTP_RAW_POST_DATA']);
          if (!empty($_FILES)) {
            $request->params= array_merge($request->params, $_FILES);
          }
          $m= 'doPost';
          break;
          
        case HTTP_GET:
          $request->setData(getenv('QUERY_STRING'));
          $m= 'doGet';
          break;
          
        case HTTP_HEAD:
          $request->setData(getenv('QUERY_STRING'));
          $m= 'doHead';
          break;        
          
        default:
          $m= NULL;
      }
      
      return $m;
    }
    
    /**
     * Receives an HTTP GET request from the <pre>process()</pre> method
     * and handles it.
     *
     * When overriding this method, request parameters are read and acted
     * upon and the response object is used to set headers and add
     * output. The request objects contains a session object if one was
     * requested via <pre>needsSession()</pre>. Return FALSE to indicate no
     * farther processing is needed - the response object's method 
     * <pre>process</pre> will not be called.
     * 
     * Example:
     * <code>
     *   function doGet(&$request, &$response) {
     *     if (NULL === ($name= $request->getParam('name'))) {
     *       // Display a form where name is entered
     *       // ...
     *       return;
     *     }
     *     $response->write('Hello '.$name);
     *   }
     * </code>
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     */
    function doGet(&$request, &$response) {
    }
    
    /**
     * Receives an HTTP POST request from the <pre>process()</pre> method
     * and handles it.
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     */
    function doPost(&$request, &$response) {
    }
    
    /**
     * Receives an HTTP HEAD request from the <pre>process()</pre> method
     * and handles it.
     *
     * Remember:
     * The HEAD method is identical to GET except that the server MUST NOT
     * return a message-body in the response. The metainformation contained
     * in the HTTP headers in response to a HEAD request SHOULD be identical
     * to the information sent in response to a GET request. This method can
     * be used for obtaining metainformation about the entity implied by the
     * request without transferring the entity-body itself. This method is
     * often used for testing hypertext links for validity, accessibility,
     * and recent modification.
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     */
    function doHead(&$request, &$response) {
    }
    
    /**
     * Creates a session. This method will only be called if 
     * <pre>needsSession()</pre> return TRUE and no session
     * is available or the session is unvalid.
     *
     * The member variable <pre>sessionURIFormat</pre> is used
     * to sprintf() the new URI:
     * <pre>
     * Ord Fill            Example
     * --- --------------- --------------------
     *   1 scheme          http
     *   2 host            host.foo.bar
     *   3 path            /foo/bar/index.html
     *   4 dirname(path)   /foo/bar/
     *   5 basename(path)  index.html
     *   6 query           a=b&b=c
     *   7 session id      cb7978876218bb7
     *   8 fraction        #test
     * </pre>
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     */
    function doCreateSession(&$request, &$response) {
      $uri= &$request->getURL();
      $response->sendRedirect(sprintf(
        $this->sessionURIFormat,
        $uri->getScheme(),
        $uri->getHost(),
        $uri->getPath(),
        dirname($uri->getPath()),
        basename($uri->getPath()),
        $uri->getQuery(),
        $request->session->getId(),
        $uri->getFragment()
      ));
      return FALSE;
    }
    
    /**
     * Initialize the scriptlet. This method is called before any 
     * method processing is done.
     *
     * In this method, you can set up "global" requirements such as a 
     * configuration manager.
     *
     * @access  public
     */
    function init() { }
    
    /**
     * Finalize the scriptlet. This method is called after all response
     * headers and data has been sent and allows you to handle things such
     * as cleaning up resources or closing database connections.
     *
     * @access  public
     */
    function finalize() { }
    
    /**
     * Set the request from the environment.
     *
     * @access  protected
     * @param   &scriptlet.HttpRequest request
     */
    function _setupRequest(&$request) {
      $request->headers= array_change_key_case(getallheaders(), CASE_LOWER);
      $request->method= getenv('REQUEST_METHOD');
      $request->setParams(array_change_key_case($_REQUEST, CASE_LOWER));
      $request->setURI(new URL(
        ('on' == getenv('HTTPS') ? 'https' : 'http').'://'.
        getenv('HTTP_HOST').
        getenv('REQUEST_URI')
      ));
    }    
    
    /**
     * This method is called to process any request and dispatches
     * it to on of the do* -methods of the scriptlet. It will also
     * call the <pre>doCreateSession()</pre> method if necessary.
     *
     * @access  public
     * @return  &scriptlet.HttpScriptletResponse the response object
     * @throws  scriptlet.HttpScriptletException indicating fatal errors
     */
    function &process() {
      $request= &$this->_request();
      $this->_setupRequest($request);

      // Check if this method can be handled. In case it can't, throw a
      // HttpScriptletException with the HTTP status code 501 ("Method not
      // implemented"). The request object will already have all headers
      // and the request method set when this method is called.
      if (!($method= $this->handleMethod($request))) {
        return throw(new HttpScriptletException(
          'HTTP method "'.$request->method.'" not supported',
          HTTP_METHOD_NOT_IMPLEMENTED
        ));
      }

      // Call the request's initialization method
      $request->initialize();

      // Check if a session is present. This is either the case when a session
      // is already in the URL or if the scriptlet explicetly states it needs 
      // one (by returning TRUE from needsSession()).
      if ($this->needsSession($request) || $request->getSessionId()) {
        $request->setSession($this->_session());
        try(); {
          $this->handleSessionInitialization($request);
        } if (catch('Exception', $e)) {
        
          // Check if session initialization errors can be handled gracefully
          // (default: no). If not, throw a HttpSessionInvalidException with
          // the HTTP status code 503 ("Service temporarily unavailable").
          if (!$this->handleSessionInitializationError($request, $response)) {
            return throw(new HttpSessionInvalidException(
              'Session initialization failed: '.$e->getMessage(),
              HTTP_SERVICE_TEMPORARILY_UNAVAILABLE
            ));
          }
          
          // Fall through, otherwise
        }

        // Check if invalid sessions can be handled gracefully (default: no).
        // If not, throw a HttpSessionInvalidException with the HTTP status
        // code 400 ("Bad request").
        if (!$request->session->isValid()) {
          if (!$this->handleInvalidSession($request, $response)) {
            return throw(new HttpSessionInvalidException(
              'Session is invalid',
              HTTP_BAD_REQUEST
            ));
          }

          // Fall through, otherwise
        }
        
        // Call doCreateSession() in case the session is new
        if ($request->session->isNew()) $method= 'doCreateSession';
      }

      // Call method handler and, in case the method handler returns anything
      // else than FALSE, the response processor. Exceptions thrown from any of
      // the two methods will result in a HttpScriptletException with the HTTP
      // status code 500 ("Internal Server Error") being thrown.
      $response= &$this->_response();
      try(); {
        $r= call_user_func_array(
          array(&$this, $method), 
          array(&$request, &$response)
        );
        
        if (FALSE !== $r && !is(NULL, $r)) {
          $response->process();
        }
      } if (catch('HttpScriptletException', $e)) {
        return throw($e);
      } if (catch('Exception', $e)) {
        return throw(new HttpScriptletException(
          'Request processing failed ['.$method.']: '.$e->getMessage(),
          HTTP_INTERNAL_SERVER_ERROR
        ));
      }
      
      // Return it
      return $response;
    }
  }
?>
