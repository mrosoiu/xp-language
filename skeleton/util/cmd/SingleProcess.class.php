<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('io.File');

  /**
   * SingleProcess provides a way to insure a process is only running
   * once at a time.
   * 
   * Usage:
   * <code>
   *   $sp= &new SingleProcess();
   *   if (!$sp->lock()) {
   *     exit(-1);
   *   }
   *
   *   // [...operation which should only take part once at a time...]
   *
   *   $sp->unlock();
   * </code>
   *
   * @purpose  Lock process so it can only be run once
   */  
  class SingleProcess extends Object {
    var 
      $lockfile     = NULL;

    /**
     * Constructor
     *
     * @access  public
     * @param   string lockfileName default NULL the lockfile's name,
     *          defaulting to <<program_name>>.lck
     */
    function __construct($lockFileName= NULL) {
      if (NULL === $lockFileName) $lockFileName= $_SERVER['argv'][0].'.lck';
      $this->lockfile= &new File($lockFileName);
    }
    
    /**
     * Lock this application
     *
     * @access  public
     * @return  bool success
     */
    function lock() {
      try(); {
        $this->lockfile->open(FILE_MODE_WRITE);
        $this->lockfile->lockExclusive();
      } if (catch('IOException', $e)) {
        $this->lockfile->close();
        return FALSE;
      }
      
      return TRUE;
    }
    
    /**
     * Unlock the application
     *
     * @access  public
     * @return  bool Success
     */
    function unlock() {
      if ($this->lockfile->unlock()) {
        $this->lockfile->close();
        $this->lockfile->unlink();
        return TRUE;
      }
      
      return FALSE;
    }
  }
?>
