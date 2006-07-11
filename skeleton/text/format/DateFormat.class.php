<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('text.format.IFormat');
  
  /**
   * Date formatter
   *
   * @purpose  Provide a Format wrapper for date/time
   * @see      php://strftime
   * @see      xp://text.format.IFormat
   */
  class DateFormat extends IFormat {
  
    /**
     * Get an instance
     *
     * @access  public
     * @return  &text.format.DateFormat
     */
    function &getInstance() {
      return parent::getInstance('DateFormat');
    }  

    /**
     * Apply format to argument
     *
     * @access  public
     * @param   mixed fmt
     * @param   &mixed argument
     * @return  string
     * @throws  lang.FormatException
     */
    function apply($fmt, &$argument) {
      switch (gettype($argument)) {
        case 'string':
          if (-1 == ($u= strtotime($argument))) {
            return throw(new FormatException('Argument "'.$argument.'" cannot be converted to a date'));
          }
          break;
          
        case 'integer':
        case 'float':
          $u= (int)$argument;
          break;
          
        case 'object':
          if (is_a($argument, 'Date')) {
            $u= $argument->getTime();
            break;
          }
          // Break missing intentionally
          
        default:
          return throw(new FormatException('Argument of type "'.gettype($argument).'" cannot be converted to a date'));
      }
      
      return strftime($fmt, $u);
    }
  }
?>
