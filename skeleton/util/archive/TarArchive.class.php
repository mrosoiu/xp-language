<?php
/*
 * $Id$
 *
 * Diese Klasse ist Bestandteil des XP-Frameworks
 * (c) 2001 Timm Friebe, Schlund+Partner AG
 *
 * @see http://doku.elite.schlund.de/projekte/xp/skeleton/
 *
 */
 
  uses('util.archive.TarArchiveEntry');

  /**
   * Kapselt ein Tar-Archiv
   *
   * @see http://www.gnu.org/software/tar/tar.html
   */
  class TarArchive extends Object {
    var
      $file;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   io.File file File-Objekt
     */  
    function __construct($file) {
      $this->file= $file;
      parent::__construct();
    }
    
    /**
     * �ffnen
     *
     * @access  public
     * @param   mixed args Argumente f�r die open()-Method des Datei-Objekts
     * @return  bool Das Ergebnis der open()-Method des Datei-Objekts
     */
    function open() {
      $args= func_get_args();
      return call_user_func_array(
        array(&$this->file, 'open'), 
        $args
      );
    }
    
    /**
     * Schlie�en
     *
     * @access  public
     * @return  bool Das Ergebnis der close()-Method des Datei-Objekts
     */
    function close() {
      return $this->file->close();
    }
    
    /**
     * Holt sich den n�chsten Eintrag aus dem Archiv
     *
     * @access  public
     * @return  io.TarArchiveEntry Eintrag 
     */
    function getEntry() {
      static $size= 0;
      
      // Am EOF nicht mehr weiterlesen
      if ($this->file->eof()) return FALSE;

      // Zur n�chsten Datei vorw�rts lesen
      $this->file->seek($this->file->tell()+ ceil($size / 512) * 512);
      
      // Header lesen
      $bin= $this->file->read(512);
      $data= unpack(
        'a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', 
        $bin
      );
      if ('' == trim($data['filename'])) return FALSE;
      
      // TarArchiveEntry-Objekt erzeugen
      $f= new TarArchiveEntry(
        $data, 
        $this->file->tell()
      );
      
      // Gr��e merken, damit beim n�chsten Aufruf dieser Funktion automatisch geseek()ed wird
      $size= $f->size;
      
      return $f;
    }
    
    /**
     * Inhalt einer Datei zur�ckgeben
     *
     * @access  public
     * @param   io.TarArchiveEntry e TarArchvieEntry-Objekt
     * @return  string content
     */
    function getEntryData($e) {
      $this->file->seek($e->offset);
      $content= $this->file->read($e->size);
      $this->file->seek($e->offset);
      return $content;
    }
  }
?>
