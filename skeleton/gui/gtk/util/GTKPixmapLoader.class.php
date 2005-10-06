<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  /**
   * Pixmap loader
   * 
   * Pixmaps will be loaded into a struct like this:
   * <pre>
   *   ['p:open'] => pixmap_of_open_pixmap,
   *   ['m:open'] => mask_of_open_pixmap,
   *   ['p:quit'] => pixmap_of_quit_pixmap,
   *   ['m:quit'] => mask_of_quit_pixmap
   * </pre>
   *
   * Transparent color will default to black (RGB {0, 0, 0})
   *
   * @test     xp://net.xp_framework.unittest.runner.gtk.UnitTestUI
   * @purpose  Pixmap loader
   */
  class GTKPixmapLoader extends Object {
    var 
      $windowRef,
      $baseDir,
      $transparentColor;

    /**
     * Constructor
     *
     * @access  public
     * @param   GtkWindow window a valid window object
     * @param   string baseDir default '.' base directory for pixmaps
     */      
    function __construct(&$window, $baseDir= '.') {
      $this->setWindowRef($window);
      $this->setBase($baseDir);
      $this->setTransparentColor(new GdkColor(0, 0, 0));
      
    }
    
    /**
     * Sets window reference
     *
     * @access  public
     * @param   GtkWindow window a valid window object
     */
    function setWindowRef(&$window) {
      $this->windowRef= &$window;
    }
    
    /**
     * Sets basedir for pixmaps
     *
     * @access  public
     * @param   string base base directory
     */
    function setBase($base) {
      $this->baseDir= $base;
    }
    
    /**
     * Sets transparent color
     *
     * @access  public
     * @param   GdkColor color the color to be transparent
     */
    function setTransparentColor(&$color) {
      $this->transparentColor= &$color;
    }
    
    /**
     * Loads a pixmap into a container
     *
     * @access  private
     * @param   &array container
     * @param   string name
     */
    function _load(&$container, $name) {
      list(
        $container['p:'.$name],
        $container['m:'.$name]
      )= Gdk::pixmap_create_from_xpm(
        $this->windowRef, 
        $this->transparentColor,
        $this->baseDir.DIRECTORY_SEPARATOR.$name.'.xpm'
      );
    }
    
    /**
     * Loads one or more pixmaps
     *
     * @access  public
     * @param   mixed names Either a string or an array of strings containinig the 
     *          names of the pixmaps to be loaded (w/o trailing .xpm!)
     * @return  &array pixmaps
     */
    function &load($names) {
      $container= array();
      if (is_string($names)) $names= array($names);
      foreach ($names as $name) {
        $this->_load($container, $name);
      }
      
      return $container;
    }
  }
?>
