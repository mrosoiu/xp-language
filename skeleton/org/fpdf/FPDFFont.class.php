<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * PDF font
   *
   * @see      xp://org.fpdf.FPDF
   * @purpose  Represent a font
   */
  class FPDFFont extends Object {
    var 
      $name     = NULL,
      $index    = 0;

    var
      $family,
      $style,
      $type,
      $desc,
      $up,
      $ut,
      $cw,
      $enc,
      $file,
      $originalsize;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     */
    function __construct($name) {
      $this->name= $name;
      
    }
    
    /**
     * Check whether this font is underlined
     *
     * @access  public
     * @return  bool
     */
    function isUnderline() {
      return (FALSE !== strpos($this->style, 'U'));
    }
    
    /**
     * Check whether this font is bold
     *
     * @access  public
     * @return  bool
     */
    function isBold() {
      return (FALSE !== strpos($this->style, 'B'));
    }

    /**
     * Check whether this font is italic
     *
     * @access  public
     * @return  bool
     */
    function isItalic() {
      return (FALSE !== strpos($this->style, 'I'));
    }
    
    /**
     * Load this font's properties from a .ini-file
     *
     * @access  public
     * @param   &util.Properties p
     */
    function configure(&$p) {
      if (NULL == $this->name) return throw(new IllegalStateException('no name defined'));
      
      $this->cw= $p->readArray($this->name, 'cw', array());
      $this->fontname= $p->readString($this->name, 'name', $this->name);
      $this->type= $p->readString($this->name, 'type');
      $this->family= $p->readString($this->name, 'family', $this->name);
      $this->style= strtoupper($p->readString($this->name, 'style'));
      $this->enc= $p->readString($this->name, 'enc');
      $this->diff= $p->readString($this->name, 'diff');
      $this->file= $p->readString($this->name, 'file');
      $this->ut= $p->readInteger($this->name, 'ut');
      $this->up= $p->readInteger($this->name, 'up');
      $this->originalsize= $p->readInteger($this->name, 'originalsize');
      $this->desc= $p->readSection($this->name, 'desc', array());
      
      if (empty($this->type)) $this->type= '__CORE__';
    }
      
    /**
     * Return the width of a specified character
     *
     * @access  public
     * @param   char c
     * @return  int
     */
    function getCharWidth($c) {
      return $this->charwidths[ord($c)];
    }
  }
?>
