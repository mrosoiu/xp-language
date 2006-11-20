<?php
/* This class is part of the XP framework
 *
 * $Id: DocumentationState.class.php 5901 2005-10-04 20:39:40Z friebe $ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractState',
    'net.xp_framework.db.caffeine.XPNews'
  );

  /**
   * Handles /xml/documentation
   *
   * @purpose  State
   */
  class InheritanceDocumentationState extends AbstractState {

    /**
     * Process this state.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     */
    function process(&$request, &$response) {
    }
  }
?>
