<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'de.uska.scriptlet.state.UskaState',
    'de.uska.scriptlet.handler.LoginHandler'
  );
  
  /**
   * Login state.
   *
   * @purpose  Provide login form
   */
  class LoginState extends UskaState {

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function __construct() {
      $this->addHandler(new LoginHandler());
    }
    
    /**
     * Setup the state
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @param   &scriptlet.xml.Context context
     */
    function setup(&$request, &$response, &$context) {
      parent::setup($request, $response, $context);
    }
  }
?>
