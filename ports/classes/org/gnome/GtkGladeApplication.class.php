<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('org.gnome.GtkApplication');

  /**
   * GTK Application class using glade
   *
   * @ext      gtk
   * @ext      gtk.glade
   * @see      xp://org.gnome.GtkApplication
   * @see      http://glade.gnome.org/
   * @test     xp://net.xp_framework.unittest.runner.gtk.UnitTestUI
   * @purpose  Base class
   */  
  class GtkGladeApplication extends GtkApplication {
    var 
      $glade        = NULL,
      $mainwin      = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   &util.cmd.ParamString paramstring
     * @param   string gladefile location of the .glade-file
     * @param   string mainwin default 'window1'
     */
    function __construct(&$p, $gladefile, $mainwin= 'window1') {
      if (!$this->glade= &new GladeXML($gladefile)) {
        return throw(new GuiException('Cannot read glade file '.$gladefile));
      }
      $this->mainwin= $mainwin;
      parent::__construct($p);
    }

    /**
     * Returns a widget from the glade file
     *
     * @access  protected
     * @param   string name
     * @return  &php.GtkWidget
     */
    function &widget($name) {
      if (!$this->glade || !$w= &$this->glade->get_widget($name)) {
        return throw(new WidgetNotFoundException($name));
      } 
      return $w;
    }

    /**
     * Creates the main window
     *
     * @access  protected
     */
    function create() {
      $this->window= &$this->widget($this->mainwin);
    }
  }
?>
