<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'util.log.Logger', 
    'util.log.FileAppender',
    'gui.WidgetNotFoundException'
  );
 
  /**
   * Base application class. Extend this class in order to create
   * an application. 
   *
   * This class has a member variable "cat" which references a
   * log category with an appender to STDERR and may be used to
   * print debugging information.
   *
   * @ext      gtk
   * @see      php-gtk://GtkWindow
   * @see      http://gtk.org/
   * @purpose  Base class
   */
  class GtkApplication extends Object {
    var 
      $window   = NULL,
      $cat      = NULL,
      $rcfile   = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   &util.ParamString p
     */
    function __construct(&$p) {

      // Set up logger
      $l= &Logger::getInstance();
      $this->cat= &$l->getCategory(get_class($this));
      $this->cat->identifier= get_class($this);
      if (empty($this->cat->_appenders)) {
        $this->cat->addAppender(new FileAppender('php://stderr'));
      }
      
      // Parse rc file if one is set
      if (!empty($this->rcfile)) Gtk::rc_parse($this->rcfile);
      
      // Create main window
      $this->create();
      $this->window->realize();

      // Connect destroy signal
      $this->window->connect('destroy', array(&$this, 'destroy'));

      $this->param= &$p;

      
    }
    
    /**
     * Returns a widget
     *
     * @access  protected
     * @param   string name
     * @return  &php.GtkWidget
     * @throws  gui.WidgetNotFoundException
     */
    function &widget($name) {
      if (isset($this->{$name})) {
        return throw(new WidgetNotFoundException($name));
      } 
      return $this->{$name};
    }
    
    /**
     * Connect a widget's signal to a callback. The callback method
     * name is created by using 'on' + <widget_name> + <signal_name>
     * if none is defined.
     *
     * By adding 'after:' before the signal's name, you can specify
     * that the callback is called _after_ the normal and default 
     * handlers. Per default, invokation takes place directly when
     * the signal is emmitted.
     *
     * Example:
     * <code>
     *   function init() {
     *     $this->connect($this->widget('select'), 'clicked');
     *     $this->connect($this->widget('button'), 'clicked', 'onSelectClicked');
     *     $button= &new GtkButton('Click me:)');
     *     $button->set_name('click_me');
     *     $this->connect($button, 'clicked', 'onSelectClicked');
     *   }
     *
     *   function onSelectClicked(&$widget) {
     *     $this->cat->info('The widget', $widget->get_name(), 'was clicked');
     *   }
     * </code>
     *
     * @access  protected
     * @param   &php.GtkWidget widget
     * @param   string signal
     * @param   string callback default NULL
     * @param   mixed data default NULL
     * @return  &php.GtkWidget widget
     * @throws  gui.GuiException in case the signal connecting failed
     */
    function &connect(&$widget, $signal, $callback= NULL, $data= NULL) {
      if (!$widget) return FALSE;
      if ('after:' == substr($signal, 0, 6)) {
        $signal= substr($signal, 6);
        $func= 'connect_after';
      } else {
        $func= 'connect';
      }

      if (!$widget->{$func}(
        $signal, 
        array(&$this, $callback ? $callback : 'on'.$widget->get_name().$signal),
        $data
      )) {
        return throw(new GuiException('Connecting "'.$widget->get_name().'.'.$signal.'" failed'));
      }
      
      return $widget;
    }

    /**
     * Creates the main window
     *
     * @access  protected
     */
    function create() {
      $this->window= &new GtkWindow();
    }

    /**
     * Initializes the application
     *
     * @model   abstract
     * @access  public
     */
    function init() { }

    /**
     * Is called after the application comes down. Include cleanup
     * code in here.
     *
     * @model   abstract
     * @access  public
     */
    function done() { }

    /**
     * Shows application window and enters main loop.
     *
     * @access  public
     */    
    function run() {
      $this->window->show_all();
      Gtk::main();
    }
    
    /**
     * Callback for when the application is to be closed.
     *
     * @access  public
     */       
    function destroy() {
      Gtk::main_quit();
    }
    
    /**
     * Process events
     *
     * @access  public
     * @see     http://gtk.php.net/manual/en/gtk.method.events_pending.php
     */
    function processEvents() {
      while (Gtk::events_pending()) Gtk::main_iteration();
    }
  }
?>
