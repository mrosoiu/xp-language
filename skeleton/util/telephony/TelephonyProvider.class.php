<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'util.telephony.TelephonyAddress', 
    'util.telephony.TelephonyAddressParser',    
    'util.telephony.TelephonyTerminal',
    'util.telephony.TelephonyCall',
    'util.telephony.TelephonyException'
  );

  /**
   * Abstract base class for a telephony provider
   * 
   * Example (using the STLI driver):
   * <code>
   *   uses(
   *     'ch.ecma.StliConnection', 
   *     'peer.Socket', 
   *     'util.log.Logger',
   *     'util.log.FileAppender',
   *     'util.cmd.ParamString'
   *   );
   * 
   *   $p= &new ParamString();
   *   if (4 != $p->count) {
   *     printf("Usage: %s server:port <from> <to>\n", basename($p->value(0)));
   *     exit();
   *   }
   *   list($server, $port)= explode(':', $p->value(1));
   * 
   *   $l= &Logger::getInstance();
   *   $cat= &$l->getCategory();
   *   $cat->addAppender(new FileAppender('php://stderr'));
   * 
   *   $c= &new StliConnection(new Socket($server, $port));
   *   $c->setTrace($cat);
   *   try(); {
   *     $c->connect();
   *     $term= &$c->getTerminal($c->getAddress($p->value(2)));
   *     $call= &$c->createCall($term, $c->getAddress($p->value(3)));
   *     $c->releaseTerminal($term);
   *     $c->close();
   *   } if (catch('Exception', $e)) {
   *     $e->printStackTrace();
   *     exit;
   *   }
   * 
   *   printf("Done\n");
   * </code>
   *
   * @purpose  Provides an interface to telephony
   * @see      http://java.sun.com/products/jtapi/jtapi-1.3/html/overview-summary.html
   */
  class TelephonyProvider extends Object {
    var
      $cat  = NULL;
    
    /**
     * Set a LogCategory for tracing communication
     *
     * @access  public
     * @param   &util.log.LogCategory cat a LogCategory object to which communication
     *          information will be passed to or NULL to stop tracing
     * @return  &util.log.LogCategory
     * @throws  lang.IllegalArgumentException in case a of a type mismatch
     */
    function &setTrace(&$cat) {
      if (NULL !== $cat && !is('LogCategory', $cat)) {
        return throw(new IllegalArgumentException('Argument passed is not a LogCategory'));
      }
      
      $this->cat= &$cat;
    }
    
    /**
     * Trace function
     *
     * @access  protected
     * @param   mixed* arguments
     */
    function trace() {
      if (!$this->cat) return;

      $args= func_get_args();
      call_user_func_array(array($this->cat, 'debug'), $args);
    }
    
    /**
     * Connect and initiate the communication
     *
     * @model   abstract
     * @access  public
     */
    function connect() { }

    /**
     * Close connection and end the communication
     *
     * @access  public
     */
    function close() { }
    
    /**
     * Retrieve an address
     *
     * @access  public  
     * @param   string number
     * @return  &util.telephony.TelephonyAddress 
     */
    function &getAddress($number) { 
      return new TelephonyAddress($number);
    }
    
    /**
     * Create a call
     *
     * @model   abstract
     * @access  public
     * @param   &util.telephony.TelephonyTerminal terminal
     * @param   &util.telephony.TelephonyAddress destination
     * @return  &util.telephony.TelephonyCall a call object
     */
    function &createCall(&$terminal, &$destination) { }
    
    /**
     * Get terminal
     *
     * @model   abstract
     * @access  public
     * @param   &util.telephony.TelephonyAddress address
     * @return  &util.telephony.TelephonyTerminal
     */
    function &getTerminal(&$address) { }
    
    /**
     * Release terminal
     *
     * @model   abstract
     * @access  public
     * @param   &util.telephony.TelephonyTerminal terminal
     * @return  bool success
     */
    function releaseTerminal(&$terminal) { }

  } implements(__FILE__, 'util.log.Traceable');
?>
