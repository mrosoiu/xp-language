<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Style class
   *
   * @see   xp://img.Image#setStyle
   */
  class ImgStyle extends Object {
    public
      $colors   = array(),
      $pixels   = array();
      
    public
      $_hdl     = IMG_COLOR_STYLED;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   img.Color[] colors an array of pixels
     */
    public function __construct(&$colors) {
      $this->colors= $colors;
      for ($i= 0, $s= sizeof($this->colors); $i < $s; $i++) {
        $this->pixels[]= $this->colors[$i]->_hdl;
      }

      
    }
    
    /**
     * Retrieves the style array as used for the second argument
     * int imagesetstyle()
     *
     * @access  public
     * @return  int[] array of color indices
     * @see     php://imagesetstyle
     */
    public function getPixels() {
      return $this->pixels;
    }
  }
?>
