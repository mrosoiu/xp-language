<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * True type font
   *
   * @see      php://imagettftext
   * @see      xp://img.shapes.Text
   * @purpose  Font
   */
  class TrueTypeFont extends Object {
    var
      $name=            '',
      $size=            0.0,
      $angle=           0.0,
      $antialiasing=    TRUE;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name the truetype font's name
     * @param   float size default 10.0
     * @param   float angle default 0.0
     */ 
    function __construct($name, $size= 10.0, $angle= 0.0) {
      $this->name= $name;
      $this->size= $size;
      $this->angle= $angle;
    }
    
    /**
     * Draw function
     *
     * @access  public
     * @param   &resource hdl an image resource
     * @param   &img.Color col
     * @param   string text
     * @param   int x
     * @param   int y
     */
    function drawtext(&$hdl, &$col, $text, $x, $y) {
      return imagettftext(
        $hdl,
        $this->size,
        $this->angle,
        $x,
        $y,
        $col->handle * ($this->antialiasing ? 1 : -1),
        $this->name,
        $text
      );
    }
  }
?>
