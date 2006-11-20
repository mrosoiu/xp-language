<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  // Constants for SVN
  define('SVN_ADDED',    0x0001);
  define('SVN_UNKNOWN',  0x0002);
  define('SVN_PATCHED',  0x0003);
  define('SVN_UPDATED',  0x0004);
  define('SVN_REMOVED',  0x0005);
  define('SVN_MODIFIED', 0x0006);
  define('SVN_CONFLICT', 0x0007);
  define('SVN_UPTODATE', 0x0008);
  define('SVN_BROKEN',   0x0009);

  uses (
    'lang.System',
    'util.log.Logger',
    'org.tigris.subversion.SVNInterfaceException'
  );
  
  /**
   * Wraps SVN commands
   *
   * @purpose  Base class
   */
  class SVNInterface extends Object {
    
    var
      $_SVN= 'svn';
  
    /**
     * Execute a SVN command
     *
     * @access  private
     * @param   int svnCmd Command to execute
     * @return  array output
     * @throws  org.tigris.subversion.SVNInterfaceException if svn fails
     * @see     http://www.cvshome.org/docs/manual/cvs_16.html#SEC115
     */
    function _execute($svnCmd, $params= '') {
      $cmdLine= $this->_SVN.' '.$svnCmd;
      foreach ($params as $param) $cmdLine.='  '.$param;
      try(); {
        $l= &Logger::getInstance();
        $c= &$l->getCategory();
        $c->debug('SVN execute:', $cmdLine);
        $output= System::exec($cmdLine, '2>&1', FALSE);
      } if (catch('SystemException', $e)) {
        return throw (new SVNInterfaceException ('SVN returned failure ['.$cmdLine.']'));
      }
      
      return $output;
    }

    /**
     * Returns file or status flags contained in passed string. Returns
     * an array containing
     * - file status (updated, deleted, ...)
     * - property status (updated, deleted, ...)
     * - lock status (broken)
     * - the filename
     *
     * @access public
     * @param string line The line to parse
     * @return mixed[]
     */
    function getStatusFromString($line) {
      if (!preg_match('/^([UADCG]+)([ UADCG]+)([ B]+)  (.*)$/', $r, $regs)) return FALSE;
      $regs[1]= $this->getStatus($regs[1]);
      $regs[2]= $this->getStatus($regs[2]);
      $regs[3]= $this->getStatus($regs[3]);
      return $regs;
    }

    /**
     * Returns one of the SVN_* status constants, indicated by passed
     * char.
     *
     * @access public
     * @param string char The status character
     * @return int
     */
    function getStatus($char) {
      switch ($regs[1]) {
        case 'U': return SVN_UPDATED;
        case 'A': return SVN_ADDDED;
        case 'D': return SVN_REMOVED;
        case 'C': return SVN_CONFLICT;
        case 'G': return SVN_MERGED;
        case 'B': return SVN_BROKEN;
        default:
          return SVN_UNKNOWN;
      }
    }
  }

?>
