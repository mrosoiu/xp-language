<?php
/* Diese Klasse ist Teil des XP-Frameworks
 *
 * $Id$
 */
 
  // Common Header
  define('HEADER_FROM',         'From');
  define('HEADER_TO',           'To');
  define('HEADER_CC',           'Cc');
  define('HEADER_BCC',          'Bcc');
  define('HEADER_SUBJECT',      'Subject');
  define('HEADER_PRIORITY',     'X-Priority');
  define('HEADER_ENCODING',     'Content-Transfer-Encoding');
  define('HEADER_CONTENTTYPE',  'Content-Type');
  
  // Priorities
  define('MAIL_PRIORITY_LOW',           0x0005);
  define('MAIL_PRIORITY_NORMAL',        0x0003);
  define('MAIL_PRIORITY_HIGH',          0x0001);
  
  // F�r die Funktion getHeaders()
  define('HEADER_ALL',        0x0000);  // Alle
  define('HEADER_COMPAT',     0x0001);  // Mail-Header, die bei der Mail-Funktion sonst doppelt auftreten, wegschmei�en:)
  
  /**
   * Repr�sentiert eine "Basic"-Email
   *
   * @see http://www.faqs.org/rfcs/rfc822.html
   */
  class Mail extends Object {
    var 
      $header           = array(),
      $body             = '';
      
    // Komfort-Member-Variablen, k�nnte man auch �ber Header abbilden
    // Sinnige Default-Werte setzen
    var
      $to               = array(),
      $from             = array(),
      $cc               = array(),
      $bcc              = array(),
      $subject          = '',
      $priority         = MAIL_PRIORITY_NORMAL,
      $contenttype      = 'text/plain; charset=iso-8859-1',
      $encoding         = '8bit';

    /**
     * Die Adressliste f�r die Darstellung als Header zur�ckgeben.
     * Beispiel
     * ============================================
     * To: Timm Friebe <friebe@schlund.de>,
     *          Alex Kiesel <kiesel@schlund.de>,
     *          Jens Strobel <strobel@schlund.de>
     * ============================================
     * => Weitere Zeilen fangen mit \n\t an
     *
     * @access  public
     * @param   array list Liste von Mailadressen
     * @return  string Entsprechend obigem Beispiel formatierter Header-Wert
     */    
    function getAddressList(&$list) {
      return implode(",\n\t", $list);
    }

    /**
     * Sonderzeichen in Headern m�ssen encoded werden
     *
     * @access  private
     * @param   string str Der Name  
     * @return  string Der kodierte Name
     */
    function _encodeHeader($str) {
      return (empty($str)
        ? ''
        : '=?iso-8859-1?q?'.str_replace(' ', '_', imap_8bit($str)).'?='
      );
    }
    
    /**
     * Eine Adresse einem der Felder From, To, Cc oder Bcc hinzuf�gen
     *
     * @access  private
     * @param   &array what Referenz auf eine Member-Variable bspw. $this->from
     * @param   string mail Die E-Mail-Adresse
     * @param   string name default '' Der Name, bspw. "Timm Friebe"
     */    
    function _addAddress(&$what, $mail, $name= '') {
      $what[]= trim(sprintf(
        '%s <%s>', 
        $this->_encodeHeader($name),
        $mail
      ));
    }

    /**
     * Eine Adresse dem Feld "From" hinzuf�gen
     *
     * @access  public
     * @param   string mail Die E-Mail-Adresse
     * @param   string name default '' Der Name, bspw. "Timm Friebe"
     */
    function addFrom($mail, $name= '') {
      $this->_addAddress($this->from, $mail, $name);
    }

    /**
     * Eine Adresse dem Feld "To" hinzuf�gen
     *
     * @access  public
     * @param   string mail Die E-Mail-Adresse
     * @param   string name default '' Der Name, bspw. "Timm Friebe"
     */
    function addTo($mail, $name= '') {
      $this->_addAddress($this->to, $mail, $name);
    }

    /**
     * Eine Adresse dem Feld "Cc" hinzuf�gen
     *
     * @access  public
     * @param   string mail Die E-Mail-Adresse
     * @param   string name default '' Der Name, bspw. "Timm Friebe"
     */
    function addCc($mail, $name= '') {
      $this->_addAddress($this->cc, $mail, $name);
    }

    /**
     * Eine Adresse dem Feld "Bcc" hinzuf�gen
     *
     * @access  public
     * @param   string mail Die E-Mail-Adresse
     * @param   string name default '' Der Name, bspw. "Timm Friebe"
     */
    function addBcc($mail, $name= '') {
      $this->_addAddress($this->bcc, $mail, $name);
    }
    
    /**
     * Einen Header hinzuf�gen, bspw: $mail->addHeader('X-Binford', '6100 (more power)');
     * Daraus wird dann X-Binford: 6100 (more power)
     *
     * @access  public
     * @param   string header Der Header
     * @param   string value Der Header-Wert
     * @return  bool TRUE := Der Header wurde gesetzt. Manche speziellen Header 
     *          (From, To, Cc, Bcc) k�nnen nicht �ber diese Funktion gesetzt werden
     */
    function addHeader($header, $value) {
      if (
        HEADER_FROM == $header or
        HEADER_TO   == $header or
        HEADER_CC   == $header or
        HEADER_BCC  == $header
      ) return FALSE;
      
      $this->header[$header]= $value;
      return TRUE;
    }

    /**
     * Gibt den Body-Part einer Mail zur�ck
     *
     * @access  public
     * @return  string Kompletter Body
     */
    function getBody() {
      return $this->body;
    }
    
    /**
     * Gibt einen bestimmten Header zur�ck
     *
     * @access  public
     * @param   string name Header-Name
     * @return  string Header-Wert oder NULL, wenn dieser Header nicht existiert
     */
    function getHeader($name) {
      return isset($this->header[$name]) ? $this->header[$name] : NULL;
    }
    
    /**
     * Private Helper-Funktion, �berschreibt Header, falls sie nicht gesetzt sind,
     * mit den entsprechenden "Komfort-Member-Variablen"
     *
     * @access  private
     * @param   string key Header-Name
     * @param   string value Header-Wert
     * @return  
     */
    function _setHeaderIfEmpty($key, $value) {
      if (empty($this->header[$key])) $this->header[$key]= $value;
    }
    
    /**
     * Gibt die Header als String zur�ck. Die PHP-Funktion mail() kommt durcheinander,
     * wenn man To: und Subject: im vierten Parameter mit�bergibt und schreibt diese
     * dann doppelt in die Mail. Daher k�nnen diese mit dem Modus HEADER_COMPAT 
     * "unterdr�ckt" werden
     *
     * @access  public
     * @param   int mode default HEADER_ALL R�ckgabe-Modus: Alle Header oder PHP-mail() kompatibel
     * @return  string String-Repr�sentation der Header
     */
    function getHeaders($mode= HEADER_ALL) {
      $header= '';
      
      // Spezial-Header
      $this->header[HEADER_BCC]=  $this->getAddressList($this->bcc);
      $this->header[HEADER_CC]=   $this->getAddressList($this->cc);
      $this->header[HEADER_TO]=   $this->getAddressList($this->to);
      $this->header[HEADER_FROM]= $this->getAddressList($this->from);

      // Wenn nicht �ber addHeader() gesetzt, "Komfort-Member" benutzen
      $this->_setHeaderIfEmpty(HEADER_PRIORITY, $this->priority);
      $this->_setHeaderIfEmpty(HEADER_SUBJECT, $this->subject);
      $this->_setHeaderIfEmpty(HEADER_ENCODING, $this->encoding);
      $this->_setHeaderIfEmpty(HEADER_CONTENTTYPE, $this->contenttype);
      
      // Kompatibilit�tsmodus f�r die mail()-Funktion, da dies sonst doppelt vorkommt:)
      if ($mode== HEADER_COMPAT) {
        unset($this->header[HEADER_TO]);
        unset($this->header[HEADER_SUBJECT]);
      }
      
      // Userdefinierte Header nach unten
      $this->header= array_reverse($this->header);

      // Header-Source (leere Header discarden)
      foreach ($this->header as $key=> $val) {
        if (empty($val)) continue;
        $header.= sprintf("%s: %s\n", $key, $val);
      }
      return $header;
    }
    
    /**
     * Den Betreff der Mail zur�ckgeben
     *
     * @access  public
     * @param   int encode default TRUE soll das Encoding vorgenommen werden
     * @return  string String-Repr�sentation des Subjects
     */
    function getSubject($encode= TRUE) {
      return ($encode
        ? $this->_encodeHeader($this->subject)
        : $this->subject
      );
    }
    
    /**
     * Die Komplette Mail als String zur�ckgebeb
     *
     * @access  public
     * @param   int mode default HEADER_ALL
     * @see     net.mail.Mail#getHeader
     * @return  string String-Repr�sentation der kompletten Mail
     */
    function getMail($mode= HEADER_ALL) {
      return (
        $this->getHeaders($mode).
        "\n".
        $this->getBody()
      );
    }
  } 
?>
