<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('scriptlet.xml.workflow.checkers.ParamChecker');

  /**
   * Checks given values for string length
   *
   * Error codes returned are:
   * <ul>
   *   <li>size_exceeded - The uploaded file exceeds specified size.</li>
   *   <li>partial - The uploaded file was only partially uploaded.</li>
   *   <li>nofile - No file was uploaded.</li>
   *   <li>unknown - Any other error</li>
   * </ul>
   *
   * @purpose  Checker
   */
  class FileUploadPrechecker extends ParamChecker {

    /**
     * Check a given value
     *
     * @access  public
     * @param   array value
     * @return  string error or NULL on success
     */
    function check($value) { 
      switch ($value['error']) {
        case UPLOAD_ERR_OK: return;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return 'size_exceeded';
        case UPLOAD_ERR_PARTIAL: return 'partial';
        case UPLOAD_ERR_NO_FILE: return 'nofile';
        default: return 'unknown';

      }    
    }
  }
?>
