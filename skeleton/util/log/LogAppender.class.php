<?php
  class LogAppender extends Object {

    /**
     * F�gt die Daten an eine Textdatei an
     *
     * @access public
     * @param  mixed args Variablen
     */ 
    function append() {
    }
    
    /**
     * Gibt eine Variable sch�n lesbar zur�ck
     *
     * @access public
     * @param  mixed var Variable jeglichen Types
     * @return string Ein Printout der Variable
     */
    function varSource($var) {
      if (is_array($var) || is_object($var)) {
        ob_start();
        var_dump($var);
        $var= ob_get_contents();
        ob_end_clean();
      }
      return $var;
    }
  }
?>
