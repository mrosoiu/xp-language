<?php
/* Diese Klasse ist Teil des XP-Frameworks
 *
 * $Id$
 */

  uses('net.mail.MimePart');

  class MimeAlternative extends MimePart {
    var
      $parts;
      
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct() {
      parent::__construct();

      // Trenner
      $this->boundary= '----=_Alternative_'.md5(uniqid(time())).'@'.getenv('HOSTNAME');
    }

    /**
     * Callback-Funktion, die von MimeMail aufgerufen wird. Die Mail muss dann nicht
     * mit multipart/mixed, sondern mit multipart/related versehen werden
     *
     * @access  private
     * @param   
     * @return  
     */
    function _pCallModifyMail(&$mail) {
      $mail->header[HEADER_CONTENTTYPE]= (
        "multipart/related;\n\t".
        "type=\"multipart/alternative\";\n\t".
        "boundary=\"".$mail->boundary.'"'
      );
    }
    
    /**
     * Einen Part hinzuf�gen
     *
     * @access  public
     * @param   net.mail.MimePart part Ein MIME-Part
     */
    function addPart(&$part) {
      $this->parts[]= &$part;
    }
    
    /**
     * Die Header eines Parts zur�ckgeben
     *
     * @access  public
     * @return  string Part-Header
     */ 
    function getHeader() {
      return sprintf(
        "Content-Type: multipart/alternative;\n\tboundary=\"%s\"\n\n",
        $this->boundary
      );
    }
    
    /**
     * Den Inhalt eines Parts zur�ckgeben
     *
     * @access  public
     * @param   bool encode default FALSE Encoding vornehmen (abh�ngig vom Member "encoding")
     * @return  string Inhalt
     */
    function getContent() {
      $body= '';
      for ($i= 0; $i< sizeof($this->parts); $i++) {
        $body.= (
          '--'.$this->boundary."\n".
          $this->parts[$i]->getPart()
        );
      }
      return (
        $body.
        '--'.$this->boundary."--\n"
      );
    }
  }
?>
