<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('img.ImagingException');

  /**
   * Image information
   *
   * @see      php://getimagesize
   * @purpose  Utility
   */
  class ImageInfo extends Object {
    var
      $width      = 0,
      $height     = 0,
      $type       = 0,
      $mime       = '',
      $bits       = NULL,
      $channels   = NULL,
      $segments   = array();

    /**
     * Retrieve an ImageInfo object from a file
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @return  &img.util.ImageInfo
     * @throws  img.ImagingException in case extracting information from image file fails
     */
    function &fromFile(&$file) {
      if (FALSE === ($data= getimagesize($file->getURI(), $segments))) {
        return throw(new ImagingException(
          'Cannot load image information from '.$file->getURI()
        ));
      }
      
      with ($i= &new ImageInfo()); {
        $i->width= $data[0];
        $i->height= $data[1];
        $i->type= $data[2];
        $i->mime= image_type_to_mime_type($data[2]);
        isset($data['bits']) && $i->bits= $data['bits'];
        isset($data['channels']) && $i->channels= $data['channels'];
        $i->segments= $segments;
      }
      return $i;
    }
    
    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        "%s(%d x %d %s)@{\n".
        "  [type       ] %d\n".
        "  [channels   ] %s\n".
        "  [bits       ] %s\n".
        "  [segments   ] %s\n".
        "}",
        $this->getClassName(),
        $this->width,
        $this->height,
        $this->mime,
        $this->type,
        NULL === $this->channels ? '(unknown)' : $this->channels,
        NULL === $this->bits ? '(unknown)' : $this->bits,
        implode(', ', array_keys($this->segments))
      );
    }

    /**
     * Set Width
     *
     * @access  public
     * @param   int width
     */
    function setWidth($width) {
      $this->width= $width;
    }

    /**
     * Get Width
     *
     * @access  public
     * @return  int
     */
    function getWidth() {
      return $this->width;
    }

    /**
     * Set Height
     *
     * @access  public
     * @param   int height
     */
    function setHeight($height) {
      $this->height= $height;
    }

    /**
     * Get Height
     *
     * @access  public
     * @return  int
     */
    function getHeight() {
      return $this->height;
    }

    /**
     * Set Type
     *
     * @access  public
     * @param   int type
     */
    function setType($type) {
      $this->type= $type;
    }

    /**
     * Get Type
     *
     * @access  public
     * @return  int
     */
    function getType() {
      return $this->type;
    }

    /**
     * Set Bits
     *
     * @access  public
     * @param   int bits
     */
    function setBits($bits) {
      $this->bits= $bits;
    }

    /**
     * Get Bits
     *
     * @access  public
     * @return  int
     */
    function getBits() {
      return $this->bits;
    }

    /**
     * Set Channels
     *
     * @access  public
     * @param   int channels
     */
    function setChannels($channels) {
      $this->channels= $channels;
    }

    /**
     * Get Channels
     *
     * @access  public
     * @return  int
     */
    function getChannels() {
      return $this->channels;
    }

    /**
     * Set Mime
     *
     * @access  public
     * @param   string mime
     */
    function setMime($mime) {
      $this->mime= $mime;
    }

    /**
     * Get Mime
     *
     * @access  public
     * @return  string
     */
    function getMime() {
      return $this->mime;
    }
    
    /**
     * Retrieve whether a specified segment is available
     *
     * @see     http://www.ozhiker.com/electronics/pjmt/jpeg_info/app_segments.html
     * @access  public
     * @param   string id the segment's name
     * @return  bool
     */    
    function hasSegment($id) {
      return isset($this->segments[$id]);
    }

    /**
     * Retrieve all segment names
     *
     * @access  public
     * @return  string[]
     */    
    function getSegmentNames() {
      return array_keys($this->segments);
    }

    /**
     * Retrieve segment data for a specified segment.
     *
     * @access  public
     * @param   string id the segment's name
     * @return  bool
     * @throws  img.ImagingException when the specified segment is not available
     */    
    function getSegment($id) {
      if (!isset($this->segments[$id])) return throw(new ImagingException(
        'Segment "'.$id.'" not available'
      ));

      return $this->segments[$id];
    }
  }
?>
