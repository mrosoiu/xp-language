<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.PHPParser');

  /**
   * Class to manage different types of parsers
   *
   * @ext      token
   * @purpose  Manager parsers
   */
  class ParserManager extends Object {
    var
      $functions= array(),
      $classes=   array(),
      $uses=      array(),
      $requires=  array(),
      $sapis=     array(),
      $history=   array(),
      $filename=     NULL;
    
    var
      $_utimeLastChange=  0;
  
    /**
     * Constructor.
     *
     * @access  public
     * @param   string filename
     */
    function __construct($file) {
      $this->filename= $file;
    }
    
    /**
     * Check whether the file has been modified since last
     * parsing and thus reparsing is necessary.
     *
     * @access  public
     * @return  bool
     */
    function needsReparsing() {
      clearstatcache();
      if (FALSE !== ($mtime= filemtime ($this->filename)))
        return ($mtime > $this->_utimeLastChange);
      
      return TRUE;
    }
    
    /**
     * Get the time of the last parsing.
     *
     * @access  public
     * @return  int
     */
    function getLastChange() {
      return $this->_utimeLastChange;
    }
    
    /**
     * Reparse the file.
     *
     * @access  public
     */
    function parse() {
      
      // Try to parse file
      try(); {
        $parser= &new PHPParser($this->filename);
        $parser->parse();
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      
      $this->requires=  $parser->requires;
      $this->uses=      $parser->uses;
      $this->sapis=     $parser->sapis;
      
      // Use function names as array keys
      $this->functions= array();
      foreach (array_keys($parser->functions) as $idx) {
        $f= &$parser->functions[$idx];
        $this->functions[$f->name]= &$f;
      }
      
      // Use class- and function-names as array_keys
      foreach (array_keys($parser->classes) as $idx) {
        $c= &$parser->classes[$idx];
        
        // Intentional copy
        $this->classes[$c->name]= $c;
        $this->classes[$c->name]->functions= array();
        
        foreach (array_keys($c->functions) as $fidx) {
          $f= &$c->functions[$fidx];
          $this->classes[$c->name]->functions[$f->name]= &$f;
        }
      }
      
      $this->_utimeLastChange= time();
      unset ($parser);
    }
  }
?>
