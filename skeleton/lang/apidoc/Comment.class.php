<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * Base class for comments
   *
   */
  class Comment extends Object {
    var $text;
    
    /**
     * Handles tags - override!
     *
     * @access  private
     * @param   string tag Tag (without leading "@")
     * @param   string line Line of text
     * @return  bool handled
     */
    function &_handleTag($tag, $line) {
      return FALSE;
    }
    
    /**
     * Sets text
     *
     * @access  public
     * @param   string text
     */
    function setText($text) {
      $this->text= $text;
    }
    
    /**
     * Gets text
     *
     * @access  public
     * @param   
     * @return  
     */
    function getText($text) {
      return $this->text;
    }
  
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function fromString($str) {
      if ('/*' !== substr($str, 0, 2)) {
        return throw(new FormatException('comment format unrecognized ['.$str.']'));
      }
      
      $tag= '';
      foreach (preg_split('/[\r\n]([\s\t]*\* ?)?/', $str) as $line) {
        if (empty($line)) continue;
        switch ($line{0}) {
          case '/':
            continue 2;
            
          case '@':
            list($tag, $line)= preg_split('/[\s\t]+/', substr($line, 1), 2);
            break;
            
          case "\t":
          case ' ':
            if (!empty($tag) && (NULL != $descr)) {
              $descr.= "\n".trim($line); 
              continue 2;
            }
            break; 
        }
        
        if (FALSE === ($descr= &$this->_handleTag(strtolower($tag), $line))) {
          $this->text.= $line."\n";
        }
      }
      
      $this->text= trim(chop($this->text));
    }
  }
?>
