<?php
/* This file is part of the XP framework's examples
 *
 * $Id$
 */
  require('lang.base.php');
  xp::sapi('cli');
  uses(
    'com.sun.webstart.jnlp.JnlpDocument', 
    'peer.http.HttpConnection',
    'lang.System',
    'lang.Process',
    'io.File',
    'io.Folder'
  );
  
  // {{{ main
  $p= &new ParamString();
  if (!$p->exists(1)) {
    Console::writeLinef('Usage: %s <url_to_jnlp_file> [--java=<java_executable>]', $p->value(0));
    exit(1);
  }
  
  Console::writeLine('===> Downloading webstart URL ', $p->value(1));
  try(); {
    $c= &new HttpConnection($p->value(1));
    $response= &$c->get();
    $document= '';
    while (FALSE !== ($buf= $response->readData())) {
      $document.= $buf;
    }
    delete($c);
  } if (catch('Exception', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  try(); {
    $j= &JnlpDocument::fromString($document);
  } if (catch('Exception', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  // Print out information provided by the JNLP information section
  $inf= &$j->getInformation();
  Console::writeLinef(
    "---> Application is %s (%s)\n     Vendor %s (see %s)", 
    $inf->getTitle(),
    $inf->getDescription(JNLP_DESCR_SHORT),
    $inf->getVendor(),
    $inf->getHomepage()
  );
  
  // Create an application directory
  $folder= &new Folder(basename($j->getCodebase()));
  try(); {
    if (!$folder->exists()) $folder->create();
  } if (catch('Exception', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  // Download all JAR files, adding them to the classpath as we go
  $classpath= System::getEnv('CLASSPATH');
  Console::writeLinef('---> Processing resources from codebase %s', $j->getCodebase());
  foreach ($j->getResources() as $resource) {
    if (!is('JnlpJarResource', $resource)) {
      continue;
    }

    $href= $resource->getHref();
    $classpath.= ':'.rtrim($folder->getURI(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$href;
    try(); {

      // Download it
      $c= &new HttpConnection($j->getCodebase().'/'.$href);
      Console::writef('     >> Downloading %s... ', $href);
      $response= &$c->get();
      Console::writef(
        'Status %d, Length %d of %s... ', 
        $response->getStatusCode(), 
        $response->getHeader('Content-length'), 
        $response->getHeader('Content-type')
      );
      
      // Create a new file instance
      $jar= &new File($folder->getURI().DIRECTORY_SEPARATOR.$href);
      
      // Check if this file resided in a subdirectory. If so, create this
      // subdirectory if necessary
      $f= &new Folder(dirname($jar->getURI()));
      if (!$f->exists()) $f->create();
      
      $jar->open(FILE_MODE_WRITE);
      while (FALSE !== ($buf= $response->readData(0x2000, $binary= TRUE))) {
        $jar->write($buf);
      }
      $jar->close();
    } if (catch('Exception', $e)) {
      Console::writeLine('FAIL');
      $e->printStackTrace();
      exit(-1);
    }
    Console::writeLine('OK');
  }
  
  // Execute Java
  $app= &$j->getApplicationDesc();
  $cmd= sprintf(
    '%s -cp %s %s %s 2>&1',
    $p->value('java', 'j', 'java'),
    $classpath,
    $app->getMain_Class(),
    implode(' ', $app->getArguments())
  );
  Console::writeLine('---> Executing ', $cmd);

  try(); {
    $p= &new Process($cmd);
    while (!$p->out->eof()) {
      Console::writeLine($p->out->readLine());
    }
  } if (catch('Exception', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  // Pass exit value to caller
  exit($p->close());
  // }}}
?>
