<?php
/* This class is part of the XP framework's experiments
 *
 * $Id$ 
 */

  uses('xml.Tree', 'xml.QName', 'xml.XMLFormatException');

  /**
   * Marshalls XML from objects by using annotations.
   *
   * Example:
   * <code>
   *   // [...create transmission object...]
   *
   *   try(); {
   *     $xml= Marshaller::marshal($transmission);
   *   } if (catch('Exception', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *
   *   echo $xml;
   * </code>
   *
   * @test     xp://net.xp_framework.unittest.xml.MarshallerTest
   * @ext      dom
   * @see      http://castor.org/xml-mapping.html
   * @purpose  XML databinding
   */
  class Marshaller extends Object {
  
    /**
     * Iterate over class methods with @xmlfactory annotation
     *
     * @model   static
     * @access  protected
     * @param   &lang.Object instance
     * @param   &lang.XPClass class
     * @param   &xml.Node node
     */
    function recurse(&$instance, &$class, &$node) {
      foreach ($class->getMethods() as $method) {
        if (!$method->hasAnnotation('xmlfactory', 'element')) continue;
        
        $element= $method->getAnnotation('xmlfactory', 'element');
        
        // Attributes
        if ('@' == $element{0}) {
          $node->setAttribute(substr($element, 1), $method->invoke($instance));
          continue;
        }
        
        // Node content
        if ('.' == $element) {
          $node->setContent($method->invoke($instance));
          continue;
        }
        
        // Create subnodes based on runtime type of method:
        //
        // - For scalar types, create a node with the element's name and set
        //   the node's content to the value
        //
        // - For arrays we iterate over keys and values (FIXME: we assume the 
        //   array is a string => scalar map!)
        //
        // - For collections, add a node with the element's name and invoke
        //   the recurse() method for each value in the collection.
        //
        // - For objects, add a new node and invoke the recurse() method
        //   on it.
        $result= &$method->invoke($instance);
        if (is_scalar($result) || NULL === $result) {
          $node->addChild(new Node($element, $result));
        } else if (is_array($result)) {
          $child= &$node->addChild(new Node($element));
          foreach ($result as $key => $val) {
            $child->addChild(new Node($key, $val));
          }
        } else if (is('lang.Collection', $result)) {
          $elementClass= &$result->getElementClass();
          foreach ($result->values() as $value) {
            Marshaller::recurse($value, $elementClass, $node->addChild(new Node($element)));
          }
        } else if (is('lang.Object', $result)) {
          Marshaller::recurse($result, $result->getClass(), $node->addChild(new Node($element)));
        }
      }
    }

    /**
     * Marshal an object to xml
     *
     * @model   static
     * @access  public
     * @param   &lang.Object instance
     * @param   xml.QName qname default NULL
     * @return  string xml
     */
    function marshal(&$instance, $qname= NULL) {
      $class= &$instance->getClass();

      // Create XML tree and root node. Use the information provided by the
      // qname argument if existant, use the class` non-qualified (and 
      // lowercased) name otherwise.
      $tree= &new Tree();
      if ($qname) {
        $prefix= $qname->prefix ? $qname->prefix : $qname->localpart{0};
        $tree->root->setName($prefix.':'.$qname->localpart);
        $tree->root->setAttribute('xmlns:'.$prefix, $qname->namespace);
      } else {
        $tree->root->setName(get_class($instance));
      }
      
      Marshaller::recurse($instance, $class, $tree->root);
      return $tree->getSource(INDENT_DEFAULT);
    }
  }
?>
