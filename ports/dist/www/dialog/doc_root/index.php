<?php
/* This file is part of the XP framework's port "Album"
 *
 * $Id$ 
 */
  require('lang.base.php');
  xp::sapi('scriptlet.development');
  uses(
    'de.thekid.dialog.scriptlet.WebsiteScriptlet',
    'util.PropertyManager'
  );
  
  // {{{ main
  $pm= &PropertyManager::getInstance();
  $pm->configure('../etc/');

  scriptlet::run(new WebsiteScriptlet(
    new ClassLoader('de.thekid.dialog.scriptlet'), 
    '../xsl/'
  ));
  // }}}  
?>
