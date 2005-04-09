<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractXMLScriptlet',
    'xml.DomXSLProcessor');

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class UskaScriptlet extends AbstractXMLScriptlet {
  
    /**
     * Set our own processor object
     *
     * @access  protected
     * @return  &xml.XSLProcessor
     */
    function &_processor() {
      return new DomXSLProcessor();
    }
    
    /**
     * Sets the responses XSL stylesheet
     *
     * @access  protected
     * @param   &scriptlet.scriptlet.XMLScriptletRequest request
     * @param   &scriptlet.scriptlet.XMLScriptletResponse response
     */
    function _setStylesheet(&$request, &$response) {
      $response->setStylesheet(sprintf(
        '%s/%s.xsl',
        $request->getProduct(),
        $request->getStateName()
      ));
    }
    
    /**
     * Decide whether a context is needed. Whenever a session is required
     * we also need a context.
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @return  bool
     */
    function wantsContext(&$request) {
      return $this->needsSession($request) || $request->hasSession();
    }
  }
?>
