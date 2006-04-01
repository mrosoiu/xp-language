<?php
  class String {
    protected $buffer= '';
    
    public __construct($initial) {
      $this->buffer= $initial;
    }
    
    public static string toLower(string $string) {
      return strtolower($string);
    }
    
    public string asLowerCase() {
      return strtolower($this->buffer);
    }
  }
  
  $name= $argv[1];
  echo 'Hello, ', String::toLower($name), "!\n";
  
  $s= new String('Hello');
  echo $s->asLowerCase();
?>
