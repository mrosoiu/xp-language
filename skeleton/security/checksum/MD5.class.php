<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('security.checksum.Checksum');
  
  /**
   * MD5 checksum
   *
   * @see      xp://security.checksum.Checksum
   * @see      php://md5
   * @purpose  Provide an API to check MD5 checksums
   */
  class MD5 extends Checksum {
  
    /**
     * Create a new checksum from a string
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  &security.checksum.MD5
     */
    function &fromString($str) {
      return new MD5(md5($str));
    }

    /**
     * Create a new checksum from a file object
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @return  &security.checksum.MD5
     */
    function &fromFile(&$file) {
      return new MD5(md5_file($file->uri));
    }
  }
?>
