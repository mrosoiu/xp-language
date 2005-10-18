<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('util.log.LogAppender');

  /**
   * LogAppender which appends data to a file
   *
   * @see      xp://util.log.LogAppender
   * @purpose  Appender
   */  
  class FileAppender extends LogAppender {
    var 
      $filename = '',
      $perms    = NULL;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string filename default 'php://stderr' filename to log to
     */
    function __construct($filename= 'php://stderr') {
      $this->filename= $filename;
    }
    
    /**
     * Appends log data to the file
     *
     * @access  public
     * @param   mixed* args variables
     */
    function append() {
      $fd= fopen($this->filename, 'a');

      if ($this->perms) {
        chmod($this->filename, octdec($this->perms));
        $this->perms= NULL;
      }
      
      with ($args= func_get_args()); {
        foreach ($args as $idx => $arg) {
          fputs($fd, $this->varSource($arg). ($idx < sizeof($args)-1 ? ' ' : ''));
        }
      }

      fputs($fd, "\n");
      fclose($fd);
    }
  }
?>
