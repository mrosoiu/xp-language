<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  // Constants for CVS
  define ('CVS_ADDED',    0x0001);
  define ('CVS_UNKNOWN',  0x0002);
  define ('CVS_PATCHED',  0x0003);
  define ('CVS_UPDATED',  0x0004);
  define ('CVS_REMOVED',  0x0005);
  define ('CVS_MODIFIED', 0x0006);
  define ('CVS_CONFLICT', 0x0007);
  define ('CVS_UPTODATE', 0x0008);

  uses (
    'org.cvshome.CVSInterfaceException',
  );
  
  class CVSInterface extends Object {
    var
      $cvsRoot= NULL;
    
    var
      $_CVS= 'cvs';
  
    /**
     * Execute a CVS command
     *
     * @access private
     * @param int cvsCmd Command to execute
     * @return array output
     * @throws CVSInterfaceException, if cvs fails
     * @see http://www.cvshome.org/docs/manual/cvs_16.html#SEC115
     */
    function _execute($cvsCmd) {
      $cmdLine= sprintf ("%s %s %s %s 2>&1",
        $this->_CVS,
        (NULL !== $this->cvsRoot ? '-d'.$this->cvsRoot : ''),
        $cvsCmd,
        basename ($this->filename)
      );
      
      // CVS sometimes needs to have the current directory
      // to be the same, the file is in:
      $oldDir= getcwd();
      chdir (dirname ($this->filename));      
      exec ($cmdLine, $output, $returnCode);
      chdir ($oldDir);
      
      if (0 !== $returnCode && 'diff' != substr ($cvsCmd, 0, 4)) {
        return throw (new CVSInterfaceException ('CVS returned failure'));
      }
      
      if (count ($output) && strstr ($output[0], 'Cannot access'))
        return throw (
          new CVSInterfaceException ('Cannot access CVSROOT!')
        );

      return $output;
    }

    /**
     * Set CVS-Root and Login
     * Login must be without "-d"
     * E.g: setCVSRoot ('/home/cvs/', ':ext:alex@php3.de')
     *
     * @access public
     * @param string cvsroot
     * @param string login
     */
    function setCVSRoot($cvsRoot, $login= '') {
      $this->cvsRoot= sprintf ("%s%s%s",
        !empty ($login) ? '-d' : '',
        !empty ($login) ? $login.':' : '',
        $cvsRoot
      );
    }

    /**
     * Returns the internal statuscode from the cvs status string
     *
     * @access public
     * @param string statusString
     * @return int statusCode
     * @throws CVSInterfaceException
     */
    function getCVSStatusFromString($statusCode) {
      switch ($statusCode) {
        case 'Up-to-date': return CVS_UPTODATE;
        case 'Added': return CVS_ADDED;
        case 'Locally Modified': return CVS_MODIFIED;
        case 'Unknown': return CVS_UNKNOWN;
        case 'Needs Checkout': return CVS_UPDATED;
        case 'Conflict': return CVS_CONFLICT;
        default: break;
      }
      
      return throw (new CVSInterfaceException ('Unknown statusstring '.$statusCode));
    }

    /**
     * Returns the internal statuscode from the cvs status code
     *
     * @access public
     * @param char statusCode
     * @return int statusCode
     * @throws CVSInterfaceException
     */
    function getCVSStatus($statusCode) {
      switch ($statusCode) {
        case '?': return CVS_UNKNOWN;
        case 'P': return CVS_PATCHED;
        case 'U': return CVS_UPDATED;
        case 'M': return CVS_MODIFIED;
        case 'C': return CVS_CONFLICT;
        case 'A': return CVS_ADDED;
        default: break;
      }
      
      return throw (new CVSInterfaceException ('Unknown statuscode '.$statusCode));
    }
    
  }

?>
