<?php
/* This class is part of the XP framework
 *
 * $Id$ 
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
    public function process(&$request, &$response) {
    }
  }
?>
