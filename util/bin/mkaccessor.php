<?php
/* This file is part of the XP framework
 *
 * $Id$ 
 */

  require('lang.base.php');
  uses('io.sys.StdStream');
  
  // {{{ main
  $stdin= &StdStream::get(STDIN);
  $str= '';
  while (!$stdin->eof()) {
    $str.= $stdin->read();
  }
  
  echo $str."\n";

  $t= strtok($str, "\r\n");
  do {
    list($var, $init)= explode('=', $t, 2);
    $var= trim($var);
    $init= substr(trim($init), 0, -1);
    
    switch (strtolower($init)) {
      case 'null':
        $type= 'lang.Object';
        $ref= TRUE;
        break;
        
      case "''":
        $type= 'string';
        $ref= FALSE;
        break;

      case '0':
        $type= 'int';
        $ref= FALSE;
        break;

      case '0.0':
        $type= 'float';
        $ref= FALSE;
        break;

      case 'false':
      case 'true':
        $type= 'bool';
        $ref= FALSE;
        break;

      case 'array()':
        $type= 'mixed[]';
        $ref= FALSE;
        break;

      case 1 == preg_match('#([^\[]+)\[\]#', $init, $matches):
      
        // TBD: Calculate correct singular, e.g. entry / entries
        $name= rtrim(substr($var, 1), 's');
        printf(<<<__
    /**
     * Add an element to %2\$ss
     *
     * @access  public
     * @param   %4\$s%3\$s %2\$s
     */
    function add%1\$s(%4\$s\$%2\$s) {
      \$this->%2\$ss[]= %4\$s\$%2\$s;
    }

    /**
     * Get one %2\$s element by position. Returns NULL if the element 
     * can not be found.
     *
     * @access  public
     * @param   int i
     * @return  %4\$s%3\$s
     */
    function %4\$s%2\$sAt(\$i) {
      if (!isset(\$this->%2\$ss[\$i])) return NULL;
      return \$this->%2\$ss[\$i];
    }

    /**
     * Get number of %2\$ss
     *
     * @access  public
     * @return  int
     */
    function num%1\$ss() {
      return sizeof(\$this->%2\$ss);
    }

__
    , ucfirst($name), $name, $matches[1], strstr($matches[1], '.') ? '&' : '');
        continue 2;
        
      default:
        $type= 'mixed';
        $ref= FALSE;
        break;
    }
    
    printf(<<<__
    /**
     * Set %2\$s
     *
     * @access  public
     * @param   %4\$s%3\$s %2\$s
     */
    function set%1\$s(%4\$s\$%2\$s) {
      \$this->%2\$s= %4\$s\$%2\$s;
    }

    /**
     * Get %2\$s
     *
     * @access  public
     * @return  %4\$s%3\$s
     */
    function %4\$sget%1\$s() {
      return \$this->%2\$s;
    }


__
    , ucfirst(substr($var, 1)), substr($var, 1), $type, $ref ? '&' : '');    
  } while ($t= strtok("\r\n"));
  
  // }}}
?>
