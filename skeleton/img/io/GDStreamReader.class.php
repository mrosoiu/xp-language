<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('img.io.StreamReader');

  /**
   * Reads GD from an image
   *
   * @ext      gd
   * @see      php://imagecreatefromgd
   * @see      xp://img.io.StreamReader
   * @purpose  Reader
   */
  class GDStreamReader extends StreamReader {

    /**
     * Read an image
     *
     * @access  protected
     * @return  resource
     * @throws  img.ImagingException
     */    
    function readFromStream() {
      return imagecreatefromgd($this->stream->getURI());
    }
  }
?>
