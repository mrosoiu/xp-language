<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'xml.XSLProcessor',
    'scriptlet.HttpScriptlet',
    'scriptlet.xml.XMLScriptletResponse',
    'scriptlet.xml.XMLScriptletRequest'
  );
  
  /**
   * XML scriptlets are the more advanced version of HttpScriptlets.
   * XML scriptlets do not implement a direct output to the client. 
   * Rather, the response object consists of a so-called "OutputDocument"
   * (resembling an XML DOM-Tree) and an XSL stylesheet.
   *
   * The three main nodes, formresult, formvalues and formerrors are 
   * represented in the OutputDocument class by corresponding
   * member variables. For ease of their manipulation, there are three
   * method in XMLSriptletResponse to add nodes to them. The
   * XSL stylesheet is applied against this XML.
   *
   * All request parameters are imported into the formvalues node to give
   * you access to the request parameters withing your XSL stylesheet (e.g.,
   * via /formresult/formvalues/param[@name= 'query']). You might
   * want to define an xsl:variable containing the formvalues for easier
   * access.
   *
   * Farthermore, the following attributes are passed as external parameters:
   * <pre>
   *   Name        Meaning
   *   ----------- -------------------------------------------------
   *   __state     the current state
   *   __lang      the language in which this page is displayed
   *   __product   the product (think of it as "theme")
   *   __sess      the session's id
   *   __query     the query string
   * </pre>
   * 
   * @see      xp://scriptlet.xml.XMLScriptletRequest
   * @see      xp://scriptlet.xml.XMLScriptletResponse
   * @see      xp://scriptlet.xml.XMLScriptletResponse#addFormValue
   * @see      xp://scriptlet.xml.XMLScriptletResponse#addFormError
   * @see      xp://scriptlet.xml.XMLScriptletResponse#addFormResult
   * @purpose  Base class for websites using XML/XSL to render their output
   */
  class XMLScriptlet extends HttpScriptlet {
    var 
      $processor = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string base default ''
     */
    function __construct($base= '') {
      $this->processor= &$this->_processor();
      $this->processor->setBase($base);
    }
    
    /**
     * Set our own processor object
     *
     * @access  protected
     * @return  &xml.XSLProcessor
     */
    function &_processor() {
      return new XSLProcessor();
    }
    
    /**
     * Set our own response object
     *
     * @access  protected
     * @return  &scriptlet.xml.XMLScriptletResponse
     * @see     xp://scriptlet.HttpScriptlet#_response
     */
    function &_response() {
      $response= &new XMLScriptletResponse();
      $response->setProcessor($this->processor);
      return $response;
    }

    /**
     * Set our own request object
     *
     * @access  protected
     * @return  &scriptlet.xml.XMLScriptletRequest
     * @see     xp://scriptlet.HttpScriptlet#_request
     */
    function &_request() {
      return new XMLScriptletRequest();
    }
    
    /**
     * Handle method. Calls doCreate if necessary (the environment variable
     * "PRODUCT" is not set - which it will be if the RewriteRule has
     * taken control).
     *
     * @access  protected
     * @param   &scriptlet.xml.XMLScriptletRequest request
     * @return  string class method (one of doGet, doPost, doHead)
     * @see     xp://scriptlet.xml.XMLScriptlet#_handleMethod
     */
    function handleMethod(&$request) {
      if (!$request->getEnvValue('PRODUCT')) {
        return 'doCreate';
      }

      return parent::handleMethod($request);
    }
    
    /**
     * Helper method for doCreate() and doCreateSession()
     *
     * @access  protected
     * @param   &scriptlet.xml.XMLScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     * @param   string sessionId default NULL
     * @return  bool
     */
    function doRedirect(&$request, &$response, $sessionId= NULL) {
      $uri= &$request->getURL();

      // Get product, language and statename from the environment if 
      // necessary. Their default values are "site" (product), 
      // "en_US" (language) and "static" (statename).
      if (!$product= $request->getProduct()) {
        $product= $request->getEnvValue('DEF_PROD', 'site');
      }
      if (!$language= $request->getLanguage()) {
        $language= $request->getEnvValue('DEF_LANG', 'en_US');
      }
      if (!$stateName= $request->getStateName()) {
        $stateName= $request->getEnvValue('DEF_STATE', 'static');
      }
      
      // Send redirect
      $response->sendRedirect(sprintf(
        '%s://%s/xml/%s.%s%s/%s%s%s', 
        $uri->getScheme(),
        $uri->getHost(),
        $product,
        $language,
        empty($sessionId) ? '' : '.psessionid='.$sessionId,
        $stateName,
        $uri->getQuery() ? '?'.$uri->getQuery() : '',
        $uri->getFragment() ? '#'.$uri->getFragment() : ''
      ));
      
      return FALSE; // Indicate no further processing is to be done
    }
    
    /**
     * Create - redirects to /xml/$pr:$ll_LL/static if 
     * necessary, regarding the environment variables DEF_PROD and 
     * DEF_LANG as values for $pr and $ll_LL. If these aren't set, "site" and
     * "en_US" are assumed as default values.
     *
     * @access  protected
     * @param   &scriptlet.xml.XMLScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     * @return  bool
     */
    function doCreate(&$request, &$response) {
      return $this->doRedirect($request, $response);
    }

    /**
     * Creates a session. 
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.HttpScriptletRequest request 
     * @param   &scriptlet.HttpScriptletResponse response 
     */
    function doCreateSession(&$request, &$response) {
      return $this->doRedirect($request, $response, $request->session->getId());
    }

    /**
     * Sets the responses XSL stylesheet
     *
     * @access  protected
     * @param   &scriptlet.scriptlet.XMLScriptletRequest request
     * @param   &scriptlet.scriptlet.XMLScriptletResponse response
     */
    function _setStylesheet(&$request, &$response) {
      $response->setStylesheet(sprintf(
        '%s/%s/%s.xsl',
        $request->getProduct(),
        $request->getLanguage(),
        $request->getStateName()
      ));
    }

    /**
     * Process request
     *
     * @access  protected
     * @param   &scriptlet.xml.XMLScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     */
    function processRequest(&$request, &$response) {

      // Define special parameters
      $response->setParam('state',   $request->getStateName());
      $response->setParam('page',    $request->getPage());
      $response->setParam('lang',    $request->getLanguage());
      $response->setParam('product', $request->getProduct());
      $response->setParam('sess',    $request->getSessionId());
      $response->setParam('query',   $request->getQueryString());
      
      // Set XSL stylesheet
      $response->hasStylesheet() || $this->_setStylesheet($request, $response);

      // Add all request parameters to the formvalue node
      foreach ($request->params as $key => $value) {
        $response->addFormValue($key, $value);
      }
    }
    
    /**
     * Handle all requests. This method is called from <pre>doPost</pre> since
     * it really makes no difference - one can still find out via the 
     * <pre>method</pre> attribute of the request object. 
     *
     * Remember:
     * When overriding this method, please make sure you include all your 
     * sourcecode _before_ you call <pre>parent::doGet()</pre>
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.xml.XMLScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     * @see     xp://scriptlet.HttpScriptlet#doGet
     */
    function doGet(&$request, &$response) {
      return $this->processRequest($request, $response);
    }
    
    /**
     * Simply call doGet
     *
     * @access  protected
     * @return  bool processed
     * @param   &scriptlet.xml.XMLScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @throws  lang.Exception to indicate failure
     * @see     xp://scriptlet.HttpScriptlet#doPost
     */
    function doPost(&$request, &$response) {
      return $this->processRequest($request, $response);
    }
  }
?>
