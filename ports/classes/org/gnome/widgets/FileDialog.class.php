<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'org.gnome.GtkGladeDialogWindow', 
    'org.gnome.util.GTKPixmapLoader', 
    'org.gnome.util.GTKWidgetUtil', 
    'io.Folder',
    'lang.System'
  );

  /**
   * File dialog 
   * <code>
   *   $dlg= &new FileDialog();
   *   if ($dlg->show()) {
   *     printf("File selected: %s%s\n", $dlg->getDirectory(), $dlg->getFilename());
   *   }
   * </code>
   *
   * @test     xp://net.xp_framework.unittest.runner.gtk.UnitTestUI
   * @purpose  Provide a widget for file dialogs
   */
  class FileDialog extends GtkGladeDialogWindow {
    var
      $filename = '',
      $dir      = '',
      $filter   = '',
      $succes   = FALSE;

    /**
     * Constructor
     *
     * @access  public
     * @param   string dir default '.'
     * @param   string filter default '.*'
     */
    function __construct($dir= '.', $filter= '.*') {
      $this->dir= $dir;
      $this->filter= $filter;
      parent::__construct(dirname(__FILE__).'/filedialog.glade', 'filedialog');
    }
   
    /**
     * Callback for OK and cancel buttons
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onClose(&$widget) {
      $this->success= ('button_ok' == $widget->get_name());
      $this->close();
    }
    
    /**
     * Callback for up button
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onUpDirClicked(&$widget) {
      $this->setDirectory(substr($this->dir, 0, strrpos(
        substr($this->dir, 0, -1), 
        DIRECTORY_SEPARATOR
      )).DIRECTORY_SEPARATOR);
    }


    /**
     * Callback for home button
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onHomeClicked(&$widget) {
      $this->setDirectory(System::getProperty('user.home'));
    }

    /**
     * Callback for favorites buttons
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onFavoriteClicked(&$widget) {
      $d= strtr(substr($widget->get_name(), 11), array(
        'HOME'  => System::getProperty('user.home'),
        'TMP'   => System::getProperty('os.tempdir'),
        'ROOT'  => DIRECTORY_SEPARATOR,
        '_'     => DIRECTORY_SEPARATOR
      ));
      $this->setDirectory($d);
    }

    /**
     * Callback for refresh button
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onRefreshClicked(&$widget) {
      $this->setDirectory($this->dir);
    }
    
    /**
     * Callback for history buttons
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onPNClicked(&$widget) {
      $this->cat->debug($widget->get_name(), $this->history, $this->history_offset);
      $this->history_offset+= ('button_prev' == $widget->get_name()) ? -1 : 1;
      $this->cat->debug($widget->get_name(), $this->history_offset, $this->history[$this->history_offset]);
      $this->setDirectory($this->history[$this->history_offset], FALSE);
    }

    /**
     * Callback for when a row in the file list is selected
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     * @param   int row
     * @param   mixed data
     * @param   php.GtkEvent event
     */
    function onEntrySelected(&$widget, $row, $data, $event) {
      $filetype= $widget->get_text($row, 1);
      $entry= $widget->get_pixtext($row, 0);
      
      // Check if an item was double clicked
      if (isset($event) && GDK_2BUTTON_PRESS == $event->type) {
        if ('' == $filetype) {
          $this->setDirectory($this->dir.$entry[0]);
        } else {
          $this->filename= $entry[0];
          $this->onClose($this->buttons['ok']);
        }
        return;
      }
      
      $this->filename= $entry[0];
      
      // Update location entry
      $this->location->set_text($entry[0]);
      
      // Set OK button sensitive if file type is not empty (indicating a directory)
      $this->buttons['ok']->set_sensitive('' != $filetype);
    }
    
    /**
     * Initialize application
     *
     * @access  public
     */
    function init() {
      $this->window->set_default_size(400, 420);
      
      // File list
      $this->files= &$this->widget('clist_files');
      $this->files->set_row_height(26);
      $this->files->set_sort_column(1); // Sort by type
      $this->connect($this->files, 'select_row', 'onEntrySelected');
      
      // Location
      $this->location= &$this->widget('entry_location');
      
      // Combo
      $this->combo= &$this->widget('combo_dir');
      
      // Buttons
      foreach (array(
        'ok'        => 'onClose',
        'cancel'    => 'onClose',
        'up'        => 'onUpDirClicked',
        'home'      => 'onHomeClicked',
        'refresh'   => 'onRefreshClicked',
        'next'      => 'onPNClicked',
        'prev'      => 'onPNClicked'
      ) as $n => $callback) {
        $this->buttons[$n]= $this->connect(
          $this->widget('button_'.$n), 
          'clicked', 
          $callback
        );
      }
      
      // Favorites
      $this->favorites= &$this->widget('bar_favorites');
      $this->favorites->set_button_relief(GTK_RELIEF_NONE);
      $view= &$this->widget('view_favorites');
      $style= Gtk::widget_get_default_style();
      $style->base[GTK_STATE_NORMAL]= $style->mid[GTK_STATE_NORMAL];
      $view->set_style($style);
      
      GTKWidgetUtil::connectChildren($this->widget('bar_favorites'), array(
        ':clicked' => array(&$this, 'onFavoriteClicked')
      ));
      
      // History
      $this->history= array();
      $this->history_offset= 0;

      // Load pixmaps
      $this->pixmaps= array();
      $if= &new Folder(dirname(__FILE__).'/icons/');
      $loader= &new GTKPixmapLoader($this->window->window, $if->uri);
      try(); {
        while ($entry= $if->getEntry()) {
          if ('.xpm' != substr($entry, -4)) continue;
          $entry= substr($entry, 0, -4);
          
          $this->pixmaps= array_merge(
            $this->pixmaps, 
            $loader->load($entry)
          );
        }
        $if->close();
      } if (catch('IOException', $e)) {
        $this->cat->error($e);
        
        // Fall through, this is not critical
      }
      
      // Read files
      $this->setDirectory($this->dir);
    }
    
    /**
     * Run this dialog
     *
     * @access  public
     */
    function run() {
      $this->success= FALSE;
      parent::run();
    }
    
    /**
     * Format file size into a string
     *
     * @access  private
     * @param   int s size
     * @return  string formatted output
     */
    function _size($s) {
      if ($s < 1024) return sprintf('%d Bytes', $s);
      if ($s < 1048576) return sprintf('%0.2f KB', $s / 1024);
      if ($s < 1073741824) return sprintf('%0.2f MB', $s / 1048576);
      return sprintf('%0.2f GB', $s / 1073741824);
    }

    /**
     * Set Filename
     *
     * @access  public
     * @param   string filename
     */
    function setFilename($filename) {
      $this->filename= $filename;
    }

    /**
     * Get Filename
     *
     * @access  public
     * @return  string
     */
    function getFilename() {
      return $this->filename;
    }

    /**
     * Set Filter
     *
     * @access  public
     * @param   string filter
     */
    function setFilter($filter) {
      $this->filter= $filter;
    }

    /**
     * Get Filter
     *
     * @access  public
     * @return  string
     */
    function getFilter() {
      return $this->filter;
    }

    /**
     * Get Dir
     *
     * @access  public
     * @return  string
     */
    function getDirectory() {
      return $this->dir;
    }
    
    /**
     * Set directory to show
     *
     * @access  public
     * @param   string directory
     * @param   bool update_offset default TRUE
     */
    function setDirectory($dir, $update_offset= TRUE) {
      $this->cat->debug('Change dir from ', $this->dir, 'to', $dir);
      $this->dir= $dir;
      
      // Disable Up button if we are at top
      $this->buttons['up']->set_sensitive(strlen($this->dir) > 1);
      
      // Update combo
      $this->history[]= $this->dir;
      $size= sizeof($this->history)- 1;
      $this->combo->set_popdown_strings(array_unique($this->history));
      
      if ($update_offset) $this->history_offset= $size;
      
      // "Previous" is available if this is not the first call
      $this->buttons['prev']->set_sensitive($this->history_offset > 0);
      $this->buttons['next']->set_sensitive($this->history_offset < $size);

      $this->readFiles();
    }
            
    /**
     * Read the selected directory's content
     *
     * @access  protected
     */  
    function readFiles() {
      $f= &new Folder($this->dir);

      // Disable Up button if we are at top
      $this->buttons['up']->set_sensitive(strlen($this->dir) > 1);
      
      // Update entry
      $entry= $this->combo->entry;
      $entry->set_text($f->uri);

      // Update list
      $this->files->freeze();
      $this->files->clear();
      try(); {
        while ($entry= $f->getEntry()) {
          $icon= $mask= NULL;
          if ($dir= is_dir($f->uri.$entry)) {
          
            // Set folder icon
            $icon= $this->pixmaps['p:folder'];
            $mask= $this->pixmaps['m:folder'];
          } else {
            if (!preg_match(':'.$this->filter.':i', $entry)) continue;
            
            $ext= '(n/a)';
              if (FALSE !== ($p= strrpos($entry, '.')) && $p > 0) $ext= substr($entry, $p+ 1);
            
            // Check for "special" files
            if (preg_match('#README|TODO|INSTALL|COPYRIGHT|NEWS#', $entry)) {
              $idx= 'special.readme';
            } else {
              $idx= isset($this->pixmaps['p:ext.'.$ext]) ? 'ext.'.$ext : 'none';
            }
            
            // Set icon
            $icon= $this->pixmaps['p:'.$idx];
            $mask= $this->pixmaps['m:'.$idx];
          }
          
          // Get file owner's name
          // !!! TBD: Generic approach, posix_getpwuid may not exist !!!
          $owner= posix_getpwuid(fileowner($f->uri.$entry));
          
          $this->files->set_pixtext(
            $this->files->append(array(
              $entry,
              $dir ? '' : $ext,
              $this->_size(filesize($f->uri.$entry)),
              date('Y-m-d H:i', filemtime($f->uri.$entry)),
              $owner['name'],
              substr(sprintf("%o", fileperms($f->uri.$entry)), 3- $dir)
            )),
            0, 
            $entry,
            4,
            $icon,
            $mask
          );

        }
        
        // Copy folder's URI (will be full path)
        $this->dir= $f->uri;
        $f->close();
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
      }
      $this->files->sort();
      $this->files->thaw();
    }
    
    /**
     * Show this dialog
     *
     * @access  public
     * @return  bool TRUE in case a file was selected and OK pressed
     */
    function show() {
      parent::show();
      return $this->success;
    }
  }
?>
