<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  /**
   * Creates dependencies
   *
   * @purpose  Factory
   * @deprecated
   */
  class DependencyFactory extends Object {

    /**
     * Create a dependency
     *
     * @model   static
     * @access  public
     * @param   string scheme
     * @param   string name
     * @param   array config
     * @return  &org.apache.xml.generator.Dependency
     * @throws  lang.IllegalArgumentException
     */  
    function factory($scheme, $name, $config) {
      static $c= array();
      
      // Load class for scheme if not already done
      if (!isset($c[$scheme])) {
        try(); {
          $c[$scheme]= XPClass::forName($config[$scheme.'.class']);
        } if (catch('ClassNotFoundException', $e)) {
          return throw(new IllegalArgumentException(
            'Dependencytype "'.$scheme.'" not supported: '.$e->getMessage()
          ));
        }
      }
      
      // Return an instance
      return $c[$scheme]->newInstance($name);
    }
  }
?>
