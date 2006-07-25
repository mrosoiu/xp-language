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
    'de.thekid.dialog.io.ImageProcessor',
    'de.thekid.dialog.io.IndexCreator',
    'de.thekid.dialog.GroupByHourStrategy',
    'de.thekid.dialog.GroupByDayStrategy',
    'img.filter.SharpenFilter'
  );
  
  define('DESCRIPTION_FILE',  'description.txt');
  define('HIGHLIGHTS_FOLDER', 'highlights');
  define('HIGHLIGHTS_MAX',    4);
  define('ENTRIES_PER_PAGE',  5);
  define('FOLDER_FILTER',     '/\.jpe?g$/i');

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
  --newdate, -N   Set date of update (default: now)
  --group, -g     Use grouping strategy (hour, day, default: hour)
__
    );
    exit(1);
  }

  // Figure out how to group images
  switch ($groupBy= $param->value('group', 'g', 'hour')) {
    case 'hour': $strategy= &new GroupByHourStrategy(); break;
    case 'day': $strategy= &new GroupByDayStrategy(); break;
    default: 
      Console::writeLine('Unknown grouping method "'.$groupBy.'"');
      exit(2);
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
      $update->setDate(new Date($param->value('newdate', 'N', time())));
      $update->setDescription($param->value('update', 'u'));

      $updateFile= &new File(DATA_FOLDER.$name.'-update_'.date('Ymd').'.dat');
      try(); {
        FileUtil::setContents($updateFile, serialize($update));
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
        exit(-1);
      }
    }

    $album->setTitle($param->value('title', 't', (string)$album->getTitle()));
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
        $highlight= &$processor->albumImageFor($i->next());
      } if (catch('ImagingException', $e)) {
        $e->printStackTrace();
        exit(-2);
      }

      if (!$highlight->exifData->dateTime) {
        $highlight->exifData->dateTime= &$album->getDate();
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
      $image= &$processor->albumImageFor($i->next());
    } if (catch('ImagingException', $e)) {
      $e->printStackTrace();
      exit(-2);
    }
    
    if (!$image->exifData->dateTime) {
      $image->exifData->dateTime= &$album->getDate();
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
  
  for ($i= 0, $s= sizeof($images); $i < $s; $i++) {
    $key= $strategy->groupFor($images[$i]);
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
  
  // Regenerate indexes
  $index= &IndexCreator::forFolder(new Folder(DATA_FOLDER));
  $index->setEntriesPerPage(ENTRIES_PER_PAGE);
  $index->setTrace($cat);
  $index->regenerate();
  
  Console::writeLine('===> Finished at ', date('r'));
  // }}}
?>
