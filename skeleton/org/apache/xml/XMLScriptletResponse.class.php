<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'org.apache.HttpScriptletResponse',
    'org.apache.xml.OutputDocument',
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
   * @see org.apache.xml.OutputDocument
   * @see org.apache.HttpScriptletResponse  
   */
  class XMLScriptletResponse extends HttpScriptletResponse {
    var 
      $document,
      $processor,
      $stylesheet,
      $params,
      $page;
    
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct() {
      $this->document= &new OutputDocument();
      $this->processor= &$this->getProcessor();
      parent::__construct();
    }

    /**
     * Retrieve the XSL processor
     *
     * @access  protected
     * @return  &xml.XSLProcessor
     */
    function &getProcessor() {
      return new XSLProcessor();
    }
    
    /**
     * Overwritten method from parent class
     *
     * @access  public
     * @throws  IllegalAccessException
     * @see     #addFormValue
     * @see     #addFormResult
     * @see     #addFormError
     */
    function write() {
      throw(new IllegalAccessException('Cannot write directly'));
    }
    
    /**
     * Overwritten method from parent class
     *
     * @access  public
     * @throws  IllegalAccessException
     * @see     #addFormValue
     * @see     #addFormResult
     * @see     #addFormError
     */
    function setContent() {
      throw(new IllegalAccessException('Cannot write directly'));
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
     * @return  &mixed val value
     */
    function &addFormValue($name, &$val) {
      if (!is_array($val)) $val= array($val);

      foreach (array_keys($val) as $k) {
        if (is_array($val[$k])) {
          $c= &Node::fromArray($val[$k], 'param');
        } elseif (is_object($val[$k])) {
          $c= &Node::fromObject($val[$k], 'param');
        } else {
          $c= &new Node('param', $val[$k]);
        }
        $c->attribute['name']= $name.(is_int($k) ? '' : "[{$k}]");
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
      } elseif (is_object($info)) {
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
     * @return  xml.Node added node
     * @throws  IllegalArgumentException if you try to add a node with
     *          the name "formerrors" or "formvalues"
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
      $this->stylesheet= array($type, $stylesheet);
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
     * Transorms the OutputDocument's XML and the stylesheet
     *
     * DOMXML/EXSLT variant LEAKS HUGE CHUNKS OF MEMORY!!!
     * <code>
     *   $doc= &domxml_open_mem(
     *     $this->document->getDeclaration()."\n".
     *     $this->document->getSource(FALSE)
     *   );
     *   $xsl= &domxml_xslt_stylesheet_file($this->stylesheet);
     *   $result= &$xsl->process($doc, $this->params, FALSE);
     *   $this->content= &$result->dump_mem();
     * </code>
     *
     * @throws  lang.IllegalStateException if no stylesheet is set
     * @throws  xml.TransformerException if the transformation fails
     * @see     org.apache.HttpScriptletResponse#process
     */
    function process() {
      parent::process();
      switch ($this->stylesheet[0]) {
        case XSLT_FILE:
          $this->processor->setXSLFile($this->stylesheet[1]);
          break;
          
        case XSLT_BUFFER:
          $this->processor->setXSLBuf($this->stylesheet[1]);
          break;
        
        default:
          return throw(new IllegalStateException(
            'Unknown type ('.$this->stylesheet[0].') for stylesheet'
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
        return throw($e);
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
      $this->document->__destruct();
      $this->processor->__destruct();
      parent::__destruct();
    }
  }
?>
