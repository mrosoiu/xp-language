<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('img.io.StreamReader');

  /**
   * Reads GD2 from an image
   *
   * @ext      gd
   * @see      php://imagecreatefromgd2
   * @see      xp://img.io.StreamReader
   * @purpose  Reader
   */
  class GD2StreamReader extends StreamReader {

    /**
     * Read an image
     *
     * @access  protected
     * @return  resource
     * @throws  img.ImagingException
     */    
    function readFromStream() {
      return imagecreatefromgd2($this->stream->getURI());
    }
  }
?>
