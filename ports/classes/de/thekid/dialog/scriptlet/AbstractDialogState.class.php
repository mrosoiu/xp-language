<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractState',
    'io.FileUtil',
    'io.File',
    'de.thekid.dialog.Album',
    'de.thekid.dialog.Update',
    'de.thekid.dialog.SingleShot',
    'de.thekid.dialog.EntryCollection',
    'util.PropertyManager'
  );

  /**
   * Abstract base class
   *
   * @purpose  Base class
   */
  class AbstractDialogState extends AbstractState {
    var
      $dataLocation = '';
    
    /**
     * Returns index
     *
     * @access  protected
     * @param   int i default 0 page number
     * @return  string[]
     */
    function getIndexPage($i= 0) {
      try(); {
        $index= unserialize(FileUtil::getContents(new File($this->dataLocation.'page_'.$i.'.idx')));
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      return $index;
    }
    
    /**
     * Retrieves which page a given element is on
     *
     * @access  public
     * @param   string name
     * @return  int
     */
    function getDisplayPageFor($name) {
      try(); {
        $page= unserialize(FileUtil::getContents(new File($this->dataLocation.$name.'.idx')));
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      return $page;
    }
    
    /**
     * Helper method
     *
     * @access  protected
     * @param   string name
     * @param   string expect expected type
     * @return  &de.thekid.dialog.IEntry
     * @throws  lang.IllegalArgumentException if found entry is not of expected type
     */
    function &_getEntryFor($name, $expect) {
      try(); {
        $entry= &unserialize(FileUtil::getContents(new File($this->dataLocation.$name.'.dat')));
      } if (catch('IOException', $e)) {
        return throw($e);
      }

      // Check expectancy
      if (!is($expect, $entry)) return throw(new IllegalArgumentException(sprintf(
        'Entry of type %s found, %s expected',
        xp::typeOf($entry),
        $expect
      )));

      return $entry;
    }

    /**
     * Returns entry for a specified name
     *
     * @access  protected
     * @param   string name
     * @return  &de.thekid.dialog.IEntry
     */
    function &getEntryFor($name) {
      return $this->_getEntryFor($name, 'de.thekid.dialog.IEntry');
    }

    /**
     * Returns album for a specified name
     *
     * @access  protected
     * @param   string name
     * @return  &de.thekid.dialog.Album
     * @throws  lang.IllegalArgumentException if it is not an album
     */
    function &getAlbumFor($name) {
      return $this->_getEntryFor($name, 'de.thekid.dialog.Album');
    }
    
    /**
     * Set up this state
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     * @param   &scriptlet.xml.Context context
     */
    function setup(&$request, &$response, &$context) {
      parent::setup($request, $response, $context);
      
      // Read configuration
      $pm= &PropertyManager::getInstance();
      with ($prop= &$pm->getProperties($request->getProduct())); {
        $this->dataLocation= $prop->readString(
          'data',
          'location',
          $request->getEnvValue('DOCUMENT_ROOT').'/../data/'
        );
        
        $response->addFormresult(Node::fromArray($prop->readSection('general'), 'config'));
      }
    }  
  }
?>
