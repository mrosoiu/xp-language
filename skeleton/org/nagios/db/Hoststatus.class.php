<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('rdbms.DataSet');
 
  /**
   * Class wrapper for table hoststatus, database nagios
   * (Auto-generated on Tue, 25 Nov 2003 12:50:31 +0100 by alex)
   *
   * @purpose  Datasource accessor
   */
  class Hoststatus extends DataSet {
    var
      $host_name= '',
      $host_status= '',
      $last_update= NULL,
      $last_check= NULL,
      $last_state_change= NULL,
      $problem_acknowledged= 0,
      $time_up= 0,
      $time_down= 0,
      $time_unreachable= 0,
      $last_notification= NULL,
      $current_notification= 0,
      $notifications_enabled= 0,
      $event_handler_enabled= 0,
      $checks_enabled= 0,
      $plugin_output= '',
      $flap_detection_enabled= 0,
      $is_flapping= 0,
      $percent_state_change= '',
      $scheduled_downtime_depth= 0,
      $failure_prediction_enabled= 0,
      $process_performance_data= 0;

    /**
     * Gets the service status by hostname
     *
     * @access  public
     * @param   string hostname
     * @return  &Hoststatus[] object
     * @throws  rdbms.SQLException in case an error occurs
     * @throws  lang.IllegalAccessException in case there is no suitable database connection available
     */
    function &getByHost_name($host_name) {
      $cm= &ConnectionManager::getInstance();  
      try(); {
        $db= &$cm->getByHost('nagios', 0);
        $q= &$db->query ('
          select
            host_name,
            host_status,
            last_update,
            last_check,
            last_state_change,
            problem_acknowledged,
            time_up,
            time_down,
            time_unreachable,
            last_notification,
            current_notification,
            notifications_enabled,
            event_handler_enabled,
            checks_enabled,
            plugin_output,
            flap_detection_enabled,
            is_flapping,
            percent_state_change,
            scheduled_downtime_depth,
            failure_prediction_enabled,
            process_performance_data
          from
            nagios.hoststatus
          where
            host_name= %s',
          $host_name);

        $data= array();
        if ($q) while ($r= &$q->next()) {
          $data[]= &new Hoststatus($r);
        }
      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return $data;
    }

    /**
     * Gets the service status by hostname
     *
     * @access  public
     * @param   string hoststatus
     * @return  &Hoststatus[] object
     * @throws  rdbms.SQLException in case an error occurs
     * @throws  lang.IllegalAccessException in case there is no suitable database connection available
     */
    function &getByHost_status($host_status) {
      $cm= &ConnectionManager::getInstance();  
      try(); {
        $db= &$cm->getByHost('nagios', 0);
        $q= &$db->query ('
          select
            host_name,
            host_status,
            last_update,
            last_check,
            last_state_change,
            problem_acknowledged,
            time_up,
            time_down,
            time_unreachable,
            last_notification,
            current_notification,
            notifications_enabled,
            event_handler_enabled,
            checks_enabled,
            plugin_output,
            flap_detection_enabled,
            is_flapping,
            percent_state_change,
            scheduled_downtime_depth,
            failure_prediction_enabled,
            process_performance_data
          from
            nagios.hoststatus
          where
            host_status= %s',
          $host_status);

        $data= array();
        if ($q) while ($r= &$q->next()) {
          $data[]= &new Hoststatus($r);
        }
      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return $data;
    }
    
    /**
     * Gets the service status by hostname
     *
     * @access  public
     * @return  &Hoststatus[] object
     * @throws  rdbms.SQLException in case an error occurs
     * @throws  lang.IllegalAccessException in case there is no suitable database connection available
     */
    function &getByNotUp() {
      $cm= &ConnectionManager::getInstance();  
      try(); {
        $db= &$cm->getByHost('nagios', 0);
        $q= &$db->query ('
          select
            host_name,
            host_status,
            last_update,
            last_check,
            last_state_change,
            problem_acknowledged,
            time_up,
            time_down,
            time_unreachable,
            last_notification,
            current_notification,
            notifications_enabled,
            event_handler_enabled,
            checks_enabled,
            plugin_output,
            flap_detection_enabled,
            is_flapping,
            percent_state_change,
            scheduled_downtime_depth,
            failure_prediction_enabled,
            process_performance_data
          from
            nagios.hoststatus
          where
            host_status != %s',
          'UP');

        $data= array();
        if ($q) while ($r= &$q->next()) {
          $data[]= &new Hoststatus($r);
        }
      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return $data;
    }
    
    /**
     * Retrieves host_name
     *
     * @access  public
     * @return  string
     */
    function getHost_name() {
      return $this->host_name;
    }
      
    /**
     * Sets host_name
     *
     * @access  public
     * @param   string host_name
     * @return  string previous value
     */
    function setHost_name($host_name) {
      return $this->_change('host_name', $host_name, '%s');
    }
      
    /**
     * Retrieves host_status
     *
     * @access  public
     * @return  string
     */
    function getHost_status() {
      return $this->host_status;
    }
      
    /**
     * Sets host_status
     *
     * @access  public
     * @param   string host_status
     * @return  string previous value
     */
    function setHost_status($host_status) {
      return $this->_change('host_status', $host_status, '%s');
    }
      
    /**
     * Retrieves last_update
     *
     * @access  public
     * @return  util.Date
     */
    function getLast_update() {
      return $this->last_update;
    }
      
    /**
     * Sets last_update
     *
     * @access  public
     * @param   util.Date last_update
     * @return  util.Date previous value
     */
    function setLast_update($last_update) {
      return $this->_change('last_update', $last_update, '%s');
    }
      
    /**
     * Retrieves last_check
     *
     * @access  public
     * @return  util.Date
     */
    function getLast_check() {
      return $this->last_check;
    }
      
    /**
     * Sets last_check
     *
     * @access  public
     * @param   util.Date last_check
     * @return  util.Date previous value
     */
    function setLast_check($last_check) {
      return $this->_change('last_check', $last_check, '%s');
    }
      
    /**
     * Retrieves last_state_change
     *
     * @access  public
     * @return  util.Date
     */
    function getLast_state_change() {
      return $this->last_state_change;
    }
      
    /**
     * Sets last_state_change
     *
     * @access  public
     * @param   util.Date last_state_change
     * @return  util.Date previous value
     */
    function setLast_state_change($last_state_change) {
      return $this->_change('last_state_change', $last_state_change, '%s');
    }
      
    /**
     * Retrieves problem_acknowledged
     *
     * @access  public
     * @return  int
     */
    function getProblem_acknowledged() {
      return $this->problem_acknowledged;
    }
      
    /**
     * Sets problem_acknowledged
     *
     * @access  public
     * @param   int problem_acknowledged
     * @return  int previous value
     */
    function setProblem_acknowledged($problem_acknowledged) {
      return $this->_change('problem_acknowledged', $problem_acknowledged, '%d');
    }
      
    /**
     * Retrieves time_up
     *
     * @access  public
     * @return  int
     */
    function getTime_up() {
      return $this->time_up;
    }
      
    /**
     * Sets time_up
     *
     * @access  public
     * @param   int time_up
     * @return  int previous value
     */
    function setTime_up($time_up) {
      return $this->_change('time_up', $time_up, '%d');
    }
      
    /**
     * Retrieves time_down
     *
     * @access  public
     * @return  int
     */
    function getTime_down() {
      return $this->time_down;
    }
      
    /**
     * Sets time_down
     *
     * @access  public
     * @param   int time_down
     * @return  int previous value
     */
    function setTime_down($time_down) {
      return $this->_change('time_down', $time_down, '%d');
    }
      
    /**
     * Retrieves time_unreachable
     *
     * @access  public
     * @return  int
     */
    function getTime_unreachable() {
      return $this->time_unreachable;
    }
      
    /**
     * Sets time_unreachable
     *
     * @access  public
     * @param   int time_unreachable
     * @return  int previous value
     */
    function setTime_unreachable($time_unreachable) {
      return $this->_change('time_unreachable', $time_unreachable, '%d');
    }
      
    /**
     * Retrieves last_notification
     *
     * @access  public
     * @return  util.Date
     */
    function getLast_notification() {
      return $this->last_notification;
    }
      
    /**
     * Sets last_notification
     *
     * @access  public
     * @param   util.Date last_notification
     * @return  util.Date previous value
     */
    function setLast_notification($last_notification) {
      return $this->_change('last_notification', $last_notification, '%s');
    }
      
    /**
     * Retrieves current_notification
     *
     * @access  public
     * @return  int
     */
    function getCurrent_notification() {
      return $this->current_notification;
    }
      
    /**
     * Sets current_notification
     *
     * @access  public
     * @param   int current_notification
     * @return  int previous value
     */
    function setCurrent_notification($current_notification) {
      return $this->_change('current_notification', $current_notification, '%d');
    }
      
    /**
     * Retrieves notifications_enabled
     *
     * @access  public
     * @return  int
     */
    function getNotifications_enabled() {
      return $this->notifications_enabled;
    }
      
    /**
     * Sets notifications_enabled
     *
     * @access  public
     * @param   int notifications_enabled
     * @return  int previous value
     */
    function setNotifications_enabled($notifications_enabled) {
      return $this->_change('notifications_enabled', $notifications_enabled, '%d');
    }
      
    /**
     * Retrieves event_handler_enabled
     *
     * @access  public
     * @return  int
     */
    function getEvent_handler_enabled() {
      return $this->event_handler_enabled;
    }
      
    /**
     * Sets event_handler_enabled
     *
     * @access  public
     * @param   int event_handler_enabled
     * @return  int previous value
     */
    function setEvent_handler_enabled($event_handler_enabled) {
      return $this->_change('event_handler_enabled', $event_handler_enabled, '%d');
    }
      
    /**
     * Retrieves checks_enabled
     *
     * @access  public
     * @return  int
     */
    function getChecks_enabled() {
      return $this->checks_enabled;
    }
      
    /**
     * Sets checks_enabled
     *
     * @access  public
     * @param   int checks_enabled
     * @return  int previous value
     */
    function setChecks_enabled($checks_enabled) {
      return $this->_change('checks_enabled', $checks_enabled, '%d');
    }
      
    /**
     * Retrieves plugin_output
     *
     * @access  public
     * @return  string
     */
    function getPlugin_output() {
      return $this->plugin_output;
    }
      
    /**
     * Sets plugin_output
     *
     * @access  public
     * @param   string plugin_output
     * @return  string previous value
     */
    function setPlugin_output($plugin_output) {
      return $this->_change('plugin_output', $plugin_output, '%s');
    }
      
    /**
     * Retrieves flap_detection_enabled
     *
     * @access  public
     * @return  int
     */
    function getFlap_detection_enabled() {
      return $this->flap_detection_enabled;
    }
      
    /**
     * Sets flap_detection_enabled
     *
     * @access  public
     * @param   int flap_detection_enabled
     * @return  int previous value
     */
    function setFlap_detection_enabled($flap_detection_enabled) {
      return $this->_change('flap_detection_enabled', $flap_detection_enabled, '%d');
    }
      
    /**
     * Retrieves is_flapping
     *
     * @access  public
     * @return  int
     */
    function getIs_flapping() {
      return $this->is_flapping;
    }
      
    /**
     * Sets is_flapping
     *
     * @access  public
     * @param   int is_flapping
     * @return  int previous value
     */
    function setIs_flapping($is_flapping) {
      return $this->_change('is_flapping', $is_flapping, '%d');
    }
      
    /**
     * Retrieves percent_state_change
     *
     * @access  public
     * @return  string
     */
    function getPercent_state_change() {
      return $this->percent_state_change;
    }
      
    /**
     * Sets percent_state_change
     *
     * @access  public
     * @param   string percent_state_change
     * @return  string previous value
     */
    function setPercent_state_change($percent_state_change) {
      return $this->_change('percent_state_change', $percent_state_change, '%s');
    }
      
    /**
     * Retrieves scheduled_downtime_depth
     *
     * @access  public
     * @return  int
     */
    function getScheduled_downtime_depth() {
      return $this->scheduled_downtime_depth;
    }
      
    /**
     * Sets scheduled_downtime_depth
     *
     * @access  public
     * @param   int scheduled_downtime_depth
     * @return  int previous value
     */
    function setScheduled_downtime_depth($scheduled_downtime_depth) {
      return $this->_change('scheduled_downtime_depth', $scheduled_downtime_depth, '%d');
    }
      
    /**
     * Retrieves failure_prediction_enabled
     *
     * @access  public
     * @return  int
     */
    function getFailure_prediction_enabled() {
      return $this->failure_prediction_enabled;
    }
      
    /**
     * Sets failure_prediction_enabled
     *
     * @access  public
     * @param   int failure_prediction_enabled
     * @return  int previous value
     */
    function setFailure_prediction_enabled($failure_prediction_enabled) {
      return $this->_change('failure_prediction_enabled', $failure_prediction_enabled, '%d');
    }
      
    /**
     * Retrieves process_performance_data
     *
     * @access  public
     * @return  int
     */
    function getProcess_performance_data() {
      return $this->process_performance_data;
    }
      
    /**
     * Sets process_performance_data
     *
     * @access  public
     * @param   int process_performance_data
     * @return  int previous value
     */
    function setProcess_performance_data($process_performance_data) {
      return $this->_change('process_performance_data', $process_performance_data, '%d');
    }
      
    /**
     * Update this object in the database
     *
     * @access  public
     * @return  boolean success
     * @throws  rdbms.SQLException in case an error occurs
     * @throws  lang.IllegalAccessException in case there is no suitable database connection available
     */
    function update() {
      $cm= &ConnectionManager::getInstance();  
      try(); {
        $db= &$cm->getByHost('nagios', 0);
        $db->update(
          'nagios.hoststatus set %c where ',
          $this->_updated($db),
          $this->process_performance_data
        );
      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return TRUE;
    }
    
    /**
     * Write this object to the database
     *
     * @access  public
     * @return  boolean success
     * @throws  rdbms.SQLException in case an error occurs
     * @throws  lang.IllegalAccessException in case there is no suitable database connection available
     */
    function insert() {
      $cm= &ConnectionManager::getInstance();  
      try(); {
        $db= &$cm->getByHost('nagios', 0);
        $db->insert('nagios.hoststatus (%c)', $this->_inserted($db));

      } if (catch('SQLException', $e)) {
        return throw($e);
      }

      return TRUE;
    }
    
  }
?>
