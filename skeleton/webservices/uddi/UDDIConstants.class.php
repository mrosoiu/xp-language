<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  // UDDI namespaces
  define('UDDI_NAMESPACE_V1',   'urn:uddi-org:api');
  define('UDDI_NAMESPACE_V2',   'urn:uddi-org:api_v2');
  define('UDDI_NAMESPACE_V3',   'urn:uddi-org:api_v3:0');

  // UDDI versions
  define('UDDI_V1',             '1.0');
  define('UDDI_V2',             '2.0');
  define('UDDI_V3',             '3.0');

  // Find qualifiers
  define('SORT_BY_DATE_ASC',    'sortByDateAsc');
  define('SORT_BY_DATE_DESC',   'sortByDateDesc');
  define('SORT_BY_NAME_ASC',    'sortByNameAsc');
  define('SORT_BY_NAME_DESC',   'sortByNameDesc');

  /**
   * UDDI constants
   *
   * @purpose  Constants and utility methods for these
   */
  class UDDIConstants extends Object {

    /**
     * Retrieve namespace for a specified version
     *
     * @model   static
     * @access  public
     * @param   int version
     * @return  string namespace
     */  
    function namespaceFor($version) {
      return constant('UDDI_NAMESPACE_V'.$version);
    }

    /**
     * Retrieve version identifier for a specified version
     *
     * @model   static
     * @access  public
     * @param   int version
     * @return  string version identifier, e.g. "1.0"
     */  
    function versionIdFor($version) {
      return constant('UDDI_V'.$version);
    }
  }
?>
