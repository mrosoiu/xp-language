<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('xml.Tree');

  /**
   * The document class is a specialized tree with accessor methods to 
   * retrieve elements by specified search criteria. 
   *
   * Note that this is not a full implementation of the Document Object
   * Model (DOM) Level 2 Core Specification, although some of the 
   * method names correspond with those from it.
   *
   * @see      xp://xml.Tree
   * @see      http://www.mozilla.org/docs/dom/domref/dom_doc_ref.html
   * @see      http://java.sun.com/j2se/1.4.2/docs/api/org/w3c/dom/Document.html 
   * @see      http://www.w3.org/TR/2000/REC-DOM-Level-2-Core-20001113 
   * @purpose  Tree
   */
  class Document extends Tree {

    /**
     * Helper method
     *
     * @access  protected
     * @param   &xml.Node starting node
     * @param   string name
     * @param   int max default -1
     * @return  xml.Node[]
     */
    function _getElementsByTagName(&$node, $tagname, $max= -1) {
      $r= array();
      foreach (array_keys($node->children) as $key) {
        if ($tagname == $node->children[$key]->getName()) {
          $r[]= &$node->children[$key];
          if ($max > 0 && sizeof($r) >= $max) return $r;
        }
        if (!empty($node->children[$key]->children)) {
          $r= array_merge($r, $this->_getElementsByTagName(
            $node->children[$key], 
            $tagname
          ));
        }
      }
      return $r;
    }

    /**
     * Helper method
     *
     * @access  protected
     * @param   &xml.Node starting node
     * @param   string attribute
     * @param   string name
     * @param   int max
     * @return  xml.Node[]
     */
    function _getElementsByAttribute(&$node, $attribute, $name, $max) {
      $r= array();
      foreach (array_keys($node->children) as $key) {
        if (
          ($node->children[$key]->hasAttribute($attribute)) &&
          ($name == $node->children[$key]->getAttribute($attribute))
        ) {
          $r[]= &$node->children[$key];
          if ($max > 0 && sizeof($r) >= $max) return $r;
        }
        if (!empty($node->children[$key]->children)) {
          $r= array_merge($r, $this->_getElementsByAttribute(
            $node->children[$key], 
            $attribute, 
            $name
          ));
        }
      }
      return $r;
    }
    
    /**
     * Returns a list of elements of a given tag name in the document.
     *
     * @access  public
     * @param   string tagname
     * @param   int max default -1 maximum number of elements to be returned
     * @return  xml.Node[]
     */
    function getElementsByTagName($tagname, $max= -1) {
      return $this->_getElementsByTagName($this->root, $tagname, $max);
    }

    /**
     * Returns a list of elements of a given name in the document.
     *
     * @access  public
     * @param   string name
     * @param   int max default -1 maximum number of elements to be returned
     * @return  xml.Node[]
     */
    function getElementsByName($name, $max= -1) {
      return $this->_getElementsByAttribute($this->root, 'name', $name);
    }

    /**
     * Returns an object reference to the identified element.
     *
     * @access  public
     * @param   string id
     * @return  &xml.Node
     */
    function &getElementById($id) {
      return $this->_getElementsByAttribute($this->root, 'id', $id, 1);
    }
    
    /**
     * Creates an element of the type specified.
     *
     * @access  public
     * @param   string name
     * @return  &xml.Node node
     */
    function &createElement($name) {
      return new $this->nodeType($name);
    }
    
    /**
     * This is a convenience method that allows direct access to the 
     * child node that is the root element of the document.
     *
     * @access  public
     * @return  &xml.Node
     */
    function &getDocumentElement() {
      return $this->root;
    }
    
    /**
     * Construct a document from a string
     *
     * @model   static
     * @access  public
     * @param   string string
     * @return  &xml.dom.Document
     */
    function &fromString($string) {
      return parent::fromString($string, __CLASS__);
    }


    /**
     * Construct a document from a file
     *
     * @model   static
     * @access  public
     * @param   &xml.File file
     * @return  &xml.dom.Document
     */
    function &fromFile(&$file) {
      return parent::fromFile($file, __CLASS__);
    }
  }
?>
