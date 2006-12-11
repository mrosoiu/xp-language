<?php
/*
 *
 * $Id:$
 */

  uses(
    'xml.Tree',
    'org.dia.DiaCompound',
    'org.dia.DiaData',
    'org.dia.DiaLayer'
  );
  
  /**
   * Represents a 'dia' diagram as a whole. This is the root class where the
   * (un-)marshalling of diagrams starts.
   *
   * @see   http://www.gnome.org/projects/dia/
   */
  class DiaDiagram extends DiaCompound {

    var
      $ns= array('dia' => 'http://www.lysator.liu.se/~alla/dia/'), // new (unused) 'http://www.gnome.org/projects/dia/'
      $node_name= 'dia:diagram';

    /**
     * Simple constructor
     *
     * @access  public
     */
    function __construct() {
      $this->initialize();
    }

    /**
     * Returns the next ID for an element (auto increment) with leading 'O'
     * (capital 'o' not zero!)
     * 
     * @model   static
     * @access  public
     * @return  int
     */
    function getId() {
      static $id= 0;
      return 'O'.$id++;
      // too complicated: return sprintf('%0'.(strlen($id)+1).'d', $id++);
    }

    /**
     * Initialize this DiaDiagram with DiaData and DiaLayer
     *
     * @access  public
     */
    function initialize() {
      $this->set('data', new DiaData());
      $this->addLayer(new DiaLayer('Background', TRUE));
    }

    /**
     * Returns the namespace with the given prefix
     *
     * @access  public
     * @return  string uri The namespace URI
     */
    function getNamespace($prefix) {
      return $this->ns[$prefix];
    }

    /**
     * Add namespace declaration to root node
     *
     * @access  public
     * @param   array namespace Example: array($prefix => $url)
     */
    #[@fromDia(xpath= 'namespace::dia', value= 'namespace')]
    function addNamespace($namespace) {
      list($prefix, $uri)= each($namespace);
      $this->ns[$prefix]= $uri;
    }

    /**
     * Returns the DiaData object
     *
     * @access  public
     * @return  &org.dia.DiaData
     */
    function &getData() {
      return $this->getChild('data');
    }

    /**
     * Sets the DiaData object of the diagram
     *
     * @access  public
     * @param   &org.dia.DiaData Data
     */
    #[@fromDia(xpath= 'dia:diagramdata', class= 'org.dia.DiaData')]
    function setData(&$Data) {
      $this->set('data', $Data);
    }

    /**
     * Returns the DiaLayer object with the given name
     *
     * @access  public
     * @param   string name default 'Background'
     * @return  &org.dia.DiaLayer
     */
    function &getLayer($name= 'Background') {
      $Child= &$this->getChild($name);
      if (!is('org.dia.DiaLayer', $Child))
        return throw(new IllegalArgumentException("The object with name='$name' is no DiaLayer!"));
      return $Child;
    }

    /**
     * Adds a DiaLayer object to the diagram
     *
     * @access  public
     * @param   &org.dia.DiaLayer Layer
     */
    #[@fromDia(xpath= 'dia:layer', class= 'org.dia.DiaLayer')]
    function addLayer(&$Layer) {
      $this->set($Layer->getName(), $Layer);
    }

    /**
     * Returns the full XML source of the 'dia' diagram
     *
     * @access  public
     * @return  string XML representation of the DIAgramm
     */
    function getSource($indent= INDENT_DEFAULT) {
      $Node= &$this->getNode();
      $Tree= &new Tree();
      $Tree->root= &$Node;
      return $Tree->getDeclaration()."\n".$Tree->getSource($indent);
    }

    /**
     * Writes the XML representation of this DiaDiagram to the given filename
     *
     * @access  public
     * @param   string filename Filename to save the DIAgramm to
     * @param   boolean zip default TRUE Gzip the DIAgram file?
     */
    function saveTo($filename, $zip= TRUE) {
      // open $File according to $zip
      if ($zip) {
        uses('io.ZipFile');
        $File= &new ZipFile($filename);
      } else {
        uses('io.File');
        $File= &new File($filename);
      }

      // try to write XML source to file
      try (); {
        $File->open(FILE_MODE_WRITE) && // default compression: 6
        $File->write($this->getSource(INDENT_DEFAULT)); // default indentation
        $File->close();
      } if (catch('Exception', $e)) {
        Console::writeLine('Fatal Exception: '.$e->toString());
        exit(-1);
      }
    }

    /************************* interface methods *************************/

    /**
     * Return XML representation of DiaComposite
     *
     * @access  public
     * @return  &xml.Node
     */
    function &getNode() {
      $node= &parent::getNode();
      foreach (array_keys($this->ns) as $prefix) {
        $node->setAttribute('xmlns:'.$prefix, $this->ns[$prefix]);
      }
      return $node;
    }

  }
?>
