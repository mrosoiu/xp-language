<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'scriptlet.HttpScriptletResponse',
    'scriptlet.xml.OutputDocument',
    'xml.XSLProcessor'
  );
  
  define('XSLT_BUFFER', 0x0000);
  define('XSLT_FILE',   0x0001);
  
  /**
   * Wraps XML response
   *
   * Instead of writing directly to the client, use the addFormValue,
   * addFormResult or addFormError methods to access the resulting
   * XML document tree.
   *
   * @see      xp://scriptlet.xml.OutputDocument
   * @see      xp://scriptlet.HttpScriptletResponse  
   * @purpose  Scriptlet response wrapper
   */
  class XMLScriptletResponse extends HttpScriptletResponse {
    var 
      $document     = NULL,
      $processor    = NULL,
      $params       = array();
    
    var
      $_processed   = TRUE,
      $_stylesheet  = array();
    
    /**
     * Constructor
     *
     * @access  public
     * @param   xml.XSLProcessor processor
     */
    function __construct($processor= NULL) {
      $this->processor= &$processor;
      $this->document= &new OutputDocument();
    }

    /**
     * Set Processor
     *
     * @access  public
     * @param   &xml.IXSLProcessor processor
     */
    function setProcessor(&$processor) {
      $this->processor= &$processor;
    }

    /**
     * Get Processor
     *
     * @access  public
     * @return  &xml.IXSLProcessor processor
     */
    function &getProcessor() {
      return $this->processor;
    }

    /**
     * Set whether this document needs to be processed
     *
     * @access  public
     * @param   bool processed
     */
    function setProcessed($processed) {
      $this->_processed= $processed;
    }

    /**
     * Overwritten method from parent class
     *
     * @access  public
     * @param   string s string to add to the content
     * @throws  lang.IllegalAccessException in case processing is requested
     */
    function write($s) {
      if ($this->_processed) {
        return throw(new IllegalAccessException('Cannot write directly'));
      }
      parent::write($s);
    }

    /**
     * Overwritten method from parent class
     *
     * @access  public
     * @param   string content Content
     * @throws  lang.IllegalAccessException
     */
    function setContent($content) {
      if ($this->_processed) {
        return throw(new IllegalAccessException('Cannot write directly'));
      }
      parent::setContent($content);
    }
    
    /**
     * Add a child to the formvalues node. The XML representation
     * is probably best shown with a couple of examples:
     *
     * Example: a string
     * <xmp>
     *   <param name="__form" xsi:type="xsd:string">new</param>
     * </xmp>
     *
     * Example: an associative array
     * <xmp>
     *   <param name="data[domain]" xsi:type="xsd:string">thekidabc</param>
     *   <param name="data[tld]" xsi:type="xsd:string">de</param>
     * </xmp>
     *
     * Example: an object
     * <xmp>
     *   <param name="faxnumber" xsi:type="xsd:object">
     *     <pre>721</pre>
     *     <number>1234567</number>
     *     <lcode>+49</lcode>
     *   </param>
     * </xmp>     
     *
     * @access  public
     * @param   string name name
     * @param   &mixed val
     */
    function addFormValue($name, &$val) {
      if (!is_array($val)) $val= array($val);

      foreach (array_keys($val) as $k) {
        if (is_array($val[$k])) {
          $c= &Node::fromArray($val[$k], 'param');
        } else if (is_object($val[$k])) {
          $c= &Node::fromObject($val[$k], 'param');
        } else {
          $c= &new Node('param', $val[$k]);
        }
        $c->attribute['name']= $name.(is_int($k) ? '' : '['.$k.']');
        $c->attribute['xsi:type']= 'xsd:'.gettype($val[$k]);
        $this->document->formvalues->addChild($c);
      } 
    }

    /**
     * Adds an error. The XML representation will look like this:
     * <xmp>
     *   <error
     *    checker="foo.bar.wrapper.MyLoginDataChecker"
     *    type="user_nonexistant"
     *    field="username"                    
     *   />                                                 
     * </xmp>
     *
     * @access  public
     * @param   string checker The class checking the input
     * @param   string type The error type
     * @param   string field default '*' The form field corresponding
     * @param   mixed info default NULL 
     * @return  bool FALSE
     */
    function addFormError($checker, $type, $field= '*', $info= NULL) {
      if (is_array($info)) {
        $c= &Node::fromArray($info, 'error');
      } else if (is_object($info)) {
        $c= &Node::fromObject($info, 'error');
      } else {
        $c= &new Node('error', $info);
      }
      $c->attribute= array(
        'type'        => $type,
        'field'       => $field,
        'checker'     => $checker
      );
      $this->document->formerrors->addChild($c);
      
      return FALSE;
    }
    
    /**
     * Add a child to the formresult node. You may add _any_ node
     * here since there is no special specification what do with
     * nodes besides formvalues and formerrors
     *
     * @access  public
     * @param   xml.Node node
     * @return  &xml.Node added node
     * @throws  lang.IllegalArgumentException
     */
    function &addFormResult(&$node) {
      if (
        ('formerrors' == $node->name) ||
        ('formvalues' == $node->name)
      ) {
        return throw(new IllegalArgumentException($node->name.' not allowed here'));
      }
      return $this->document->formresult->addChild($node);
    }
    
    /**
     * Sets the absolute path to the XSL stylesheet
     *
     * @access  public
     * @param   string stylesheet
     * @param   int type default XSLT_FILE
     */
    function setStylesheet($stylesheet, $type= XSLT_FILE) {
      $this->_stylesheet= array($type, $stylesheet);
    }

    /**
     * Retrieves whether a stylesheet has been set
     *
     * @access  public
     * @return  bool
     */
    function hasStylesheet() {
      return !empty($this->_stylesheet);
    }
    
    /**
     * Sets an XSL parameter
     *
     * @access  public
     * @param   string name
     * @param   string value
     */
    function setParam($name, $value) {
      $this->params['__'.$name]= $value;
    }
    
    /**
     * Retrieves an XSL parameter by its name
     *
     * @access  public
     * @param   string name
     * @return  string value
     */
    function getParam($name) {
      return $this->params['__'.$name];
    }
    
    /**
     * Forward to another state (optionally with query string and fraction)
     *
     * @access  public
     * @param   string state
     * @param   string query default NULL the query string without the leading "?"
     * @param   string fraction default NULL the fraction without the leading "#"
     */
    function forwardTo($state, $query= NULL, $fraction= NULL) {
      sscanf(
        getenv('REQUEST_URI'), 
        '/xml/%[^.].%[^./].psessionid=%[^/]', 
        $product,
        $language,
        $sessionId
      );
      $this->sendRedirect(sprintf(
        '%s://%s/xml/%s.%s%s/%s%s%s', 
        ('on' == getenv('HTTPS') ? 'https' : 'http'),
        getenv('HTTP_HOST'),          
        $product,
        $language,
        ('' == (string)$sessionId) ? '' : '.psessionid='.$sessionId,
        $state,
        ('' == (string)$query) ? '' : '?'.$query,
        ('' == (string)$fraction) ? '' : '#'.$fraction        
      ));
    }
    
    /**
     * Transforms the OutputDocument's XML and the stylesheet
     *
     * @throws  lang.IllegalStateException if no stylesheet is set
     * @throws  scriptlet.HttpScriptletException if the transformation fails
     * @see     xp://scriptlet.HttpScriptletResponse#process
     */
    function process() {
      if (!$this->_processed) return FALSE;

      switch ($this->_stylesheet[0]) {
        case XSLT_FILE:
          try(); {
            $this->processor->setXSLFile($this->_stylesheet[1]);
          } if (catch('FileNotFoundException', $e)) {
            return throw(new HttpScriptletException($e->getMessage(), HTTP_NOT_FOUND));
          }
          break;
          
        case XSLT_BUFFER:
          $this->processor->setXSLBuf($this->_stylesheet[1]);
          break;
        
        default:
          return throw(new IllegalStateException(
            'Unknown type ('.$this->_stylesheet[0].') for stylesheet'
          ));
      }
      
      $this->processor->setParams($this->params);
      $this->processor->setXMLBuf(
        $this->document->getDeclaration()."\n".
        $this->document->getSource(FALSE)
      );
      
      // Transform XML/XSL
      try(); {
        $this->processor->run();
      } if (catch('TransformerException', $e)) {
        return throw(new HttpScriptletException($e->getMessage(), HTTP_INTERNAL_SERVER_ERROR));
      }
      
      $this->content= &$this->processor->output();
      
      return TRUE;
    }
    
    /**
     * Destructor
     *
     * @access  public
     */
    function __destruct() {
      delete($this->document);
      delete($this->processor);
    }
  }
?>
