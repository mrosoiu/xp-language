<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractState',
    'io.Folder'
  );

  /**
   * Handles /xml/lookup
   *
   * @purpose  State
   */
  class LookupState extends AbstractState {

    /**
     * Process this state. Redirects to known targets or invokes the 
     * search if the lookup fails.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     */
    function process(&$request, &$response) {
      with ($query= $request->getParam('q', $request->getData()), $uri= $request->getURI()); {
        $target= 'search?q='.$query;
        
        // Use cache directory
        $query= basename($query);
        $folder= &new Folder('../build/cache/');
        while ($entry= &$folder->getEntry()) {
          $fn= $folder->getURI().$entry.DIRECTORY_SEPARATOR;

          if (!is_dir($fn)) continue;
          if (
            file_exists($fn.'class'.DIRECTORY_SEPARATOR.$query) ||
            file_exists($fn.'sapi'.DIRECTORY_SEPARATOR.$query)
          ) {
            $target= 'documentation/class?'.$entry.'/'.$query;
            break;
          }
        }
        
        $response->sendRedirect(sprintf(
          '%s://%s/xml/%s.%s%s/%s', 
          $uri['scheme'],
          $uri['host'],          
          $request->getProduct(),
          $request->getLanguage(),
          $request->session ? '.psessionid='.$request->session->getId() : '',
          $target
        ));
        return FALSE; // Indicate no further processing is to be done
      }
    }
  }
?>
