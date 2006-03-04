<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'com.flickr.api.FlickrPackage',
    'com.flickr.FlickrPhotoCollection'
  );

  /**
   * Flickr Interestingness Package
   *
   * @purpose  Interestingess Api
   */
  class FlickrInterestingness extends FlickrPackage {
  
    /**
     * Fetch list of interesting photos
     *
     * @access  public
     * @param   util.Date date default NULL
     * @param   string extras default NULL
     * @param   int perPage default 100
     * @param   int page default 1
     * @return  &com.flickr.FlickrPhotoCollection
     */
    function &getList($date= NULL, $extras= NULL, $perPage= 100, $page= 1) {
      $arguments= array(
        'per_page'  => $perPage,
        'page'      => $page
      );
      $date && $arguments['date']= $date->toString('Y-m-d');
      $extras || $extras= 'license,date_upload,date_taken,owner_name,icon_server,original_format,last_update';
      $arguments['extras']= $extras;
      
      return $this->client->invokeExpecting(
        'flickr.interestingness.getList', 
        $arguments,
        'com.flickr.FlickrPhotoCollection'
      );
    }
  }
?>
