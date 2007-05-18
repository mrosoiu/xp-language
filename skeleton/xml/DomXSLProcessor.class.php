<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'xml.TransformerException',
    'io.FileNotFoundException',
    'xml.IXSLProcessor',
    'xml.XSLCallback'
  );
  
  /**
   * XSL Processor using DomXML
   * 
   * Usage example [Transform two files]
   * <code>
   *   $proc= new DomXSLProcessor();
   *   $proc->setXSLFile('test.xsl');
   *   $proc->setXMLFile('test.xml');
   *   
   *   try {
   *     $proc->run();
   *   } catch(TransformerException $e) {
   *     $e->printStackTrace();
   *     exit();
   *   }
   *
   *   var_dump($proc->output());
   * </code>
   *
   * @purpose  Transform XML/XSLT using PHPs XSLT functions
   * @ext      xslt
   */
  class DomXSLProcessor extends Object implements IXSLProcessor {
    public 
      $processor    = NULL,
      $stylesheet   = NULL,
      $document     = NULL,
      $params       = array(),
      $output       = '';

    public
      $_instances   = array(),
      $_base        = '';
      
    /**
     * Constructor
     *
     */
    public function __construct() {
      $this->_checkErrors();
    }
          
    /**
     * Set base directory
     *
     * @param   string dir
     */
    public function setBase($dir) {
      $this->_base= rtrim(realpath($dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * Get base
     *
     * @return  string
     */
    public function getBase() {
      return $this->_base;
    }
    
    /**
     * Set a scheme handler
     *
     * @param   mixed callback
     * @see     php://xslt_set_scheme_handlers
     */
    public function setSchemeHandler($defines) {
      // Not implemented in DOM
    }

    /**
     * Set XSL file
     *
     * @param   string file file name
     * @throws  io.FileNotFoundException
     */
    public function setXSLFile($file) {
      if (!file_exists($this->_base.$file))
        throw new FileNotFoundException($this->_base.$file.' not found');
      
      $this->stylesheet= new DOMDocument();
      $this->stylesheet->load($this->_base.$file);
      $this->_checkErrors();
    }
    
    /**
     * Set XSL buffer
     *
     * @param   string xsl the XSL as a string
     */
    public function setXSLBuf($xsl) {
      $this->stylesheet= new DOMDocument();
      $this->stylesheet->loadXML($xsl);
      strlen($this->_base) && $this->stylesheet->documentURI= $this->_base;
      $this->_checkErrors();
    }

    /**
     * Set XML file
     *
     * @param   string file file name
     */
    public function setXMLFile($file) {
      if (!file_exists($this->_base.$file)) {
        throw(new FileNotFoundException($this->_base.$file.' not found'));
      }
      
      $this->document= new DOMDocument();
      $this->document->load($file);
      $this->_checkErrors();
    }
    
    /**
     * Set XML buffer
     *
     * @param   string xml the XML as a string
     */
    public function setXMLBuf($xml) {
      $this->document= new DOMDocument();
      $this->document->loadXML($xml);
      $this->_checkErrors();
    }

    /**
     * Set XSL transformation parameters
     *
     * @param   array params associative array { param_name => param_value }
     */
    public function setParams($params) {
      $this->params= $params;
    }
    
    /**
     * Set XSL transformation parameter
     *
     * @param   string name
     * @param   string value
     */
    public function setParam($name, $val) {
      $this->params[$name]= $val;
    }
    
    /**
     * Retrieve XSL transformation parameter
     *
     * @param   string name
     * @return  string value
     */
    public function getParam($name) {
      return $this->params[$name];
    }    

    /**
     * Retrieve messages generate during processing.
     *
     * @return  string[]
     */
    public function getMessages() {
      return libxml_get_last_error();
    }
    
    /**
     * Register object instance under defined name
     * for access from XSL callbacks.
     *
     * @param   string name
     * @param   lang.Object instance
     */
    function registerInstance($name, $instance) {
      $this->_instances[$name]= $instance;
    }

    /**
     * Run the XSL transformation
     *
     * @return  bool success
     * @throws  xml.TransformerException
     */
    public function run() {
      $this->_checkErrors();
      
      $this->processor= new XSLTProcessor();
      $this->processor->importStyleSheet($this->stylesheet);
      $this->processor->setParameter('', $this->params);
      
      // If we have registered instances, register them in XSLCallback
      if (sizeof($this->_instances)) {
        $cb= XSLCallback::getInstance();
        foreach ($this->_instances as $name => $instance) {
          $cb->registerInstance($name, $instance);
        }
      }

      $this->processor->registerPHPFunctions(array('XSLCallback::invoke'));
      
      // Start transformation
      $result= $this->processor->transformToXML($this->document);
      $this->output= $result;

      if (FALSE === $this->output) {
        $this->_checkErrors();
        
        // Fallthrough
        throw new TransformerException('Unknown transformation error occurred.');
      }

      // Perform cleanup when necessary (free singleton for further use)
      sizeof($this->_instances) && XSLCallback::getInstance()->clearInstances();
      
      return TRUE;
    }
    
    /**
     * Check for XML/XSLT errors and throw exceptions accordingly
     *
     * @throws  xml.TransformerException in case an XML error has occurred
     */
    protected function _checkErrors() {
      if ($error= libxml_get_last_error()) {
        libxml_clear_errors();
        
        throw new TransformerException(sprintf("Transformation failed: #%d: %s\n  at %s, line %d, column %d",
          $error->code,
          trim($error->message),
          (strlen($error->file) ? $error->file : '<buffer>' ),
          $error->line,
          $error->column
        ));
      }
    }

    /**
     * Retrieve the transformation's result
     *
     * @return  string
     */
    public function output() {
      return $this->output;
    }
  }
?>
