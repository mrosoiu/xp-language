<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  // Defines for menus
  define('MENU_WANT_LEFTCLICK',     1);
  define('MENU_WANT_RIGHTCLICK',    3);

  /**
   * Menu
   *
   * @purpose  Wrapper for GtkMenu
   */
  class GtkMenuWidget extends Object {
    var
      $menu   = NULL;
    
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct() {
      $this->menu= &new GtkMenu();
    }

    /**
     * Add a new menu entry
     *
     * @access  public
     * @param   string menustring
     * @param   string callback
     * @return  &GtkMenuItem
     */    
    function &addMenuItem($string, $callback) {
      $item= &new GtkMenuItem ($string);
      $this->menu->append ($item);
      $item->connect ('button_press_event', $callback);
      return $item;
    }
    
    /**
     * Add a menu separator
     *
     * @access  public
     * @return  &GtkMenuItem
     */
    function &addSeparator() {
      $s= &$this->addMenuItem ('', NULL);
      $s->set_sensitive (FALSE);
      return $s;
    }    
    
    /**
     * Shows the popup menu
     *
     * @access  public
     * @param   int button which button to click
     * @param   int time events time
     */    
    function show($button= MENU_WANT_LEFTCLICK, $time= 0) {
      $this->menu->show_all();

      $this->menu->popup (
        NULL,
        NULL,
        NULL,
        $button,
        $time
      );
    }
  }

?>
