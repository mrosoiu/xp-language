<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('xml.XML', 'xml.parser.XMLParser', 'xml.Node');
 
  /**
   * The Tree class represents a tree which can be exported
   * to and imported from an XML document.
   *
   * @see      xp://xml.parser.XMLParser
   * @purpose  Tree
   */
  class Tree extends XML {
    var 
      $root     = NULL,
      $children = array(),
      $nodeType = 'node';

    var
      $_cnt,
      $_cdata,
      $_objs;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string rootName default 'document'
     */
    function __construct($rootName= 'document') {
      $this->root= &new Node($rootName);
    }
    
    /**
     * Retrieve XML representation
     *
     * @access  public
     * @param   bool indent default TRUE whether to indent
     * @return  string
     */
    function getSource($indent= TRUE) {
      return (isset($this->root)
        ? $this->root->getSource($indent)
        : NULL
      );
    }
    
    /**
     * Add a child to this tree
     *
     * @access  public
     * @param   &xml.Node child 
     * @return  &xml.Node the added child
     */   
    function &addChild(&$child) {
      return $this->root->addChild($child);
    }

    /**
     * Construct an XML tree from a string.
     *
     * <code>
     *   $tree= &Tree::fromString('<document>...</document>');
     * </code>
     *
     * @model   static
     * @access  public
     * @param   string string
     * @param   string c default __CLASS__ class name
     * @return  &xml.Tree
     * @throws  xml.XMLFormatException in case of a parser error
     */
    function &fromString($string, $c= __CLASS__) {
      $parser= &new XMLParser();
      $tree= &new $c();
      try(); {
        $parser->setCallback($tree);
        $parser->parse($string, 1);
        delete($parser);
      } if (catch('XMLFormatException', $e)) {
        return throw($e);
      }
      
      return $tree;
    }
    
    /**
     * Construct an XML tree from a file.
     *
     * <code>
     *   $tree= &Tree::fromFile(new File('foo.xml');
     * </code>
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @param   string c default __CLASS__ class name
     * @return  &xml.Tree
     * @throws  xml.XMLFormatException in case of a parser error
     * @throws  io.IOException in case reading the file fails
     */ 
    function &fromFile(&$file, $c= __CLASS__) {
      $parser= &new XMLParser();
      $tree= &new $c();
      
      try(); {
        $parser->setCallback($tree);
        $parser->dataSource= $file->uri;
        $file->open(FILE_MODE_READ);
        $string= $file->read($file->size());
        $file->close();
        $parser->parse($string);
        delete($parser);
      } if (catch('XMLFormatException', $e)) {
        return throw($e);
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      
      return $tree;
    }
    
    /**
     * Callback function for XMLParser
     *
     * @access  public
     * @param   resource parser
     * @param   string name
     * @param   string attrs
     * @see     xp://xml.parser.XMLParser
     */
    function onStartElement($parser, $name, $attrs) {
      $this->_cdata= '';

      $element= &new $this->nodeType($name, NULL, $attrs);
      if (!isset($this->_cnt)) {
        $this->root= &$element;
        $this->_objs[1]= &$element;
        $this->_cnt= 1;
      } else {
        $this->_cnt++;
        $this->_objs[$this->_cnt]= &$element;
      }
    }
   
    /**
     * Callback function for XMLParser
     *
     * @access  public
     * @param   resource parser
     * @param   string name
     * @see     xp://xml.parser.XMLParser
     */
    function onEndElement($parser, $name) {
      if ($this->_cnt > 1) {
        $node= &$this->_objs[$this->_cnt];
        $node->content= $this->_cdata;
        $parent= &$this->_objs[$this->_cnt- 1];
        $parent->addChild($node);
        $this->_cdata= '';
      }
      $this->_cnt--;
    }

    /**
     * Callback function for XMLParser
     *
     * @access  public
     * @param   resource parser
     * @param   string cdata
     * @see     xp://xml.parser.XMLParser
     */
    function onCData($parser, $cdata) {
      $this->_cdata.= $cdata;
    }

    /**
     * Callback function for XMLParser
     *
     * @access  public
     * @param   resource parser
     * @param   string data
     * @see     xp://xml.parser.XMLParser
     */
    function onDefault($parser, $data) {
    }

  } implements(__FILE__, 'xml.parser.ParserCallback');
?>
