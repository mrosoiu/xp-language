<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.Date');

  /**
   * Reads the EXIF headers from JPEG or TIFF
   *
   * @ext      exif
   * @purpose  Utility
   */
  class ExifData extends Object {
    var
      $height       = 0,
      $width        = 0,
      $make         = '',
      $model        = '',
      $flash        = 0,
      $orientation  = 0,
      $mimeType     = '',
      $dateTime     = NULL;

    /**
     * Read from a file
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @return  &img.util.ExifData
     */
    function fromFile(&$file) {
      if (!($info= exif_read_data($file->getURI()))) {
        return throw(new ImagingException(
          'Cannot get EXIF information from '.$file->getURI()
        ));
      }
      
      with ($e= &new ExifData()); {
        $e->setWidth($info['COMPUTED']['Width']);
        $e->setHeight($info['COMPUTED']['Height']);
        $e->setMake($info['Make']);
        $e->setModel($info['Model']);
        $e->setFlash($info['Flash']);
        $e->setOrientation($info['Orientation']);
        $e->setMimeType($info['MimeType']);
        $t= sscanf($info['DateTime'], '%4d:%2d:%2d %2d:%2d:%2d');
        $e->setDateTime(new Date(mktime($t[3], $t[4], $t[5], $t[1], $t[2], $t[0])));
      }
      return $e;
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
     * Set Make
     *
     * @access  public
     * @param   string make
     */
    function setMake($make) {
      $this->make= $make;
    }

    /**
     * Get Make
     *
     * @access  public
     * @return  string
     */
    function getMake() {
      return $this->make;
    }

    /**
     * Set Model
     *
     * @access  public
     * @param   string model
     */
    function setModel($model) {
      $this->model= $model;
    }

    /**
     * Get Model
     *
     * @access  public
     * @return  string
     */
    function getModel() {
      return $this->model;
    }

    /**
     * Set Flash
     *
     * @access  public
     * @param   int flash
     */
    function setFlash($flash) {
      $this->flash= $flash;
    }

    /**
     * Get Flash
     *
     * @access  public
     * @return  int
     */
    function getFlash() {
      return $this->flash;
    }

    /**
     * Set Orientation
     *
     * @access  public
     * @param   int orientation
     */
    function setOrientation($orientation) {
      $this->orientation= $orientation;
    }

    /**
     * Get Orientation
     *
     * @access  public
     * @return  int
     */
    function getOrientation() {
      return $this->orientation;
    }

    /**
     * Set MimeType
     *
     * @access  public
     * @param   string mimeType
     */
    function setMimeType($mimeType) {
      $this->mimeType= $mimeType;
    }

    /**
     * Get MimeType
     *
     * @access  public
     * @return  string
     */
    function getMimeType() {
      return $this->mimeType;
    }

    /**
     * Set DateTime
     *
     * @access  public
     * @param   &util.Date dateTime
     */
    function setDateTime(&$dateTime) {
      $this->dateTime= &$dateTime;
    }

    /**
     * Get DateTime
     *
     * @access  public
     * @return  &util.Date
     */
    function &getDateTime() {
      return $this->dateTime;
    }
    
    /**
     * Retrieve whether the flash was used.
     *
     * @see     http://jalbum.net/forum/thread.jspa?forumID=4&threadID=830&messageID=4438
     * @access  public
     * @return  bool
     */
    function flashUsed() {
      return 1 == ($this->flash % 8);
    }
    
    /**
     * Returns whether picture is horizontal
     *
     * @see     http://sylvana.net/jpegcrop/exif_orientation.html
     * @access  public
     * @return  bool
     */
    function isHorizontal() {
      return $this->orientation <= 4;
    }

    /**
     * Returns whether picture is vertical
     *
     * @see     http://sylvana.net/jpegcrop/exif_orientation.html
     * @access  public
     * @return  bool
     */
    function isVertical() {
      return $this->orientation > 4;
    }
    
    /**
     * (Insert method's description here)
     *
     * The orientation of the camera relative to the scene, when the 
     * image was captured. The relation of the '0th row' and '0th column' 
     * to visual position is shown as below:
     *
     * <pre>
     *   +---------------------------------+-----------------+
     *   | value | 0th row    | 0th column | human readable  |
     *   +---------------------------------+-----------------+
     *   | 1     | top        | left side  | normal          |
     *   | 2     | top        | right side | flip horizontal |
     *   | 3     | bottom     | right side | rotate 180�     |
     *   | 4     | bottom     | left side  | flip vertical   |
     *   | 5     | left side  | top        | transpose       |
     *   | 6     | right side | top        | rotate 90�      |
     *   | 7     | right side | bottom     | transverse      |
     *   | 8     | left side  | bottom     | rotate 270�     |
     *   +---------------------------------+-----------------+
     *</pre>
     *
     * @access  public
     * @return  string
     */
    function getOrientationString() {
      static $string= array(
        1 => 'normal',
        2 => 'flip_horizonal',
        3 => 'rotate_180',
        4 => 'flip_vertical',
        5 => 'transpose',
        6 => 'rotate_90',
        7 => 'transverse',
        8 => 'rotate_270' 
      );
      return $string[$this->orientation];
    }
    
    /**
     * Get degree of rotation (one of 0, 90, 180 or 270)
     *
     * @see     http://sylvana.net/jpegcrop/exif_orientation.html
     * @access  public
     * @return  int
     */
    function getRotationDegree() {
      static $degree= array(
        3 => 180,   // flip
        6 => 90,    // clockwise
        8 => 270    // counterclockwise
      );
      return isset($degree[$this->orientation]) ? $degree[$this->orientation] : 0;
    }
    
    /**
     * Retrieve a string representation
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        "%s(%d x %d %s)@{\n".
        "  [make         ] %s\n".
        "  [model        ] %s\n".
        "  [flash        ] %d (%s)\n".
        "  [orientation  ] %s (%s, %s)\n".
        "  [dateTime     ] %s\n".
        "}",
        $this->getClassName(),
        $this->width,
        $this->height,
        $this->mimeType,
        $this->make,
        $this->model,
        $this->flash, 
        $this->flashUsed() ? 'on' : 'off',
        $this->orientation,
        $this->isHorizontal() ? 'horizontal' : 'vertical',
        $this->getOrientationString(),
        $this->dateTime->toString('r')
      );
    }
  }
?>
