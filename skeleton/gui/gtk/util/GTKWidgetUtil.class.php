<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Widget utility class
   *
   * @model static
   */
  class GTKWidgetUtil extends Object {
  
    /**
     * Connect a list of signals for a widget
     *
     * Example:
     * <pre>
     *   GTKWidgetUtil::connect($w, array(
     *     'after:button_press_event' => array(&$this, 'onButtonPressed'),
     *     ':clicked'                 => array(&$this, 'onButtonClicked', &$data)
     *   ));
     * </pre>
     *
     * @access  public
     * @param   &GtkWidget widget
     * @param   array signals an associative array
     * @see     php-gtk://GtkObject%3A%3Aconnect
     * @see     php-gtk://GtkObject%3A%3Aconnect_after
     */
    function connect(&$widget, $signals) {
      foreach (array_keys($signals) as $key) {
        list($mode, $signal)= explode(':', $key);
        
        switch (sizeof($signals[$key])) {
          case 1:                           // 'function'
          case 2:                           // array(&$this, 'function')
            $handler= &$signals[$key]; 
            $val= NULL; 
            break;
            
          case 3:                           // array(&$this, 'function', &$custom)
            $handler= array(&$signals[$key][0], &$signals[$key][1]);
            $val= &$signals[$key][2];  
            break;
        }
        
        switch ($mode) {
          case 'after':
            $widget->connect_after($signal, $handler, $val);
            break;
          default:
            $widget->connect($signal, $handler, $val);
            break;
        }
      }
    }
    
    /**
     * Connects a list of signals to a widget's children 
     *
     * @access  public
     * @param   &GtkWidget widget
     * @see     #connect
     */
    function connectChildren(&$widget, $signals) {
      foreach ($widget->children() as $child) {
        GTKWidgetUtil::connect($child, $signals);
      }
    }
    
    function setChildrenSensitive(&$widget, $sensitivity) {
      foreach ($widget->children() as $child) {
        if (!isset($sensitivity[$name= $child->get_name()])) continue;
        $child->set_sensitive($sensitivity[$name]);
      }    
    }
  }
?>
