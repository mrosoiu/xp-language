<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.XMLScriptlet', 
    'scriptlet.xml.workflow.WorkflowScriptletRequest'
  );

  /**
   * Workflow model scriptlet implementation
   *
   * @purpose  Base class
   */
  class AbstractXMLScriptlet extends XMLScriptlet {
    var
      $package  = NULL;

    /**
     * Constructor
     *
     * @access  public
     * @param   string package
     * @param   string base default ''
     */
    function __construct($package, $base= '') {
      parent::__construct($base);
      $this->package= $package;
    }

    /**
     * Create the request object
     *
     * @access  protected
     * @return  &scriptlet.xml.workflow.WorkflowScriptletRequest
     */
    function &_request() {
      return new WorkflowScriptletRequest($this->package);
    }
    
    /**
     * Retrieve context class
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @return  &lang.XPClass
     * @throws  lang.ClassNotFoundException
     */
    function &getContextClass(&$request) {
      return XPClass::forName($this->package.'.'.(ucfirst($request->getProduct()).'Context'));
    }

    /**
     * Decide whether a session is needed
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @return  bool
     */
    function needsSession(&$request) {
      return ($request->state && (
        $request->state->hasHandlers() || 
        $request->state->requiresAuthentication()
      ));
    }
    
    /**
     * Decide whether a context is needed. Returns FALSE in this default
     * implementation.
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @return  bool
     */
    function wantsContext(&$request) {
      return FALSE;
    }
    
    /**
     * Process workflow. Calls the state's setup() and process() 
     * methods in this order. May be overwritten by subclasses.
     *
     * Return FALSE from this method to indicate no further 
     * processing is to be done
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @return  bool
     */
    function processWorkflow(&$request, &$response) {

      // Context initialization
      $context= NULL;
      if ($this->wantsContext($request) && $request->hasSession()) {
      
        // Set up context. The context contains - so to say - the "autoglobals",
        // in other words, the omnipresent data such as the user
        try(); {
          $class= &$this->getContextClass($request);;
        } if (catch('ClassNotFoundException', $e)) {
          throw(new HttpScriptletException($e->getMessage()));
          return FALSE;
        }
      
        // Get context from session. If it is not available there, set up the 
        // context and store it to the session.
        $cidx= $class->getName();
        if (!($context= &$request->session->getValue($cidx))) {
          $context= &$class->newInstance();

          try(); {
            $context->setup($request);
          } if (catch('IllegalStateException', $e)) {
            throw(new HttpScriptletException($e->getMessage(), HTTP_INTERNAL_SERVER_ERROR));
            return FALSE;
          } if (catch('IllegalArgumentException', $e)) {
            throw(new HttpScriptletException($e->getMessage(), HTTP_NOT_ACCEPTABLE));
            return FALSE;
          } if (catch('IllegalAccessException', $e)) {
            throw(new HttpScriptletException($e->getMessage(), HTTP_FORBIDDEN));
            return FALSE;
          }
          $request->session->putValue($cidx, $context);
        }

        // Run context's process() method.
        try(); {
          $context->process($request);
        } if (catch('IllegalStateException', $e)) {
          throw(new HttpSessionInvalidException($e->getMessage(), HTTP_BAD_REQUEST));
          return FALSE;
        } if (catch('IllegalAccessException', $e)) {
          throw(new HttpScriptletException($e->getMessage(), HTTP_FORBIDDEN));
          return FALSE;
        }

        delete($class);
      }
      
      // Call state's setup() method
      try(); {
        $request->state->setup($request, $response, $context);
      } if (catch('IllegalStateException', $e)) {
        throw(new HttpScriptletException($e->getMessage(), HTTP_INTERNAL_SERVER_ERROR));
        return FALSE;
      } if (catch('IllegalArgumentException', $e)) {
        throw(new HttpScriptletException($e->getMessage(), HTTP_NOT_ACCEPTABLE));
        return FALSE;
      } if (catch('IllegalAccessException', $e)) {
        throw(new HttpScriptletException($e->getMessage(), HTTP_FORBIDDEN));
        return FALSE;
      }
      
      // Call state's process() method. In case it returns FALSE, the
      // context's insertStatus() method will not be called. This, for
      // example, is useful when process() wants to send a redirect.
      if (FALSE === ($r= $request->state->process($request, $response, $context))) {
        return FALSE;
      }
      
      // If there is no context, we're finished
      if (!$context) return;

      // Tell context to insert form elements. Then store it, if necessary
      $context->insertStatus($response);
      $context->getChanged() && $request->session->putValue($cidx, $context);
    }

    /**
     * Process request
     *
     * @access  protected
     * @param   &scriptlet.xml.XMLScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     */
    function processRequest(&$request, &$response) {
      if (FALSE === $this->processWorkflow($request, $response)) {
      
        // The processWorkflow() method indicates no further processing
        // is to be done. Pass result "up".
        return FALSE;
      }

      return parent::processRequest($request, $response);
    }
  }
?>
