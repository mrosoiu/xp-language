<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Base class for
   *
   * @access public
   */
  class XML extends Object {
    const
      ENCODING_DEFAULT = 'iso-8859-1',
      DECLARATION = '<?xml version="1.0" encoding="'.ENCODING_DEFAULT.'" ?>';

    public 
      $version   = '1.0',
      $_encoding = 'iso-8859-1';
    
    /**
     * Set encoding
     *
     * @access  public
     * @param   string e encoding
     */
    public function setEncoding($e) {
      $this->_encoding= $e;
    }
    
    /**
     * Retrieve encoding
     *
     * @access  public
     * @return  string encoding
     */
    public function getEncoding() {
      return $this->_encoding;
    }
    
    /**
     * Returns XML declaration
     *
     * @access  public
     * @return  string declaration
     */
    public function getDeclaration() {
      return sprintf(
        '<?xml version="%s" encoding="%s"?>',
        $this->version,
        self::getEncoding()
      );
    }
  }
?>
