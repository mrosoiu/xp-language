<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'gui.gtk.GTKGladeApplication',
    'gui.gtk.util.GTKWidgetUtil',
    'gui.gtk.util.GTKPixmapLoader',
    'peer.mail.store.Pop3Store',
    'peer.mail.store.ImapStore',
    'peer.URL'
  );

  /**
   * Mail box monitor
   *
   * @ext      gtk
   * @purpose  Application
   */
  class MBoxMonitor extends GTKGladeApplication {
    var
      $stor         = NULL,
      $statusbar    = NULL,
      $list         = NULL,
      $pixmaps      = array(),
      $dsn          = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &util.cmd.ParamString p
     */
    function __construct(&$p) {
      if (!$p->exists(1)) {
        printf(
          "Usage: gtkphp %1\$s protocol://user:password@server\n".
          "       Supported protocols: pop3, imap\n",
          $p->value(0)
        );
        exit(-1);
      }
    
      // Find protocol
      $this->dsn= &new URL($p->value(1));
      switch ($this->dsn->getScheme()) {
        case 'pop3': 
          $this->stor= &new Pop3Store(); 
          break;
          
        case 'imap': 
          $this->stor= &new ImapStore(); 
          break;
          
        default: 
          printf("Protocol %s not supported\n", $this->dsn->getScheme()); 
          exit(-2);
      }
      
      parent::__construct('MBoxMonitor', dirname(__FILE__).'/mboxmonitor.glade');
    }
    
    /**
     * Initialize application
     *
     * @access  public
     */
    function init() {
      parent::init();
      $this->window->set_title(sprintf(
        'MailboxMonitor [%s] %s@%s',
        $this->dsn->getScheme(),
        $this->dsn->getUser(),
        $this->dsn->getHost()
      ));
      
      // Connect buttons
      GTKWidgetUtil::connect($this->widget('button_receive'), array(
        ':clicked'  => array(&$this, 'onReceiveButtonClicked')
      ));
      GTKWidgetUtil::connect($this->widget('button_expunge'), array(
        ':clicked'  => array(&$this, 'onExpungeButtonClicked')
      ));
      
      // Set up additional widgets we need to access via name
      $this->statusbar= &$this->widget('statusbar');
      $this->menu= &$this->widget('menu1');
      GTKWidgetUtil::connectChildren($this->menu, array(
        ':activate'             => array(&$this, 'onMenuItemActivated'),
      ));
      
      $this->list= &$this->widget('clist_messages');
      $this->list->set_row_height(18);
      GTKWidgetUtil::connect($this->list, array(
        ':click_column'         => array(&$this, 'onListColumnClicked'),
        ':button_press_event'   => array(&$this, 'onListButtonPressed'),
      ));
      
      // Load art
      $p= &new GTKPixmapLoader($this->window->window, dirname(__FILE__));
      $this->pixmaps= $p->load(array(
        'priority-high',
        'mail-new',
        'mail-deleted',
        'attachment'
      ));
    }
    
    /**
     * Callback for "delete"
     *
     * @access  protected
     * @param   int[] selection
     */
    function onDeleteMenuItemActivated($selection) {
      $this->list->freeze();
      foreach (array_values($this->list->selection) as $idx) {
        $msg= &$this->list->get_row_data($idx);
        
        try(); {
          $msg->folder->deleteMessage($msg);
        } if (catch('Exception', $e)) {
          $this->log($e->getStackTrace());
          
          $this->setStatusText('Error updating message status');
          continue;
        }
        
        $row= $this->list->get_pixtext($idx, 0);
        $this->list->set_pixtext(
          $idx, 
          0,
          $row[0],
          $row[1],
          $this->pixmaps['p:mail-deleted'], 
          $this->pixmaps['m:mail-deleted']
        );       
      }
      $this->list->thaw();
    }

    /**
     * Callback for "undelete"
     *
     * @access  protected
     * @param   int[] selection
     */
    function onUndeleteMenuItemActivated($selection) {
      $this->list->freeze();
      foreach (array_values($this->list->selection) as $idx) {
        $msg= &$this->list->get_row_data($idx);
        
        try(); {
          $msg->folder->undeleteMessage($msg);
        } if (catch('Exception', $e)) {
          $this->log($e->getStackTrace());
          
          $this->setStatusText('Error updating message status');
          continue;
        }
        
        $row= $this->list->get_pixtext($idx, 0);
        $this->list->set_pixtext(
          $idx, 
          0,
          $row[0],
          $row[1],
          $this->pixmaps['p:mail-new'], 
          $this->pixmaps['m:mail-new']
        );       
      }
      $this->list->thaw();
    }
    
    /**
     * Callback for _all_ menu items in popup menu
     *
     * @access  protected
     * @param   &php.GtkWidget item
     */
    function onMenuItemActivated(&$item) {
      return call_user_func(
        array(&$this, sprintf('on%sMenuItemActivated', ucfirst($item->get_name()))),
        $this->list->selection
      );
    }
    
    /**
     * Callback for mouse clicks in the list
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     * @param   &php.GdkEvent event
     */
    function onListButtonPressed(&$widget, &$event) {
      if (3 != $event->button || empty($widget->selection)) return;
      
      // Show context menu
      $this->menu->popup(
        NULL, 
        NULL, 
        NULL,
        $event->button,
        $event->time
      );
    }
    
    /**
     * Callback for column clicks
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     * @param   int column
     */
    function onListColumnClicked(&$widget, $column) {
      $this->log('Column', $column, 'clicked, sorting...');
      $widget->set_sort_column($column);
      $widget->sort();
    }
    
    /**
     * Set statusbar text and waits for the GUI to process
     * whatever events are still pending.
     *
     * @access  protected
     * @param   string fmt
     * @param   mixed* args
     */
    function setStatusText($fmt) {
      $args= func_get_args();
      $text= vsprintf($args[0], array_slice($args, 1));
      $this->log('Status', $text);
      $this->statusbar->push(1, $text);
      
      // Leave the GUI time to repaint
      while(Gtk::events_pending()) Gtk::main_iteration();
    }
    
    /**
     * Add a message to the list
     *
     * @access  protected
     * @param   &peer.mail.Message msg
     */
    function addMessage(&$msg) {
      $idx= $this->list->append(array(
        NULL,     // Pixmap
        NULL,     // Attachment
        NULL,     // Status
        $msg->from->personal.' <'.$msg->from->localpart.'@'.$msg->from->domain.'>',
        $msg->getSubject(),
        $msg->date->toString('Y-m-d H:i:s'),
        sprintf('%0.2f KB', $msg->size / 1024)
      ));

      // Icon
      $this->list->set_pixtext(
        $idx, 
        0,
        '+'.$msg->uid,
        4,
        $this->pixmaps['p:mail-new'], 
        $this->pixmaps['m:mail-new']
      );

      // Attachment
      if (is_a($msg, 'MimeMessage')) $this->list->set_pixtext(
        $idx, 
        1,
        'TRUE',
        4,
        $this->pixmaps['p:attachment'], 
        $this->pixmaps['m:attachment']
      );

      // Priority
      if (MAIL_PRIORITY_HIGH >= $msg->priority) $this->list->set_pixtext(
        $idx, 
        2,
        $msg->priority,
        4,
        $this->pixmaps['p:priority-high'], 
        $this->pixmaps['m:priority-high']
      );
      
      // Copy message
      $this->list->set_row_data($idx, $msg);
      
      // Leave the GUI time to repaint
      while(Gtk::events_pending()) Gtk::main_iteration();
    }
    
    
    /**
     * Callback for when expunge button is clicked
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onExpungeButtonClicked(&$widget) {
      try(); {
        $this->stor->expunge();
      } if (catch('Exception', $e)) {
        $this->log($e->getStackTrace());
        $this->setStatusText('Error expunging!');
        return;
      }
      
      $this->setStatusText('Expunged');
      
      // Reread list
      $this->onReceiveButtonClicked($widget);
    }
    
    /**
     * Callback for when receive button is clicked
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     */
    function onReceiveButtonClicked(&$widget) {

      // Set receive button unsensitive
      $r= &$this->widget('button_receive');
      $r->set_sensitive(FALSE);
      
      // Freeze list
      $this->list->set_sensitive(FALSE);
      $this->list->freeze();
      
      try(); {
        $this->stor->cache->expunge();
        
        if (!$this->stor->isConnected()) {
          $this->setStatusText('Connecting...');
          $this->stor->connect($this->dsn->getURL()); 
        }
        
        $this->setStatusText('Getting folder contents');
        if ($f= &$this->stor->getFolder('INBOX')) {
          $f->open();
          
          $this->list->clear();
          while ($msg= &$f->getMessage()) {
            $this->setStatusText('Retreiving message %s', $msg->uid);
            $this->addMessage($msg);
          }
        }
      } if (catch('Exception', $e)) {
        $this->setStatusText('Error retreiving messages');
        
        // TBD: Show messagebox
        $this->log($e->getStackTrace());
        $r->set_sensitive(TRUE);
        
        // Unfreeze list
        $this->list->set_sensitive(TRUE);
        $this->list->thaw();
        return;
      }
      
      // Set receive button sensitive again
      $r->set_sensitive(TRUE);
      
      // Set expunge button sensitive
      $e= &$this->widget('button_expunge');
      $e->set_sensitive(TRUE);
      
      // Unfreeze list
      $this->list->set_sensitive(TRUE);
      $this->list->thaw();
      
      $this->setStatusText('Done');
    }
    
    /**
     * Close application
     *
     * @access  public
     */
    function done() {
      if (!parent::done()) return;
      $this->setStatusText('Disconnecting...');
      $this->stor->close();
    }
  }
?>
