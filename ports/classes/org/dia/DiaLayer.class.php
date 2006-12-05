<?php
/*
 *
 * $Id:$
 */

  uses(
    'org.dia.DiaCompound'
  );

  /**
   * Representation of a 'dia:layer' node. Every diagram may have multiple
   * layers, each containing objects (shapes) of the diagram.
   *
   */
  class DiaLayer extends DiaCompound {

    var
      $name= NULL, 
      $visibility= NULL,
      $node_name= 'dia:layer';


    /**
     * Create new instance of DiaLayer with the given name.
     *
     * @access  public
     * @param   string name default NULL
     * @param   bool visible default NULL
     */
    function __construct($name= NULL, $visible= NULL) {
      if (isset($name)) $this->setName($name);
      if (isset($visible)) $this->setVisibility($visible);
    }

    /**
     * Get the name of this DiaLayer
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }

    /**
     * Set the name of this DiaLayer
     *
     * @access  public
     * @param   string name
     */
    #[@fromDia(xpath= 'attribute::name', value= 'string')]
    function setName($name) {
      $this->name= $name;
    }

    /**
     * Get the visibility of this DiaLayer
     *
     * @access  public
     * @return  bool
     */
    function getVisibility() {
      return $this->visibility;
    }

    /**
     * Set the visibility of this DiaLayer
     *
     * @access  public
     * @param   bool visible
     */
    #[@fromDia(xpath= 'attribute::visible', value= 'boolean')]
    function setVisibility($visible) {
      $this->visibility= $visible;
    }

    /**
     * Adds a standard 'Text' dia object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaTextObject Text
     */
    #[@fromDia(xpath= 'dia:object[@type="Standard - Text"]', class= 'org.dia.DiaTextObject')]
    function addText(&$Text) {
      $this->set($Text->getName(), $Text);
    }

    // TODO: other 'Standard - *' objects: Arc, BezierLine, Box, Ellipse,
    // Image, Line, Polygon, PolyLine, ZigZagLine...

    /**
     * Adds a 'LargePackage' object to the layer
     *
     * HINT: objects like this, which may contain other objects, should be
     * added first, so that their child objects appear 'in front' of this object
     *
     * @access  public
     * @param   &org.dia.DiaUMLLargePackage Package
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - LargePackage"]', class= 'org.dia.DiaUMLLargePackage')]
    function addLargePackage(&$Package) {
      $this->set($Package->getName(), $Package);
    }

    /**
     * Adds a 'UMLClass' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLClass Class
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Class"]', class= 'org.dia.DiaUMLClass')]
    function addClass(&$Class) {
      $this->set($Class->getName(), $Class);
    }

    /**
     * Adds a 'UMLGeneralization' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLGeneralization Gen
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Generalization"]', class= 'org.dia.DiaUMLGeneralization')]
    function addGeneralization(&$Gen) {
      $this->set($Gen->getName(), $Gen);
    }

    /**
     * Adds a 'UMLDependency' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLDependency Dep
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Dependency"]', class= 'org.dia.DiaUMLDependency')]
    function addDependency(&$Dep) {
      $this->set($Dep->getName(), $Dep);
    }

    /**
     * Adds a 'UMLRealizes' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLRealizes Real
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Realizes"]', class= 'org.dia.DiaUMLRealizes')]
    function addRealizes(&$Real) {
      $this->set($Real->getName(), $Real);
    }

    /**
     * Adds a 'UMLImplements' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLImplements Impl
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Implements"]', class= 'org.dia.DiaUMLImplements')]
    function addImplements(&$Impl) {
      $this->set($Impl->getName(), $Impl);
    }

    /**
     * Adds a 'UMLAssociation' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLAssociation Assoc
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Association"]', class= 'org.dia.DiaUMLAssociation')]
    function addAssociation(&$Assoc) {
      $this->set($Assoc->getName(), $Assoc);
    }

    /**
     * Adds a 'UMLNote' object to the layer
     *
     * @access  public
     * @param   &org.dia.DiaUMLNote Note
     */
    #[@fromDia(xpath= 'dia:object[@type="UML - Note"]', class= 'org.dia.DiaUMLNote')]
    function addNote(&$Note) {
      $this->set($Note->getName(), $Note);
    }

    /**
     * Returns the object with the given name
     *
     * @access  public
     * @return  &org.dia.DiaObject
     */
    function &getObject($name) {
      $Child= &$this->getChild($name);
      if (!is('org.dia.DiaObject', $Child))
        return throw(new IllegalArgumentException("The object with name='$name' is no DiaObject!"));
      return $Child;
    }

    /**
     * Returns all objects of the given type contained in this layer
     *
     * @access  public
     * @param   string type The DiaObject->getNodeType() (type)
     * @return  org.dia.DiaObject[]
     */
    function getObjectsByType($type) {
      $children= $this->getChildren();
      $ret= array();
      foreach (array_keys($children) as $key) {
        if (!is('org.dia.DiaObject', $children[$key])) continue;
        $Object= &$children[$key];
        if ($Object->getNodeType() === 'type') {
          $ret[]= &$Object;
          // TODO: recurse into objects that may contain other objects
          // i.e. LargePackage, ...
        }
      }
      return $ret;
    }

    /**
     * Adds a non-UML object to the layer. This is currently not used, as all
     * objects get their own method + annotation. ATM only 'Standard - Text'
     * objects will be processed/added by the 'addText()' method.
     *
     * Annotation uses workaround 'string(@type)' to be able to evaluate
     * annotation in XPClass
     *
     * fromDia(xpath= 'dia:object[not(starts-with(string(@type), "UML"))]', class= 'org.dia.DiaObject')
     *
     * @access  public
     * @param   &org.dia.DiaObject Object
     */
    function addObject(&$Object) {
      $this->set($Object->getName(), $Object);
    }

    /**
     * Return XML representation of DiaComposite
     *    
     * @access  public
     * @return  &xml.Node
     */ 
    function &getNode() {
      $Node= &parent::getNode(); 
      if (isset($this->name))
        $Node->setAttribute('name', $this->name);
      if (isset($this->visibility))
        $Node->setAttribute('visible', $this->visibility ? 'true' : 'false');
      return $Node;
    }    
  }
?>
