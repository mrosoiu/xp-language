<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'text.StringTokenizer',
    'net.xp_framework.util.markup.DefaultProcessor',
    'net.xp_framework.util.markup.CopyProcessor',
    'net.xp_framework.util.markup.CodeProcessor'
  );

  /**
   * Markup builder based on regular expressions
   *
   * @purpose  Plain text to markup converter
   */
  class MarkupBuilder extends Object {
    var 
      $stack= array();

    /**
     * Push a processor onto stack
     *
     * @access  protected
     * @param   &net.xp_framework.util.markup.MarkupProcessor proc
     * @return  &net.xp_framework.util.markup.MarkupProcessor
     */
    function &pushProcessor(&$proc) {
      array_unshift($this->stack, $proc);
      return $proc;
    }
    
    /**
     * Pop processor off stack
     *
     * @access  protected
     * @return  &net.xp_framework.util.markup.MarkupProcessor
     */
    function &popProcessor() {
      array_shift($this->stack);
      return $this->stack[0];
    }

    /**
     * Retrieve markup for specified text
     *
     * @access  public
     * @param   string text
     * @return  string
     */
    function markupFor($text) {
      static $processors= array();
      static $state= array(
        'pre'   => 'copy',
        'code'  => 'code'
      );

      if (!$processors) {
        $processors['default']= &new DefaultProcessor();
        $processors['copy']= &new CopyProcessor();
        $processors['code']= &new CodeProcessor();
      }
      
      $processor= &$this->pushProcessor($processors['default']);

      $st= &new StringTokenizer($text, '<>', $returnDelims= TRUE);
      $out= '';      
      while ($st->hasMoreTokens()) {
        if ('<' == ($token= $st->nextToken())) {
          $tag= $st->nextToken('>');
          
          // If this is an opening tag and a behaviour is defined for it, switch
          // states and pass control to the processor.
          if (ctype_alnum($tag[0])) {
            $st->nextToken('>');
            $lookup= strtolower($tag);

            if (isset($state[$lookup])) {
              $processor= &$this->pushProcessor($processors[$state[$lookup]]);
              $out.= $processor->initialize();
            } else {
              $out.= '<'.$tag.'>';
            }
            continue;
          }           
          
          // If this is a closing tag and behaviour is defined for it, switch back
          // state and return control to the previous processor.
          if ('/' == $tag[0]) {
            $st->nextToken('>');
            $lookup= ltrim(strtolower($tag), '/');

            if (isset($state[$lookup])) {
              $out.= $processor->finalize();
              $processor= &$this->popProcessor();
            } else {
              $out.= '<'.$tag.'>';
            }
            continue;
          }
          
          $token= '<'.$tag;
        }

        $out.= $processor->process($token);
      }
      
      return $out;
    }
  }
?>
