<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Observable - base class for Model/View/Controller architecture.
   *
   * A basic implementation might look like this:
   *
   * TextObserver class:
   * <code>
   *   class TextObserver extends Object {
   *
   *     function update(&$obs, $arg= NULL) {
   *       echo __CLASS__, ' was notified of update in value, is now ';
   *       var_dump($obs->getValue());
   *     }
   *
   *   } implements(__FILE__, 'util.Observer');
   * </code>
   *
   * ObservableValue class:
   * <code>
   *   uses('util.Observable');
   *
   *   class ObservableValue extends Observable {
   *     var
   *       $n    = 0;
   *     
   *     function __construct($n) {
   *       $this->n= $n;
   *     }
   *     
   *     function setValue($n) {
   *       $this->n= $n;
   *       self::setChanged();
   *       self::notifyObservers();
   *     }
   *     
   *     function getValue() {
   *       return $this->n;
   *     }
   *   }
   * </code>
   *
   * Main program:
   * <code>
   *   uses('de.thekid.util.TextObserver', 'de.thekid.util.ObservableValue');
   *
   *   $value= &new ObservableValue(3);
   *   $value->addObserver(new TextObserver());
   *   $value->setValue(5);
   * </code>
   *
   * The update method gets passed the instance of Observable as its first
   * argument and - if existant - the argument passed to notifyObservers as 
   * its second.
   *
   * @see      http://www.javaworld.com/javaworld/jw-10-1996/jw-10-howto.html
   * @purpose  Base class
   */
  class Observable extends Object {
    var
      $_obs      = array(),
      $_changed  = FALSE;
      
    /**
     * Add an observer
     *
     * @access  public
     * @param   &util.Observer observer a class implementing the util.Observer interface
     * @throws  lang.IllegalArgumentException in case the argument is not an observer
     */
    function addObserver(&$observer) {
      if (!is('util.Observer', $observer)) {
        return throw(new IllegalArgumentException('Passed argument is not an util.Observer'));
      }
      $this->_obs[]= &$observer;
    }
    
    /**
     * Notify observers
     *
     * @access  public
     * @param   mixed arg default NULL
     */
    function notifyObservers($arg= NULL) {
      if (!$this->hasChanged()) return;
      
      for ($i= 0, $s= sizeof($this->_obs); $i < $s; $i++) {
        $this->_obs[$i]->update($this, $arg);
      }
      
      $this->clearChanged();
      unset($arg);
    }
    
    /**
     * Sets changed flag
     *
     * @access  protected
     */
    function setChanged() {
      $this->_changed= TRUE;
    }

    /**
     * Clears changed flag
     *
     * @access  protected
     */
    function clearChanged() {
      $this->_changed= FALSE;
    }

    /**
     * Checks whether changed flag is set
     *
     * @access  public
     * @return  bool
     */
    function hasChanged() {
      return $this->_changed;
    }
  }
?>
