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
     * @return  &.xml.XSLProcessor
     */
    protected function _processor() {
      return new DomXSLProcessor();
    }

    /**
     * Sets the responses XSL stylesheet
     *
     * @param   &scriptlet.scriptlet.XMLScriptletRequest request
     * @param   &scriptlet.scriptlet.XMLScriptletResponse response
     */
    protected function _setStylesheet($request, $response) {
      $response->setStylesheet(sprintf(
        '%s/%s.xsl',
        $request->getProduct(),
        $request->getStateName()
      ));
    }
  }
?>
