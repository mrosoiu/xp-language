<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Converts a truecolor image to a paletted image
   *
   * @ext      gd
   * @see      xp://img.convert.ImageConverter
   * @purpose  Converter
   */
  class PaletteConverter extends Object {
    var
      $dither   = FALSE,
      $ncolors  = 0;

    /**
     * Constructor
     *
     * @see     php://imagetruecolortopalette
     * @access  public
     * @param   bool dither default FALSE indicates if the image should be dithered
     * @param   int ncolors default 256 maximum # of colors retained in the palette
     */
    function __construct($dither= FALSE, $ncolors= 256) {
      $this->dither= $dither;
      $this->ncolors= $ncolors;
    }
  
    /**
     * Convert an image. Returns TRUE when successfull, FALSE if image is
     * not a truecolor image.
     *
     * @access  public
     * @param   &img.Image image
     * @return  bool
     * @throws  img.ImagingException
     */
    function convert(&$image) { 
      if (!imageistruecolor($image->handle)) return FALSE;

      return imagetruecolortopalette(
        $image->handle, 
        $this->dither, 
        $this->ncolors
      );
    }

  } implements(__FILE__, 'img.convert.ImageConverter');
?>
