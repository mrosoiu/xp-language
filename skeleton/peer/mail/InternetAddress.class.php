<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses('text.encode.QuotedPrintable', 'text.encode.Base64');
 
  /**
   * Internet address
   *
   * @see      http://www.cs.tut.fi/~jkorpela/rfc/822addr.html
   * @see      rfc://2822
   * @see      rfc://2822#3.4.1
   * @purpose  Wrap
   */
  class InternetAddress extends Object {
    var 
      $personal  = '',
      $localpart = '',
      $domain    = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   mixed mail
     * @param   string personal default ''
     */
    function __construct($mail, $personal= '') {
      list($this->localpart, $this->domain)= (is_array($mail) 
        ? $mail
        : explode('@', $mail)
      );
      $this->personal= $personal;
    }
    
    /**
     * Retrieve hashcode
     *
     * @access  public
     * @return  string
     */
    function hashCode() {
      return md5($this->localpart.'@'.$this->domain);
    }
    
    /**
     * Create an InternetAddress object from a string
     *
     * Recognizes:
     * <pre>
     *   Timm Friebe <friebe@example.com>
     *   friebe@example.com (Timm Friebe)
     *   "Timm Friebe" <friebe@example.com>
     *   friebe@example.com
     *   <friebe@example.com>
     *   =?iso-8859-1?Q?Timm_Friebe?= <friebe@example.com>
     * </pre>
     *
     * @model   static
     * @access  public
     * @param   string str
     * @return  &peer.mail.InternetAddress address object
     * @throws  FormatException in case the string could not be parsed into an address
     */
    function &fromString($str) {
      static $matches= array(
        '/^=\?([^\?])+\?([QB])\?([^\?]+)\?= <([^ @]+@[0-9a-z.-]+)>$/i' => 3,
        '/^<?([^ @]+@[0-9a-z.-]+)>?$/i'                                => 0,
        '/^([^<]+) <([^ @]+@[0-9a-z.-]+)>$/i'                          => 2,
        '/^"([^"]+)" <([^ @]+@[0-9a-z.-]+)>$/i'                        => 1,
        '/^([^ @]+@[0-9a-z.-]+) \(([^\)]+)\)$/i'                       => 1,
      );
      
      $str= trim(chop($str));
      foreach ($matches as $match => $def) {
        if (!preg_match($match, $str, $_)) continue;
        
        switch ($def) {
          case 0: $mail= $_[1]; $personal= ''; break;
          case 1: $mail= $_[1]; $personal= $_[2]; break;
          case 2: $mail= $_[2]; $personal= $_[1]; break;
          case 3: $mail= $_[4]; switch (strtoupper($_[2])) {
            case 'Q': $personal= QuotedPrintable::decode($_[3]); break;
            case 'B': $personal= Base64::decode($_[3]); break;
          } 
          break;
        }
        
        break;
      }
      
      // Was it unparsable?
      if (!isset($mail)) return throw(
        new FormatException('String "'.$str.'" could not be parsed')
      );
      
      return new InternetAddress($mail, $personal);
    }
    
    /**
     * Create string representation
     *
     * Return values:
     * <pre>
     * - personal specified: =?iso-8859-1?Q?Timm_Friebe?= <friebe@example.com>
     * - Empty personal:     <friebe@example.com>  
     * </pre>
     *
     * @access  public
     * @param   string charset default 'iso-8859-1'
     * @return  string
     */
    function toString($charset= 'iso-8859-1') {
      return (
        empty($this->personal) ? '' : 
        QuotedPrintable::encode($this->personal, $charset).' '
      ).'<'.$this->localpart.'@'.$this->domain.'>';
    }
  }
?>
