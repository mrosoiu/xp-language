<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'de.uska.scriptlet.state.UskaState',
    'de.uska.db.Event'
  );

  /**
   * View details for an event
   *
   * @purpose  View event
   */
  class ViewEventState extends UskaState {
    
    /**
     * Process this state.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @param   &scriptlet.xml.Context context
     * @return  boolean
     */
    function process(&$request, &$response, &$context) {
      parent::process($request, $response, $context);
      
      $eventid= intval($request->getEnvValue('QUERY_STRING'));
      if (!$eventid) return FALSE;
      
      try(); {
        $event= &Event::getByEvent_id($eventid);
        
        $event && $query= &$this->db->query('
          select
            p.player_id,
            p.firstname,
            p.lastname,
            a.offers_seats,
            a.needs_driver,
            a.attend
          from
            event as e,
            player_team_matrix as ptm,
            player as p left outer join event_attendee as a on p.player_id= a.player_id and a.event_id= e.event_id
          where p.player_id= ptm.player_id
            and ptm.team_id= e.team_id
            and e.event_id= %d
          ',
          $event->getEvent_id()
        );
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      
      $node= &$response->addFormResult(Node::fromObject($event, 'event'));
      $n= &$node->addChild(new Node('attendeeinfo'));
      while ($query && $record= &$query->next()) {
        $n->addChild(new Node('player', NULL, $record));
      }
    }
  }
?>
