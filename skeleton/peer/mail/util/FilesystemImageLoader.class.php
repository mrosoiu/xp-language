<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('io.File', 'io.FileUtil', 'util.MimeType');

  /**
   * Loads images from the filesystem
   *
   * @purpose  ImageLoader
   */
  class FilesystemImageLoader extends Object {

    /**
     * Load an image
     *
     * @access  public
     * @param   &peer.URL source
     * @return  string[2] data and contenttype
     */
    function load(&$source) { 
      return array(
        FileUtil::getContents(new File($source->getURL())),
        MimeType::getByFilename($source->getURL())
      );
    }
  
  } implements(__FILE__, 'peer.mail.util.ImageLoader');
?>
