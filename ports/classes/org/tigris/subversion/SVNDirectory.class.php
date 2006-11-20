<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'io.Folder',
    'org.tigris.subversion.SVNInterface'
  );

  /**
   * SVN directory
   *
   * @purpose  Interface to SVN binary
   */
  class SVNDirectory extends SVNInterface {
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
      $this->_folder= &new Folder($path);
    }
    
    /**
     * Update a directory
     *
     * @access  public
     * @param   bool recursive default FALSE
     * @return  stdclass[] objects
     * @throws  org.cvshome.CVSInterfaceException
     */
    function update($recursive= FALSE) {
      try(); {
        $results= $this->_execute(sprintf('update %s %s',
          ($recursive ? '' : '-N'),
          $this->path
        ));
      } if (catch('SVNInterfaceException', $e)) {
        return throw ($e);
      }
      
      $stats= array();
      foreach ($results as $r) {
        if ($regs= $this->getStatusFromString($r)) {
          $f= new stdClass;
          $f->status= $regs[1];
          $f->filename= basename($regs[4]);
          $f->uri= $this->path.DIRECTORY_SEPARATOR.$f->filename;
          $stats[]= $f;
        } else {
          // We could not identify this status, so ignore this file
          // TBI: Should we throw an excehption?
        }
      }
      
      return $stats;
    } 

    /**
     * Commit the file (needs write access to repository)
     *
     * @access  public
     * @param   string comment
     */
    function commit($comment) {
      $f= &new TempFile();
      try(); {
        $f->open(FILE_MODE_WRITE);
        $f->writeLine($comment);
        $f->close();
      } if (catch('IOException', $e)) {
        return throw($e);
      }

      $return= &$this->_execute(sprintf('commit -F %s %s', $f->getURI(), $this->path));
      
      $f->unlink();
      return $return;
    }
    
    /**
     * Removes a directory from the repository. To complete this action,
     * you have to call commit. Use this with caution.
     *
     * @access  public
     * @return  bool success
     */
    function delete() {
      return $this->_execute('delete '.$this->path);
    }

    /**
     * Adds directory to a repository. Please note, that commit is required.
     *
     * @access  public
     * @return  bool success
     */    
    function add() {
      return $this->_execute('add '.$this->path);
    }
  }  
?>
