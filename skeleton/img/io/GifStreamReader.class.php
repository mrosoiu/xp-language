<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('img.io.StreamReader');

  /**
   * Reads GIF from an image
   *
   * @ext      gd
   * @see      php://imagecreatefromgif
   * @see      xp://img.io.StreamReader
   * @purpose  Reader
   */
  class GifStreamReader extends StreamReader {

    /**
     * Read an image
     *
     * @access  protected
     * @return  resource
     * @throws  img.ImagingException
     */    
    function readFromStream() {
      return imagecreatefromgif($this->stream->getURI());
    }
  }
?>
