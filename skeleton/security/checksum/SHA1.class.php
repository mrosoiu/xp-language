<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses('security.checksum.Checksum');
  
  /**
   * SHA1 checksum
   *
   * @see      xp://security.checksum.Checksum
   * @see      php://SHA1
   * @purpose  Provide an API to check SHA1 checksums
   */
  class SHA1 extends Checksum {
  
    /**
     * Create a new checksum from a string
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  &security.checksum.SHA1
     */
    function &fromString($str) {
      return new SHA1(sha1($str));
    }

    /**
     * Create a new checksum from a file object
     *
     * @model   static
     * @access  public
     * @param   &io.File file
     * @return  &security.checksum.SHA1
     */
    function &fromFile(&$file) {
      return new SHA1(sha1_file($file->uri));
    }
  }
?>
