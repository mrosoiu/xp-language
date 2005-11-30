<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'scriptlet.xml.workflow.AbstractState',
    'de.uska.db.Player'
  );

  /**
   * Base state for all uska states.
   *
   * @purpose  Base state
   */
  class UskaState extends AbstractState {
    var
      $cat=     NULL,
      $db=      NULL;
      
    /**
     * Constructor.
     *
     * @access  public
     */
    function __construct() {
      $log= &Logger::getInstance();
      $this->cat= &$log->getCategory();
    }
    
    /**
     * Setup this state. Sets up database connection and redirects
     * to login form in case the state needs an authenticated user.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @param   &scriptlet.xml.Context context
     */
    function setup(&$request, &$response, &$context) {
    
      // Automatically handle authentication if state indicates so
      if ($this->requiresAuthentication()) {
        if (!is('de.uska.db.Player', $context->user)) {

          // Store return point in session
          $uri= &$request->getURI();
          $request->session->putValue('authreturn', $uri);

          // Send redirect
          $response->sendRedirect(sprintf(
            '%s://%s/xml/%s.%s%s/%s%s%s',
            $uri['scheme'],
            $uri['host'],
            $request->getProduct(),
            $request->getLanguage(),
            '.psessionid='.$request->getSessionId(),
            'login',                                            // Authenticate state
            empty($uri['query']) ? '' : '?'.$uri['query'],
            empty($uri['fraction']) ? '' : '#'.$uri['fraction']        
          ));
          
          return FALSE;
        }
      }
    
      $cm= &ConnectionManager::getInstance();
      $this->db= &$cm->getByHost($request->getProduct(), 0);
      parent::setup($request, $response, $context);
    }
  
    /**
     * Insert all teams into the result tree.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     */
    function insertTeams(&$request, &$response) {
      $pm= &PropertyManager::getInstance();
      $prop= &$pm->getProperties('product');
      
      try(); {
        $teams= $this->db->select('
            team_id,
            name
          from
            team
          where team_id in (%d)',
          $prop->readArray($request->getProduct(), 'teams')
        );
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      
      $response->addFormResult(Node::fromArray($teams, 'teams'));
    }
    
    /**
     * Insert event calendar into result tree.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     */
    function insertEventCalendar(&$request, &$response, $team= NULL, $contextDate= NULL) {
      $pm= &PropertyManager::getInstance();
      $prop= &$pm->getProperties('product');
      
      if (!$contextDate) $contextDate= &Date::now();
      
      $month= &$response->addFormResult(new Node('month', NULL, array(
        'num'   => $contextDate->getMonth(),    // Month number, e.g. 4 = April
        'year'  => $contextDate->getYear(),     // Year
        'days'  => $contextDate->toString('t'), // Number of days in the given month
        'start' => (date('w', mktime(            // Week day of the 1st of the given month
          0, 0, 0, $contextDate->getMonth(), 1, $contextDate->getYear()
        )) + 6) % 7
      )));

      try(); {
        $calendar= &$this->db->query('
          select
            dayofmonth(target_date) as day,
            count(*) as numevents
          from
            event as e
          where year(target_date) = %d
            and month(target_date) = %d
            %c
          group by day',
          $contextDate->getYear(),
          $contextDate->getMonth(),
          ($team ? $this->db->prepare('and team_id= %d', $team) : '')
        );
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      
      while ($record= &$calendar->next()) {
        $month->addChild(new Node('entries', $record['numevents'], array(
          'day' => $record['day']
        )));
      }
    }
  }
?>
