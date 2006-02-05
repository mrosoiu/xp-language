<?php
/* This file is part of the XP framework's people's experiments
 *
 * $Id$
 */
  require('lang.base.php');
  xp::sapi('cli');

  // {{{ main
  $p= &new ParamString();
  if (!$p->exists(1) || $p->exists('help', '?')) {
    Console::writeLine(<<<__
Tokenizes a given input and dumps the resulting tokens

Usage:
  php tokenize.php <php_code>

Example:
  php tokenize.php 'XPClass::forName("util.Binford")->newInstance()'
__
    );
    exit(1);
  }

  $tokens= token_get_all('<?php '.trim($p->value(1)).' ?>');
  for ($i= 1, $s= sizeof($tokens); $i < $s - 2; $i++) {
    if (is_array($tokens[$i])) {
      printf("%3d [%-26s] '%s'\n", $i, token_name($tokens[$i][0]), $tokens[$i][1]);
    } else {
      printf("%3d [%-26s] '%s'\n", $i, '', $tokens[$i]);
    }
  }
  // }}}
?>
