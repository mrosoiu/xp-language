<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('org.cvshome.CVSInterface');

  /**
   * CVS directory
   *
   * @purpose  Interface to CVS binary
   */
  class CVSDirectory extends CVSInterface {
    var
      $path     = NULL,
      $_folder  = NULL;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string path
     */
    function __construct($path) {
      $this->path= $path;
      $this->_folder= &new Folder ($path);
    }
    
    /**
     * Protected helper method that executes a CVS command, changing
     * to the desired directory before doing so and changing it back
     * after finishing.
     *
     * @access  protected
     * @param   string cvsCmd
     * @return  string[]
     */
    function _execute($cvsCmd) {
      $olddir= getcwd(); 
      chdir($this->path);
      $r= parent::_execute ($cvsCmd);
      chdir($olddir);
      
      return $r;
    }
    
    /**
     * Update a directory
     *
     * @access  public
     * @param   bool recursive default FALSE
     * @param   bool sim default FALSE whether to simulate
     * @return  stdclass[] objects
     * @throws  org.cvshome.CVSInterfaceException
     */
    function update($recursive= FALSE, $sim= TRUE) {
      try(); {
        $results= $this->_execute (sprintf ('%s update %s',
          ($sim ? '-nq' : ''),
          ($recursive ? '' : '-l')
        ));
      } if (catch('CVSInterfaceException', $e)) {
        return throw ($e);
      }
      
      $stats= array();
      foreach ($results as $r) {
        if (strstr ($r, 'cvs server: warning:')) {
          // File has been removed, find out filename
          if (preg_match ('/(\S+) is not not \(any longer\) pertinent$/', $r, $match))
            $stats[$match[1]]= CVS_REMOVED;
            
          continue;
        }
    
        list ($state, $filename)= explode (' ', $r, 2);
        if (FALSE !== ($s= $this->getCVSStatus ($state))) {
          $f= &new stdClass();
          $f->status= $s;
          $f->filename= $filename;
          $f->uri= $this->path.DIRECTORY_SEPARATOR.$filename;
          $stats[]= $f;
        } else {
          // We could not identify this status, so ignore this file
          // TBI: Should we throw an excehption?
        }
      }
      
      return $stats;
    } 
  }  
?>
