<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.parser.VFormatParser', 'util.Date');
  
  // Identifier
  define('VCARD_ID',             'VCARD');

  // Property params: TEL
  define('VCARD_TEL_TYPE_FAX',   'FAX');
  define('VCARD_TEL_TYPE_VOICE', 'VOICE');
  define('VCARD_TEL_LOC_WORK',   'WORK');
  define('VCARD_TEL_LOC_HOME',   'HOME');
  define('VCARD_TEL_LOC_CELL',   'CELL');
  
  // Property params: ADR
  define('VCARD_ADR_HOME',       'HOME');
  define('VCARD_ADR_WORK',       'WORK');
  define('VCARD_ADR_POSTAL',     'POSTAL');
  
  // Property params: EMAIL
  define('VCARD_EMAIL_DEFAULT',  'DEFAULT');
  define('VCARD_EMAIL_WORK',     'WORK');
  
  /**
   * VCard
   *
   * <quote>
   * vCard automates the exchange of personal information typically found on a 
   * traditional business card. vCard is used in applications such as Internet
   * mail, voice mail, Web browsers, telephony applications, call centers, 
   * video conferencing, PIMs (Personal Information Managers), PDAs (Personal 
   * Data Assistants), pagers, fax, office equipment, and smart cards. vCard 
   * information goes way beyond simple text, and includes elements like 
   * pictures, company logos, live Web addresses, and so on.
   * </quote>
   *
   * Example:
   * <pre>
   * BEGIN:VCARD
   * N:Internet Mail Consortium
   * FN:Internet Mail Consortium
   * ORG:Internet Mail Consortium;
   * EMAIL;INTERNET;WORK:phoffman@imc.org
   * TEL;WORK;VOICE:+1 831 426 9827
   * TEL;WORK;FAX:+1 831 426 7301
   * ADR;POSTAL:;;127 Segre Place;Santa Cruz;CA;95060;USA
   * LABEL;POSTAL;DOM;ENCODING=QUOTED-PRINTABLE:127 Segre Place=0D=0A=
   *      Santa Cruz, CA  95060=0D=0A                        USA
   * URL:http://www.imc.org/
   * TZ:-08:00
   * LOGO;GIF;ENCODING=BASE64:
   *     R0lGODlhogBNAPEAAP////+AgP8AAAAAACH5BAEAAAAALAAAAACiAE0AAAL/BISpy+1i
   *     opy02ouz3rzTB4aMR5bmiXriCqbuC8cVSy/yjeddzev+/+PRgMRiTLgyKpckZIgJjWKc
   *     D6n1eqCOsFyo1tYNG7+J0+B8FqszZEgJDV/LP18T/D7PG+qku18/p2XnFwcoRzVImGa4
   *     5mSmuMiohvQIKXlYk2J52TjkQsgZyAKDF4opYpp6kqTauvPkGrsBK1tr0WKbO+Og2yvB
   *     6xucBfYT+TZQobihHLOJspXz11coAWlMYY2smX2toiCTrRFeza1Nzl1ZfhKgEPBZbo6t
   *     bgB/XR+/fI/fEcCO0L8N3QV49PRF0LdvIEIT/fw1DDjOAkGEaSjmK3UOo4eG/+w4NmMm
   *     bt7BehMiKtRYktpGjiw/guIgMGXMjC8lSjuZUAPLnaRAhvQpzxrOm0FVniyxk+cLZz+J
   *     2gQqs2ZUo0+RJm35TmoGpk+dJoNa0OvXnBeuKoWIEgPXsWKnUgUAdiwJs2fTpR361i2a
   *     pnfjBl1J1yPavGzv6u1WtK/WG4HrHluMd+9WvyMXr43R2PE0yF0N0yRc2etlGJk1dxhd
   *     GHTYtp8lt0b8onRSu6pfkw3tGTdV1Chkz07EOnFt3qvTEmfo2zTf2rpdR4ZtW7hz0smV
   *     T+acenp26M27CZVR/beH491vFx8O9Tv18FjHUz5sHu57+ZbJc2AvHub88su1S//3bp9O
   *     +FnXGXPn+Qdff4ipl8KA+SkYH3/XBXfgggGW5SCB/0VYYXzHXWYSchm2B+Fp830YV4iA
   *     jSjYRRRGN2FuHRb4ogUsPvgch/S9iCJ2MyVDwY040shdgjGqxiB8iu0j5JBKRqiibdxF
   *     2aFIETTppEVSbqLllrtRxCSWpnVZJZcLPZnXmRKIOSSZO5L0JkFoIhjnhcLokeSd8ugJ
   *     Vy/m6AgFoBJt5YOgyxykRjyGvlEoEIs2Co42Ff3pmmSTyofpOat9lak9kXyq1qeWImMM
   *     NYVcGsciqkpKKqsljUQPogXNmimtrnYaqq2yghorp32KqmufsQJbq7C9GktpsMeL/imr
   *     scLiw6yzkqrVbELROlvNq9kie2y3z2q7bLXijsvted5GK81t11JaabPgnhtuWPBiqyi5
   *     625bbr74xmsttfzie+279MabLbTzEnxvtwkjiy64t3470LirkpsSObCWyyyxBku8MLv0
   *     0drpqB6HXKq1E5OM6V4InlrpxKa2DG2ryU56a5F8ykIACgA7
   * 
   * REV:19970726T000001
   * VERSION:2.1
   * END:VCARD
   * </pre>
   *
   * @see      rfc://2425
   * @see      rfc://2426
   * @see      http://www.imc.org/pdi/
   * @see      http://www.imc.org/pdi/pdiproddev.html
   * @purpose  Handle vCard
   */
  class VCard extends Object {
    var
      $name             = array(),
      $address          = array(),
      $email            = array(),
      $phone            = array(),
      $organization     = array(),
      $logo             = array(),
      $birthday         = NULL,
      $fullname         = '',
      $title            = '',
      $url              = '',
      $nick             = '';

    /**
     * Set Name
     *
     * @access  public
     * @param   string last
     * @param   string first
     * @param   string middle default ''
     * @param   string title default ''
     * @param   string suffix default ''
     */
    function setName($last, $first, $middle= '', $title= '', $suffix= '') {
      $this->name= array(
        'last'    => $last,
        'first'   => $first,
        'middle'  => $middle,
        'title'   => $title,
        'suffix'  => $suffix
      );
    }
    
    /**
     * Get Name. If a portion is specified, only this portion is returned,
     * else the whole array.
     *
     * @access  public
     * @param   string portion default '' either last, first, middle, title or initial
     * @return  mixed
     */
    function getName($portion= '') {
      return $portion ? $this->name[$portion] : $this->name;
    }

    /**
     * Set Address
     *
     * @access  public
     * @param   string type one of the VCARD_ADR_* constants
     * @param   string street
     * @param   string zip
     * @param   string city
     * @param   string province
     * @param   string country 
     * @param   string pobox default ''
     * @param   string suffix default ''
     */
    function setAddress(
      $type,
      $street, 
      $zip, 
      $city, 
      $province, 
      $country, 
      $pobox= '', 
      $suffix= ''
    ) {
      $this->address[$type]= array(
        'pobox'      => $pobox,     
        'suffix'     => $suffix,    
        'street'     => $street,    
        'city'       => $city,      
        'province'   => $province,  
        'zip'        => $zip,       
        'country'    => $country    
      );
    }

    /**
     * Get Address. If a portion is specified, only this portion is returned,
     * else the whole array.
     *
     * @access  public
     * @param   string type one of the VCARD_ADR_* constants
     * @param   string portion default '' either street, zip, city, province, country, pobox or suffix
     * @return  mixed
     */
    function getAddress($type, $portion= '') {
      return $portion ? $this->address[$type][$portion] : $this->address[$type];
    }

    /**
     * Add an email
     *
     * @access  public
     * @param   string type one of the VCARD_EMAIL_* constants
     * @param   string email
     */
    function addEmail($type, $email) {
      $this->email[$type][]= $email;
    }

    /**
     * Get Email
     *
     * @access  public
     * @param   string type one of the VCARD_EMAIL_* constants
     * @return  string[] emails
     */
    function getEmails($type= VCARD_EMAIL_DEFAULT) {
      return $this->email[$type];
    }

    /**
     * Set Phone
     *
     * @access  public
     * @param   mixed[] phone
     */
    function setPhone($phone) {
      $this->phone= $phone;
    }

    /**
     * Get Phone
     *
     * @access  public
     * @return  mixed[]
     */
    function getPhone() {
      return $this->phone;
    }

    /**
     * Set Organization
     *
     * @access  public
     * @param   mixed[] organization
     */
    function setOrganization($organization) {
      $this->organization= $organization;
    }

    /**
     * Get Organization
     *
     * @access  public
     * @return  mixed[]
     */
    function getOrganization() {
      return $this->organization;
    }

    /**
     * Set Logo
     *
     * @access  public
     * @param   mixed[] logo
     */
    function setLogo($logo) {
      $this->logo= $logo;
    }

    /**
     * Get Logo
     *
     * @access  public
     * @return  mixed[]
     */
    function getLogo() {
      return $this->logo;
    }

    /**
     * Set Birthday
     *
     * @access  public
     * @param   &util.Date birthday
     */
    function setBirthday(&$birthday) {
      $this->birthday= &$birthday;
    }

    /**
     * Get Birthday
     *
     * @access  public
     * @return  &util.Date
     */
    function &getBirthday() {
      return $this->birthday;
    }

    /**
     * Set Fullname
     *
     * @access  public
     * @param   string fullname
     */
    function setFullname($fullname) {
      $this->fullname= $fullname;
    }

    /**
     * Get Fullname
     *
     * @access  public
     * @return  string
     */
    function getFullname() {
      return $this->fullname;
    }

    /**
     * Set Title
     *
     * @access  public
     * @param   string title
     */
    function setTitle($title) {
      $this->title= $title;
    }

    /**
     * Get Title
     *
     * @access  public
     * @return  string
     */
    function getTitle() {
      return $this->title;
    }

    /**
     * Set Url
     *
     * @access  public
     * @param   string url
     */
    function setUrl($url) {
      $this->url= $url;
    }

    /**
     * Get Url
     *
     * @access  public
     * @return  string
     */
    function getUrl() {
      return $this->url;
    }

    /**
     * Set Nick
     *
     * @access  public
     * @param   string nick
     */
    function setNick($nick) {
      $this->nick= $nick;
    }

    /**
     * Get Nick
     *
     * @access  public
     * @return  string
     */
    function getNick() {
      return $this->nick;
    }
      
    /**
     * Parser callback
     *
     * @access  public
     * @param   array keys
     * @param   mixed value
     * @throws  lang.FormatException
     */
    function addProperty($keys, $value) {
      switch ($keys[0]) {
        case 'LOGO':
          $this->logo= array(
            'format'    => $keys[1],
            'data'      => $value
          );
          break;
          
        case 'BDAY':
          $this->birthday= &new Date($value);
          break;
        
        case 'EMAIL':
          $this->email[isset($keys[2]) ? strtolower($keys[2]) : 'default'][]= $value;
          break;
          
        case 'URL':
          $this->url= $value;
          break;
          
        case 'ORG':
          $this->organization= explode(';', $value);
          break;
          
        case 'TITLE':
          $this->title= $value;
          break;
          
        case 'NICKNAME':
          $this->nick= $value;
          break;
          
        case 'TEL':
          switch ($keys[1]) {
            case VCARD_TEL_LOC_WORK: 
            case VCARD_TEL_LOC_HOME: 
            case VCARD_TEL_LOC_CELL: 
              $this->phone[strtolower($keys[1])][isset($keys[2]) ? strtolower($keys[2]) : 'default']= $value;
              break;
              
            default: 
              return throw(new FormatException($keys[1].' is not a recognized phone type'));
          }
          break;
        
        case 'FN':
          $this->fullname= $value;
          break;
        
        case 'N':
          $values= explode(';', $value);
          $this->name= array(
            'first'      => $values[1],        // First name
            'last'       => $values[0],        // Last name
            'middle'     => $values[2],        // Middle initial
            'title'      => $values[3],        // Title
            'suffix'     => $values[4]         // Suffix
          );
          break;

        case 'ADR':
          switch ($keys[1]) {
            case VCARD_ADR_HOME:   $loc= 'home'; break;
            case VCARD_ADR_WORK:   $loc= 'work'; break;
            case VCARD_ADR_POSTAL: $loc= 'postal'; break;
            default: 
              return throw(new FormatException($keys[1].' is not a recognized address type'));
          }
          
          $values= explode(';', $value);
          $this->address[$loc]= array(
            'pobox'      => $values[0],        // P.O. Box
            'suffix'     => $values[1],        // Suffix
            'street'     => $values[2],        // Street
            'city'       => $values[3],        // City
            'province'   => $values[4],        // Province
            'zip'        => $values[5],        // Zipcode
            'country'    => $values[6]         // Country
          );
          
          break;
          
        default:
          // Discard
      }
    }
    
    /**
     * Creata a vCard from a stream
     *
     * <code>
     *   try(); {
     *     $vcard= &VCard::fromStream(new File('/tmp/imc.vcf'));
     *   } if (catch('Exception', $e)) {
     *     $e->printStackTrace();
     *     exit(-1);
     *   }
     *   
     *   var_dump($vcard);
     * </code>
     *
     * @model   static
     * @access  public
     * @param   &io.Stream stream
     * @return  &org.imc.VCard
     */
    function &fromStream(&$stream) {
      $card= &new VCard();
      
      $p= &new VFormatParser(VCARD_ID);
      $p->setDefaultHandler(array(&$card, 'addProperty'));
      
      try(); {
        $p->parse($stream);
      } if (catch('Exception', $e)) {
        return throw($e);
      }
      
      return $card;
    }
    
    /**
     * Private helper function for export
     *
     * @access  private
     * @param   string key
     * @param   mixed values
     * @return  string
     */
    function _export($key, $values) {
      $value= is_array($values) ? implode(';', $values) : $values;
      if (0 == strlen($value)) return '';
      
      return $key.':'.$value."\n";
    }
    
    /**
     * Returns the textual representation of this vCard
     *
     * <code>
     *   [...]
     *   $f= &new File('me.vcf');
     *   $f->open(FILE_MODE_WRITE);
     *   $f->write($card->export());
     *   $f->close();
     * </code>
     *
     * @access  public
     * @return  string
     */
    function export() {
    
      // Build addresses string
      $address= '';
      foreach ($this->address as $k => $v) {
        $address.= $this->_export('ADR;'.strtoupper($k).';CHARSET=UTF-8', array(
          utf8_encode($v['pobox']),
          utf8_encode($v['suffix']),
          utf8_encode($v['street']),
          utf8_encode($v['city']),
          utf8_encode($v['province']),
          utf8_encode($v['zip']),
          utf8_encode($v['country'])
        ));
      }
      
      // Build email addresses string
      $email= '';
      foreach ($this->email as $k => $v) {
        for ($i= 0, $s= sizeof($v); $i < $s; $i++) {
          $email.= $this->_export(
            'EMAIL;INTERNET'.(('default' == $k) ? '' : ';'.strtoupper($k)), 
            $v[$i]
          );
        }
      }

      // Build tel string
      $phone= '';
      foreach ($this->phone as $k => $v) {
        foreach ($v as $type => $num) {
          $email.= $this->_export(
            'TEL;'.strtoupper($k).(('default' == $type) ? '' : ';'.strtoupper($type)), 
            $num
          );
        }
      }
      
      return (
        'BEGIN:'.VCARD_ID."\n".
        $this->_export('N', array(
          $this->name['last'],
          $this->name['first'],
          $this->name['middle'],
          $this->name['title'],
          $this->name['suffix']
        )).
        $this->_export('FN', $this->fullname).
        $this->_export('TITLE', $this->fullname).
        $this->_export('NICKNAME', $this->nick).
        $this->_export('ORG', $this->organization).
        $this->_export('URL', $this->url).
        $this->_export('BDAY', is_a($this->birthday, 'Date') ? $this->birthday->toString('Y-m-d') : '').
        $phone.
        $email.
        $address.
        $this->_export('LOGO;'.$this->logo['format'].';ENCODING=BASE64', isset($this->logo['data']) 
          ? "\n    ".str_replace("\n", "\n    ", chunk_split(base64_encode($this->logo['data'])))
          : ''
        ).
        'END:'.VCARD_ID."\n"
      );
    }
  
  }
?>
