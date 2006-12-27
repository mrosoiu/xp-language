<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'scriptlet.xml.workflow.Wrapper',
    'scriptlet.xml.workflow.casters.ToFileData',
    'scriptlet.xml.workflow.checkers.FileUploadPrechecker'
  );

  /**
   * Wrapper for NewPageHandler
   * Handler
   * 
   * @see      xp://name.kiesel.pxl.scriptlet.handler.NewPageHandler
   * @purpose  Wrapper
   */
  class NewPageWrapper extends Wrapper {

    /**
     * Constructor
     *
     */  
    public function __construct() {
      $this->registerParamInfo(
        'name',
        OCCURRENCE_UNDEFINED,
        NULL,
        NULL,
        NULL,
        NULL
      );
      $this->registerParamInfo(
        'description',
        OCCURRENCE_UNDEFINED,
        NULL,
        NULL,
        NULL,
        NULL
      );
      $this->registerParamInfo(
        'file',
        OCCURRENCE_UNDEFINED,
        NULL,
        array('scriptlet.xml.workflow.casters.ToFileData'),
        array('scriptlet.xml.workflow.checkers.FileUploadPrechecker'),
        NULL
      );
      $this->registerParamInfo(
        'online',
        OCCURRENCE_UNDEFINED,
        NULL,
        NULL,
        NULL,
        NULL
      );
      $this->registerParamInfo(
        'tags',
        OCCURRENCE_OPTIONAL,
        NULL,
        NULL,
        NULL,
        NULL
      );
    }

    /**
     * Returns the value of the parameter name
     *
     * @return  string
     */
    public function getName() {
      return $this->getValue('name');
    }

    /**
     * Returns the value of the parameter description
     *
     * @return  string
     */
    public function getDescription() {
      return $this->getValue('description');
    }

    /**
     * Returns the value of the parameter file
     *
     * @return  string
     */
    public function getFile() {
      return $this->getValue('file');
    }

    /**
     * Returns the value of the parameter online
     *
     * @return  boolean
     */
    public function getOnline() {
      return $this->getValue('online');
    }

    /**
     * Returns the value of the parameter tags
     *
     * @return  string
     */
    public function getTags() {
      return $this->getValue('tags');
    }

  }
?>
