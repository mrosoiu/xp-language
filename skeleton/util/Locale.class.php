<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Locale
   * 
   * Usage [retreiving default locale]
   * <code>
   *   $locale= &Locale::getDefault();
   *   var_dump($locale);
   * </code>
   *
   * Usage [setting default locale]
   * <code>
   *   Locale::setDefault(new Locale('de_DE'));
   * </code>
   *
   * @see      http://ftp.ics.uci.edu/pub/ietf/http/related/iso639.txt
   * @see      http://userpage.chemie.fu-berlin.de/diverse/doc/ISO_3166.html
   * @see      http://groups.google.com/groups?threadm=DREPPER.96Aug8030605%40i44d2.ipd.info.uni-karlsruhe.de#link1
   * @purpose  Represent a locale
   */
  class Locale extends Object {
    var
      $lang     = '',
      $country  = '',
      $variant  = '';
    
    var
      $_str     = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   string lang 2-letter abbreviation of language
     * @param   string country 2-letter abbreviation of country
     * @param   string variant default ''
     */
    function __construct() {
      switch (func_num_args()) {
        case 1: 
          $this->_str= func_get_arg(0);
          sscanf(func_get_arg(0), '%2s_%2s%s', $this->lang, $this->country, $this->variant);
          break;
          
        case 2:
          list($this->lang, $this->country)= func_get_args();
          $this->_str= $this->lang.'_'.$this->country;
          break;
          
        case 3:
          list($this->lang, $this->country, $this->variant)= func_get_args();
          $this->_str= $this->lang.'_'.$this->country.'@'.$this->variant;
          break;
      }
    }
    
    /**
     * Get default locale
     *
     * @model   static
     * @access  public
     * @return  &util.Locale
     */
    function &getDefault() {
      return new Locale(('C' == ($locale= setlocale(LC_ALL, NULL)) 
        ? 'en_US'
        : $locale
      ));
    }
    
    /**
     * Set default locale for this script
     *
     * @model   static
     * @access  public
     * @param   &util.Locale locale
     * @throws  lang.IllegalArgumentException in case the locale is not available
     */
    function setDefault(&$locale) {
      if (FALSE === setlocale(LC_ALL, $locale->toString())) {
        return throw(new IllegalArgumentException(sprintf(
          'Locale [lang=%s,country=%s,variant=%s] not available',
          $this->lang, 
          $this->country, 
          ltrim($this->variant, '.@')
        )));
      }
    }

    /**
     * Get Language
     *
     * @access  public
     * @return  string
     */
    function getLanguage() {
      return $this->lang;
    }

    /**
     * Get Country
     *
     * @access  public
     * @return  string
     */
    function getCountry() {
      return $this->country;
    }

    /**
     * Get Variant
     *
     * @access  public
     * @return  string
     */
    function getVariant() {
      return $this->variant;
    }

    /**
     * Returns a hashcode for this object
     *
     * @access  public
     * @return  string
     */
    function hashCode() {
      return sprintf('%u', crc32($this->_str));
    }
    
    /**
     * Create string representation
     *
     * Examples:
     * <pre>
     * de_DE
     * en_US
     * de_DE@euro
     * de_DE.ISO8859-1
     * </pre>
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return $this->_str;
    }
  }
?>
