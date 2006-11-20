<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.parser.DateParser');

  /**
   * Represents a htdig search entry
   *
   * @purpose  Wrap search entry
   */
  class HtdigEntry extends Object {
    var
      $current=          0,
      $docId=            0,
      $stars=            0,
      $score=            0,
      $url=              '',
      $title=            '',
      $excerpt=          '',
      $metadescription=  '',
      $modified=         NULL,
      $size=             0,
      $hopcount=         0,
      $percent=          0;

    /**
     * Set Current
     *
     * @access  public
     * @param   int current
     */
    function setCurrent($current) {
      $this->current= (int)$current;
    }

    /**
     * Get Current
     *
     * @access  public
     * @return  int
     */
    function getCurrent() {
      return $this->current;
    }

    /**
     * Set DocId
     *
     * @access  public
     * @param   string docId
     */
    function setDocId($docId) {
      $this->docId= (int)$docId;
    }

    /**
     * Get DocId
     *
     * @access  public
     * @return  string
     */
    function getDocId() {
      return $this->docId;
    }

    /**
     * Set Stars
     *
     * @access  public
     * @param   int stars
     */
    function setStars($stars) {
      $this->stars= (int)$stars;
    }

    /**
     * Alias for the setStars function.
     *
     * (Needed because htdigs result field name is nstars)
     *
     * @access  public
     * @param   int stars
     */
    function setNstars($stars) {
      $this->setStars($stars);
    }

    /**
     * Get Stars
     *
     * @access  public
     * @return  int
     */
    function getStars() {
      return $this->stars;
    }

    /**
     * Set Score
     *
     * @access  public
     * @param   int score
     */
    function setScore($score) {
      $this->score= (int)$score;
    }

    /**
     * Get Score
     *
     * @access  public
     * @return  int
     */
    function getScore() {
      return $this->score;
    }

    /**
     * Set Url
     *
     * @access  public
     * @param   string url
     */
    function setUrl($url) {
      $this->url= urldecode($url);
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
     * Set Title
     *
     * @access  public
     * @param   string title
     */
    function setTitle($title) {
      $this->title= urldecode($title);
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
     * Set Excerpt
     *
     * @access  public
     * @param   string excerpt
     */
    function setExcerpt($excerpt) {
      $this->excerpt= urldecode($excerpt);
    }

    /**
     * Get Excerpt
     *
     * @access  public
     * @return  string
     */
    function getExcerpt() {
      return $this->excerpt;
    }

    /**
     * Set Metadescription
     *
     * @access  public
     * @param   string metadescription
     */
    function setMetadescription($metadesc) {
      $this->metadescription= urldecode($metadesc);
    }

    /**
     * Get Metadescription
     *
     * @access  public
     * @return  string
     */
    function getMetadescription() {
      return $this->metadescription;
    }

    /**
     * Set Modified
     *
     * @access  public
     * @param   &lang.Object modified
     */
    function setModified(&$modified) {
      if (is('util.Date', $modified)) {
        $this->modified= &$modified;
        return;
      }
      
      try(); {
        $d= &DateParser::parse(urldecode($modified));
      } if (catch('FormatException', $e)) {
      
        // Date could not be parsed, so default to now.
        $this->modified= &Date::now();
      }
      
      $this->modified= &$d;
    }

    /**
     * Get Modified
     *
     * @access  public
     * @return  &util.Date
     */
    function &getModified() {
      return $this->modified;
    }

    /**
     * Set Size
     *
     * @access  public
     * @param   int size
     */
    function setSize($size) {
      $this->size= (int)$size;
    }

    /**
     * Get Size
     *
     * @access  public
     * @return  int
     */
    function getSize() {
      return $this->size;
    }

    /**
     * Set Hopcount
     *
     * @access  public
     * @param   int hopcount
     */
    function setHopcount($hopcount) {
      $this->hopcount= (int)$hopcount;
    }

    /**
     * Get Hopcount
     *
     * @access  public
     * @return  int
     */
    function getHopcount() {
      return $this->hopcount;
    }

    /**
     * Set Percent
     *
     * @access  public
     * @param   int percent
     */
    function setPercent($percent) {
      $this->percent= (int)$percent;
    }

    /**
     * Get Percent
     *
     * @access  public
     * @return  int
     */
    function getPercent() {
      return $this->percent;
    }
    
    /**
     * Create a HtdigEntry from an array
     *
     * @model   static
     * @access  public
     * @param   &mixed array
     * @return  &org.htdig.HtdigResult
     * @throws  lang.IllegalArgumentException when array is malformed
     */
    function &fromArray(&$arr) {
      $entry= &new HtdigEntry();
      
      foreach (array_keys($arr) as $key) {
        if (!method_exists($entry, 'set'.$key))
          return throw (new IllegalArgumentException('The given array is malformed. Its key '.$key.' is not associated with a function.'));
          
        call_user_func(array(&$entry, 'set'.$key), $arr[$key]);
      }
      
      return $entry;
    }
    
    /**
     * Returns the string representation of this object.
     *
     * @access  public
     * @return  string 
     */
    function toString() {
      
      // Retrieve object variables and figure out the maximum length 
      // of a key which will be used for the key "column". The minimum
      // width of this column is 20 characters.
      $vars= get_object_vars($this);
      $max= 20;
      foreach (array_keys($vars) as $key) {
        $max= max($max, strlen($key));
      }
      $fmt= '  [%-'.$max.'s] %s';
      
      // Build string representation.
      $s= $this->getClassName().'@('.$this->hashCode()."){\n";
      foreach (array_keys($vars) as $key) {
        if ('__id' == $key) continue;

        $s.= sprintf($fmt, $key, is_a($this->$key, 'Object') 
          ? $this->$key->toString()
          : var_export($this->$key, 1)
        )."\n";
      }
      return $s.'}';
    }
  }
?>
