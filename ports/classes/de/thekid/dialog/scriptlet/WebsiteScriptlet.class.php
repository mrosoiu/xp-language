<?php
/* This class is part of the XP framework's port "Dialog"
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractXMLScriptlet', 
    'xml.DomXSLProcessor'
  );

  /**
   * Website scriptlet for the Album port
   *
   * @see      http://album.friebes.info/
   * @purpose  Scriptlet
   */
  class WebsiteScriptlet extends AbstractXMLScriptlet {

    /**
     * Set our own processor object
     *
     * @access  protected
     * @return  &.xml.XSLProcessor
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
  }
?>
