<?php
  uses('lang.Enum');$package= 'net.xp_forge.examples.enum.coin'; class net�xp_forge�examples�enum�coin�Coin extends lang�Enum { public static $penny,$nickel,$dime,$quarter;
static function __static() {  net�xp_forge�examples�enum�coin�Coin::$penny= new net�xp_forge�examples�enum�coin�Coin(1, 'penny');
  net�xp_forge�examples�enum�coin�Coin::$nickel= new net�xp_forge�examples�enum�coin�Coin(2, 'nickel');
  net�xp_forge�examples�enum�coin�Coin::$dime= new net�xp_forge�examples�enum�coin�Coin(10, 'dime');
  net�xp_forge�examples�enum�coin�Coin::$quarter= new net�xp_forge�examples�enum�coin�Coin(25, 'quarter');
}public static function values() { return array(  net�xp_forge�examples�enum�coin�Coin::$penny,   net�xp_forge�examples�enum�coin�Coin::$nickel,   net�xp_forge�examples�enum�coin�Coin::$dime,   net�xp_forge�examples�enum�coin�Coin::$quarter, ); }
/**
 * @return  int
 */
public function value(){return $this->ordinal;
  }
/**
 * @return  string
 */
public function color(){switch ($this) {case net�xp_forge�examples�enum�coin�Coin::$penny: return 'copper';
  ;
  case net�xp_forge�examples�enum�coin�Coin::$nickel: return 'nickel';
  ;
  case net�xp_forge�examples�enum�coin�Coin::$dime: ;
  case net�xp_forge�examples�enum�coin�Coin::$quarter: return 'silver';
  ;
  };
  }};
  
?>