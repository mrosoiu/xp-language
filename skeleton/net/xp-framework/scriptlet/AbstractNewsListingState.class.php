<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'net.xp-framework.util.markup.FormresultHelper',
    'scriptlet.xml.workflow.AbstractState',
    'rdbms.ConnectionManager',
    'util.Date'
  );

  /**
   * Base class for all news listing states
   *
   * @purpose  Abstract base class
   */
  class AbstractNewsListingState extends AbstractState {

    /**
     * Retrieve entries
     *
     * @model   abstract
     * @access  protected
     * @param   &rdbms.DBConnection db
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @return  &rdbms.ResultSet
     */
    function &getEntries(&$db, &$request) { }
    
    /**
     * Return date
     *
     * @access  protected
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @return  &util.Date
     */
    function &getContextMonth(&$request) {
      return Date::now();
    }
    
    /**
     * Process this state.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @param   &scriptlet.xml.XMLScriptletResponse response
     */
    function process(&$request, &$response) {

      // Retrieve date information
      $contextDate= &$this->getContextMonth($request);
      $month= &$response->addFormResult(new Node('month', NULL, array(
        'num'   => $contextDate->getMonth(),    // Month number, e.g. 4 = April
        'year'  => $contextDate->getYear(),     // Year
        'days'  => $contextDate->toString('t'), // Number of days in the given month
        'start' => (date('w', mktime(            // Week day of the 1st of the given month
          0, 0, 0, $contextDate->getMonth(), 1, $contextDate->getYear()
        )) + 6) % 7
      )));

      $cm= &ConnectionManager::getInstance();
      try(); {
        $db= &$cm->getByHost('news', 0);
        
        // Add all categories to the formresult
        $n= &$response->addFormResult(new Node('categories'));
        $q= &$db->query('select categoryid, category_name from serendipity_category');
        while ($record= $q->next()) {
          $n->addChild(new Node('category', $record['category_name'], array(
            'id' => $record['categoryid']
          )));
        }
        
        // Fill in all days for which an entry exists
        $q= &$db->query('
          select 
            dayofmonth(from_unixtime(entry.timestamp)) as day, 
            count(*) as numentries
          from 
            serendipity_entries entry 
          where 
            year(from_unixtime(entry.timestamp)) = %d 
            and month(from_unixtime(entry.timestamp)) = %d 
          group by day',
          $contextDate->getYear(),
          $contextDate->getMonth()
        );
        while ($record= $q->next()) {
          $month->addChild(new Node('entries', $record['numentries'], array(
            'day' => $record['day']
          )));
        }
        
        // Call the getEntries() method (which is overridden by subclasses
        // and returns the corresponding entries)
        $q= &$this->getEntries($db, $request);
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      
      $n= &$response->addFormResult(new Node('entries'));
      while ($record= $q->next()) {
        with ($entry= &$n->addChild(new Node('entry'))); {
          $entry->setAttribute('id', $record['id']);
          $entry->addChild(new Node('title', $record['title']));
          $entry->addChild(new Node('author', $record['author']));
          $entry->addChild(new Node('extended_length', $record['extended_length']));
          $entry->addChild(Node::fromObject(new Date($record['timestamp']), 'date'));
          $entry->addChild(FormresultHelper::markupNodeFor('body', $record['body']));
        }
      }
      return TRUE;
    }
  }
?>
