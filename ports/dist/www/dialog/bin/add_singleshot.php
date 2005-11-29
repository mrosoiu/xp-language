<?php
/* This file is part of the XP framework's port "Dialog"
 *
 * $Id$ 
 */
  require('lang.base.php');
  xp::sapi('cli');
  uses(
    'io.Folder',
    'io.File',
    'io.FileUtil',
    'util.Date',
    'util.log.Logger',
    'util.log.ConsoleAppender', 
    'de.thekid.dialog.SingleShot',
    'de.thekid.dialog.io.ShotProcessor',
    'de.thekid.dialog.io.IndexCreator',
    'img.filter.SharpenFilter'
  );

  define('ENTRIES_PER_PAGE',  5);
  define('DESCRIPTION_EXT',  '.txt');
  
  define('DATA_FOLDER',       dirname(__FILE__).'/../data/');
  define('IMAGE_FOLDER',      dirname(__FILE__).'/../doc_root/shots/');
  
  // {{{ main
  $param= &new ParamString();
  if (!$param->exists(1) || $param->exists('help', '?')) {
    Console::writeLine(<<<__
Imports a directory of images into an album
--------------------------------------------------------------------------------
Usage:
  php add_singleshot.php <<image_file>> [<<options>>]

Options:
  --debug, -d     Turns on debugging (default: off)
  --title, -t     Set shot title (default: origin file name)
  --desc, -E      Set description in case description file does not exist
  --date, -D      Set album date (default: origin file's creation date)
__
    );
    exit(1);
  }

  // Check origin file
  $origin= &new File($param->value(1));
  if (!$origin->exists()) {
    Console::writeLinef(
      'The specified file "%s" does not exist', 
      $origin->getURI()
    );
    exit(2);
  }

  // Calculate shot name
  $filename= substr($origin->getFilename(), 0, strpos($origin->getFilename(), '.'));
  $name= preg_replace('/[^a-z0-9-]/i', '_', $filename);
  
  // Create destination folder if it doesn't exist yet
  $destination= &new Folder(IMAGE_FOLDER);
  try(); {
    $destination->exists() || $destination->create(0755);
  } if (catch('IOException', $e)) {
    $e->printStackTrace();
    exit(-1);
  }

  Console::writeLine('===> Starting import at ', date('r'));

  // Set up processor
  $processor= &new ShotProcessor();
  $processor->addFilter(new SharpenFilter());
  $processor->setOutputFolder($destination);
  
  // Check if debugging output is wanted
  $cat= NULL;
  if ($param->exists('debug')) {
    $l= &Logger::getInstance();
    $cat= &$l->getCategory();
    $cat->addAppender(new ConsoleAppender());
    $processor->setTrace($cat);
  }
  
  with ($shot= &new SingleShot()); {
    $shot->setName($name);
    $shot->setFileName($origin->getFilename());
    $shot->setTitle($param->value('title', 't', $origin->filename));
    $shot->setDate(new Date($param->value('date', 'D', $origin->createdAt())));

    // Read the introductory text from [filename].txt if existant
    if (is_file($df= $origin->getPath().DIRECTORY_SEPARATOR.$filename.DESCRIPTION_EXT)) {
      Console::writeLine('---> Using description from ', $df);
      $shot->setDescription(file_get_contents($df));
    } else {
      $shot->setDescription($param->value('desc', 'E', ''));
    }

    try(); {
      $image= &$processor->albumImageFor($origin->getURI());
    } if (catch('ImagingException', $e)) {
      $e->printStackTrace();
      exit(-2);
    }

    if (!$image->exifData->dateTime) {
      $image->exifData->dateTime= &$shot->getDate();
    }
    
    $shot->setImage($image);
  }

  // Save shot
  $serialized= &new File(DATA_FOLDER.$name.'.dat');
  $cat && $cat->debug($shot);
  try(); {
    FileUtil::setContents($serialized, serialize($shot));
  } if (catch('IOException', $e)) {
    $e->printStackTrace();
    exit(-1);
  }

  // Regenerate indexes
  $index= &IndexCreator::forFolder(new Folder(DATA_FOLDER));
  $index->setEntriesPerPage(ENTRIES_PER_PAGE);
  $index->setTrace($cat);
  $index->regenerate();

  Console::writeLine('===> Finished at ', date('r'));
  // }}}
?>
