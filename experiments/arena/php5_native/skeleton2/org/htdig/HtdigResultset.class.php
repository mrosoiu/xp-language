<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Wraps a complete htdig search result. This includes the 
   * search entries, as well as the search metadata.
   *
   * @purpose  Wrap search result
   */
  class HtdigResultset extends Object {
    public
      $metaresult=  NULL,
      $results=     array();
    
    public
      $_csvdef=     NULL;

    /**
     * Set Csvdef. This is the mapping of the no. of the retrieved
     * column to its name, just like a csv-file header.
     *
     * @access  public
     * @param   &array _cvsdef
     */
    public function setCsvdef($csvdef) {
      $this->_csvdef= $csvdef;
    }

    /**
     * Set Metaresult
     *
     * @access  public
     * @param   &mixed metaresult
     */
    public function setMetaresult(&$metaresult) {
      static $mapping= array(
        'boolean'       => 'boolean',
        'logicalwords'  => 'logicalWords',
        'pattern'       => 'pattern',
        'matches'       => 'matches',
        'pages'         => 'pages'
      );

      $this->metaresult= array();
      foreach ($mapping as $src => $tgt) {
        if (isset ($metaresult[$src])) {
          $this->metaresult[$tgt]= $metaresult[$src];
        }
      }
    }
    
    /**
     * Returns the number of matches contained in
     * this resultset.
     *
     * @access  public
     * @return  int matches
     */
    public function getMatches() {
      return $this->metaresult['matches'];
    }
    
    /**
     * Returns the logical words determined for the
     * search query.
     *
     * @access  public
     * @return  string words
     */
    public function getLogicalWords() {
      return $this->metaresult['logicalWords'];
    }    

    /**
     * Get Metaresult
     *
     * @access  public
     * @return  &lang.Object
     */
    public function &getMetaresult() {
      return $this->metaresult;
    }

    /**
     * Set Results
     *
     * @access  public
     * @param   mixed[] results
     */
    public function setResults($results) {
      $this->results= $results;
    }

    /**
     * Get Results
     *
     * @access  public
     * @return  mixed[]
     */
    public function getResults() {
      return $this->results;
    }
    
    /**
     * Adds an entry to the result entries.
     *
     * @access  public
     * @param   &mixed array raw form of result data
     */
    public function addResult($result) {
      $res= array();
      foreach (array_keys($result) as $idx) {
        $res[$this->_csvdef[$idx]]= &$result[$idx];
      }
      
      if ($entry= &HtdigEntry::fromArray($res))
        $this->results[]= &$entry;
    }
    
    /**
     * Rewinds the result pointer to the first result.
     *
     * @access  public
     */
    public function rewindResults() {
      reset($this->results);
    }
    
    /**
     * Fetch the next result object. Returns FALSE when there
     * are no more result objects left.
     *
     * @access  public
     * @return  &mixed
     */
    public function &getNextResult() {
      $result= &current($this->results);
      next($this->results);
      return $result;
    }
            
    /**
     * Returns the string representation of this object.
     *
     * @access  public
     * @return  string 
     */
    public function toString() {
      
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
        
        if ('results' == $key) {
          foreach (array_keys($this->results) as $idx) {
            $s.= '===> Result #'.$idx.":\n";
            $s.= $this->results[$idx]->toString()."\n";
          }
          
          continue;
        }

        $s.= sprintf($fmt, $key, is('Object', $this->$key) 
          ? $this->$key->toString()
          : var_export($this->$key, 1)
        )."\n";
      }
      return $s.'}';
    }
  }
?>
