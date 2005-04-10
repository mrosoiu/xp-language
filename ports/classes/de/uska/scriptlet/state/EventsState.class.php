<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('de.uska.scriptlet.state.UskaState');

  /**
   * (Insert class' description here)
   *
   * @ext      extension
   * @see      reference
   * @purpose  purpose
   */
  class EventsState extends UskaState {
  
    /**
     * Process this state.
     *
     * @access  public
     * @param   &scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   &scriptlet.xml.XMLScriptletResponse response 
     * @param   &scriptlet.xml.Context context
     */
    function process(&$request, &$response, &$context) {
      static $types= array(
        'training'    => 1,
        'tournament'  => 2
      );
      parent::process($request, $response, $context);
      
      try(); {
        $team= FALSE;
        $type= FALSE;
        with ($env= $request->getEnvValue('QUERY_STRING')); {
          if (strlen($env)) list($type, $team)= explode(',', $env);
        }
        
        $events= $this->db->select('
            e.event_id,
            e.team_id,
            t.name as teamname,
            e.name,
            e.description,
            e.target_date,
            e.deadline,
            e.max_attendees,
            e.req_attendees,
            e.allow_guests,
            e.event_type_id,
            e.lastchange,
            e.changedby
          from
            event as e,
            team as t
          where t.team_id= e.team_id
            %c
            %c',
          ($team ? $this->db->prepare('and e.team_id= %d', $team) : ''),
          ($type ? $this->db->prepare('and e.event_type_id= %d', $types[$type]) : '')
        );
      } if (catch('SQLException', $e)) {
        return throw($e);
      }
      
      $this->insertEventCalendar($request, $response, $team);
      $response->addFormResult(Node::fromArray($events, 'events'));
    }
  }
?>
