<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('img.io.StreamWriter');

  /**
   * Writes JPEG to a stream
   *
   * @ext      gd
   * @see      php://imagejpeg
   * @see      xp://img.io.StreamWriter
   * @purpose  Writer
   */
  class JpegStreamWriter extends StreamWriter {
    var
      $quality  = 0;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   &io.Stream stream
     * @param   int quality default 75
     */
    function __construct(&$stream, $quality= 75) {
      parent::__construct($stream);
      $this->quality= $quality;
    }

    /**
     * Output an image
     *
     * @access  protected
     * @param   resource handle
     * @return  bool
     */    
    function output($handle) {
      return imagejpeg($handle, '', $this->quality);
    }
  }
?>
