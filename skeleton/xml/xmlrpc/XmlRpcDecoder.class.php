<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('xml.XMLFormatException');

  /**
   * XML-RPC decoder
   *
   * @ext      xml
   * @see      http://xmlrpc.com
   * @purpose  Decode XML-RPC data
   */
  class XmlRpcDecoder extends Object {
  
    /**
     * Decode XML node-set into the data structures
     * they represent
     *
     * @access  public
     * @param   &xml.Node node
     * @return  &mixed
     */
    function &decode(&$node) {
      return $this->_unmarshall($node);
    }
  
    
    /**
     * Recursively deserialize data for the given node.
     *
     * @access  protected
     * @param   &xml.Node node
     * @return  &mixed
     * @throws  lang.IllegalArgumentException if the data cannot be deserialized
     * @throws  lang.ClassNotFoundException in case a XP object's class could not be loaded
     * @throws  xml.XMLFormatException
     */
    function &_unmarshall(&$node) {
      if (!is('xml.Node', $node->children[0]))
        return throw(new XMLFormatException('Tried to access nonexistant node.'));
        
      switch ($node->children[0]->getName()) {
        case 'struct':
          $ret= array();
          foreach (array_keys($node->children[0]->children) as $idx) {
            $data= array();
            $data[$node->children[0]->children[$idx]->children[0]->getName()]= &$node->children[0]->children[$idx]->children[0];
            $data[$node->children[0]->children[$idx]->children[1]->getName()]= &$node->children[0]->children[$idx]->children[1];
            $ret[$data['name']->getContent()]= &$this->_unmarshall($data['value']);
            unset($data);
          }
          
          // Check whether this is a XP object
          if (isset($ret['__xp_class'])) {
            $cname= $ret['__xp_class'];
            
            // Load the class definition
            try(); {
              XPClass::forName($cname);
            } if (catch('ClassNotFoundException', $e)) {
              return throw($e);
            }
            
            // Cast the object to the class
            unset($ret['__xp_class']);
            $ret= &cast($ret, xp::reflect($cname));
          }
          
          return $ret;
          break;
          
        case 'array':
          $ret= array();
          foreach (array_keys($node->children[0]->children[0]->children) as $idx) {
            $ret[]= &$this->_unmarshall($node->children[0]->children[0]->children[$idx]);
          }
          return $ret;
          break;
        
        case 'int':
        case 'i4':
          $i= (int)$node->children[0]->getContent();
          return $i;
        
        case 'double':
          $d= (double)$node->children[0]->getContent();
          return $d;
        
        case 'boolean':
          $b= (bool)$node->children[0]->getContent();
          return $b;
        
        case 'string':
          $s= (string)$node->children[0]->getContent();
          return $s;
        
        case 'dateTime.iso8601':
          $d= &Date::fromString($node->children[0]->getContent());
          return $d;
          
        default:
          return throw(new IllegalArgumentException('Could not decode node as it\'s type is not supported: '.$node->children[0]->getName()));
          break;
      }
    }
  }
?>
