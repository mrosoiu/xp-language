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
    'de.thekid.dialog.Album',
    'de.thekid.dialog.Update',
    'de.thekid.dialog.io.FilteredFolderIterator',
    'de.thekid.dialog.io.ImageProcessor'
  );
  
  define('DESCRIPTION_FILE',  'description.txt');
  define('HIGHLIGHTS_FOLDER', 'highlights');
  define('HIGHLIGHTS_MAX',    4);
  define('ENTRIES_PER_PAGE',  5);
  define('FOLDER_FILTER',     '/\.jpe?g$/i');
  define('DATA_FILTER',       '/\.dat$/');

  define('DATA_FOLDER',       dirname(__FILE__).'/../data/');
  define('IMAGE_FOLDER',      dirname(__FILE__).'/../doc_root/albums/');
  
  // {{{ main
  $param= &new ParamString();
  if (!$param->exists(1) || $param->exists('help', '?')) {
    Console::writeLine(<<<__
Imports a directory of images into an album
--------------------------------------------------------------------------------
Usage:
  php import.php <<directory>> [<<options>>]

Options:
  --debug, -d     Turns on debugging (default: off)
  --title, -t     Set album title (default: origin directory name)
  --date, -D      Set album date (default: origin directory's creation date)
  --update, -u    Set text for update (default: do not create update)
__
    );
    exit(1);
  }

  // Check origin folder
  $origin= &new Folder($param->value(1));
  if (!$origin->exists()) {
    Console::writeLinef(
      'The specified folder "%s" does not exist', 
      $origin->getURI()
    );
    exit(2);
  }
  
  // Calculate album name
  $name= preg_replace('/[^a-z0-9-]/i', '_', $origin->dirname);
  
  // Create destination folder if it doesn't exist yet
  $destination= &new Folder(IMAGE_FOLDER.$name);
  try(); {
    $destination->exists() || $destination->create(0755);
  } if (catch('IOException', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  
  Console::writeLine('===> Starting import at ', date('r'));
  
  // Set up processor
  $processor= &new ImageProcessor();
  $processor->setOutputFolder($destination);
  
  // Check if debugging output is wanted
  $cat= NULL;
  if ($param->exists('debug')) {
    $l= &Logger::getInstance();
    $cat= &$l->getCategory();
    $cat->addAppender(new ConsoleAppender());
    $processor->setTrace($cat);
  }

  // Check if album already exists
  $serialized= &new File(DATA_FOLDER.$name.'.dat');
  if ($serialized->exists()) {
    Console::writeLine('---> Found existing album');
    try(); {
      $album= unserialize(FileUtil::getContents($serialized));
    } if (catch('IOException', $e)) {
      $e->printStackTrace();
      exit(-1);
    }
    
    // Create update entry if specified
    if ($param->exists('update', 'u')) {
      Console::writeLine('---> Creating update entry');

      $update= &new Update();
      $update->setAlbumName($name);
      $update->setTitle($album->getTitle());
      $update->setDate(new Date($param->value('date', 'D', time())));
      $update->setDescription($param->value('update', 'u'));

      $updateFile= &new File(DATA_FOLDER.$name.'-update_'.date('Ymd').'.dat');
      try(); {
        FileUtil::setContents($updateFile, serialize($update));
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
        exit(-1);
      }

      $updateFile->touch($update->date->getTime());
    }

    $album->setTitle($param->value('title', 't', $album->getTitle()));
    $album->setCreatedAt(new Date($param->value('date', 'D', $album->createdAt->getTime())));

    // We will regenerate these from scratch...
    $album->highlights= $album->chapters= array();
  } else {
    Console::writeLine('---> Creating new album...');

    // Create album
    $album= &new Album();
    $album->setName($name);
    $album->setTitle($param->value('title', 't', $origin->dirname));
    $album->setCreatedAt(new Date($param->value('date', 'D', $origin->createdAt())));
  }
  
  // Read the introductory text from description.txt if existant
  if (is_file($df= $origin->getURI().DESCRIPTION_FILE)) {
    $album->setDescription(file_get_contents($df));
  }

  // Get highlights from special folder if existant
  $highlights= &new Folder($origin->getURI().HIGHLIGHTS_FOLDER);
  if ($highlights->exists()) {
    for ($i= &new FilteredFolderIterator($highlights, FOLDER_FILTER); $i->hasNext(); ) {
      try(); {
        $highlight= &$processor->albumImageFor($highlights->getURI().$i->next());
      } if (catch('ImagingException', $e)) {
        $e->printStackTrace();
        exit(-2);
      }
      $album->addHighlight($highlight);
      Console::writeLine('     >> Added highlight ', $highlight->getName());
    }
    $highlights->close();
  }
  $needsHighlights= HIGHLIGHTS_MAX - $album->numHighlights();

  // Process all images
  $images= array();
  for ($i= &new FilteredFolderIterator($origin, FOLDER_FILTER); $i->hasNext(); ) {
    try(); {
      $image= &$processor->albumImageFor($origin->getURI().$i->next());
    } if (catch('ImagingException', $e)) {
      $e->printStackTrace();
      exit(-2);
    }
    $images[]= &$image;
    Console::writeLine('     >> Added image ', $image->getName());

    // Check if more highlights are needed
    if ($needsHighlights <= 0) continue;

    Console::writeLine('     >> Need ', $needsHighlights, ' more highlight(s), using above image');
    $album->addHighlight($image);
    $needsHighlights--;
  }
  $origin->close();

  // Sort images by their creation date (from EXIF data)
  usort($images, create_function(
    '&$a, &$b', 
    'return $b->exifData->dateTime->compareTo($a->exifData->dateTime);'
  ));
  
  // Divide up into chapters by hour
  for ($i= 0, $s= sizeof($images); $i < $s; $i++) {
    $key= $images[$i]->exifData->dateTime->toString('Y-m-d H');
    if (!isset($chapter[$key])) {
      $chapter[$key]= &$album->addChapter(new AlbumChapter($key));
    }
    
    $chapter[$key]->addImage($images[$i]);
  }

  // Save album
  $cat && $cat->debug($album);
  try(); {
    FileUtil::setContents($serialized, serialize($album));
  } if (catch('IOException', $e)) {
    $e->printStackTrace();
    exit(-1);
  }
  $serialized->touch($album->createdAt->getTime());
  
  // Regenerate indexes
  $data= &new Folder(DATA_FOLDER);
  $entries= array();
  for ($i= &new FilteredFolderIterator($data, DATA_FILTER); $i->hasNext(); ) {
    $entry= $i->next();
    $entries[filemtime($data->getURI().$entry).$entry]= basename($entry, '.dat');
  }
  $data->close();
  krsort($entries);
  $cat && $cat->debug($entries);
  
  // ...by pages. The index "page_0" can be used for the home page
  for ($i= 0, $s= sizeof($entries); $i < $s; $i+= ENTRIES_PER_PAGE) {
    Console::writeLinef('---> Generating index for album #%d - #%d', $i, $i+ ENTRIES_PER_PAGE);
    try(); {
      FileUtil::setContents(
        new File(DATA_FOLDER.'page_'.($i / ENTRIES_PER_PAGE).'.idx'), 
        serialize(array(
          'total'   => $s, 
          'perpage' => ENTRIES_PER_PAGE,
          'entries' => array_slice($entries, $i, ENTRIES_PER_PAGE)
        ))
      );
    } if (catch('IOException', $e)) {
      $e->printStackTrace();
      exit(-1);
    }
  }

  Console::writeLine('===> Finished at ', date('r'));
  // }}}
?>
