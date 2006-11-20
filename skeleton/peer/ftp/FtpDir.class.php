<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.ftp.FtpEntry');

  /**
   * FTP directory
   *
   * @see      xp://peer.ftp.FtpConnection
   * @purpose  Represent an FTP directory
   */
  class FtpDir extends FtpEntry {
    var
      $entries  = NULL,
      $_offset  = 0;

    /**
     * Check if directory exists
     *
     * @access  public
     * @return  bool
     */
    function exists() {
      return ftp_size($this->connection->handle, $this->name) != -1;
    }

    /**
     * Get entries (iterative function)
     *
     * @access  public
     * @return  &peer.ftp.FtpEntry FALSE to indicate EOL
     */
    function &getEntry() {
      if (NULL === $this->entries) {

        // Retrieve entries
        if (FALSE === ($list= ftp_rawlist($this->connection->handle, $this->name))) {
          throw(new SocketException('Cannot list '.$this->name));
          return FALSE;
        }
        
        $this->entries= $list;
        $this->_offset= 0;
        if (empty($this->entries)) return FALSE;
      } else if (0 == $this->_offset) {
        $this->entries= NULL;
        return FALSE;
      }

      // Get rid of directory self-reference "." and parent directory 
      // reference, ".."
      do {        
        try(); {
          $entry= &$this->connection->parser->entryFrom($this->entries[$this->_offset]);
        } if (catch('Exception', $e)) {
          throw(new SocketException(sprintf(
            'During listing of #%d (%s): %s',
            $this->_offset,
            $this->entries[$this->_offset],
            $e->getMessage()
          )));
          return FALSE;
        }
        
        // If we reach max, reset offset to 0 and break out of this loop
        if (++$this->_offset >= sizeof($this->entries)) {
          $this->_offset= 0;
          break;
        }
      } while ('.' == $entry->getName() || '..' == $entry->getName());
      
      // Inject connection and return
      $entry->connection= &$this->connection;
      return $entry;
    }
  }
?>
