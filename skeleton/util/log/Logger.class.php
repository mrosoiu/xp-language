<?php
/* Diese Klasse ist Bestandteil des XP-Frameworks
 *
 * $Id$
 */

  uses('util.log.LogCategory');
  
  define('LOG_DEFINES_DEFAULT', 'default');
  
  /**
   * A singleton logger
   * 
   * Example output:
   * <pre>
   * [20:45:30 16012 info ] Starting work on 2002/05/29/ 
   * [20:45:30 16012 info ] Done, 0 order(s) processed, 0 error(s) occured 
   * [20:45:30 16012 info ] Finish 
   * </pre>
   *
   * The format of the line prefix (noted in square brackets above) can be configured by:
   * <ul>
   *   <li>the identifier (an id which has recognizing value, e.g. the PID)</li>
   *   <li>the variable "format" (a printf-string)</li>
   * </ul>
   *
   * Note:
   * <ul>
   *   <li>The identifier defaults to the PID of the current process</li>
   *   <li>
   *     The argument order for the format parameter is:<br/>
   *     1) Current date<br/>
   *     2) Identifier<br/>
   *     3) Indicator [info, warn, error, debug]<br/>
   *   </li>
   *   <li>The format string defaults to "[%1$s %2$s %3$s]"</li>
   *   <li>The date format defaults to "H:i:s"</li>
   * </ul>
   *
   * Example [Setting up a logger]:
   * <code>
   *   $l= &Logger::getInstance();
   *   $cat= &$l->getCategory();
   *   $cat->addAppender(new FileAppender('php://stderr'));
   * </code>
   *
   * Example [Configuring a logger]:
   * <code>
   *   $log= &Logger::getInstance();
   *   $log->configure(new Properties('etc/log.ini'));
   * </code>
   *
   * Example [Usage somewhere later on]:
   * <code>
   *   $l= &Logger::getInstance();
   *   $cat= &$l->getCategory();
   * </code>
   *
   * Property file sample:
   * <pre>
   * [default]
   * appenders="util.log.FileAppender"
   * appender.util.log.FileAppender.params="filename"
   * appender.util.log.FileAppender.param.filename="/var/log/xp/service_%Y-%m-%d.log"
   * 
   * [info.binford6100.webservices.EventHandler]
   * appenders="util.log.FileAppender"
   * appender.util.log.FileAppender.params="filename"
   * appender.util.log.FileAppender.param.filename="/var/log/xp/event_%Y-%m-%d.log"
   * 
   * [info.binford6100.webservices.SubscriberHandler]
   * appenders="util.log.FileAppender"
   * appender.util.log.FileAppender.params="filename"
   * appender.util.log.FileAppender.param.filename="/var/log/xp/subscribe_%Y-%m-%d.log"
   * </pre>
   *
   * @model    singleton
   * @purpose  Singleton logger
   */
  class Logger extends Object {
    var 
      $category= array();
    
    var
      $defaultIdentifier,
      $defaultDateformat,
      $defaultFormat,
      $defaultFlags,
      $defaultAppenders;
  
    /**
     * Get a category
     *
     * @access  public
     * @param   string name default LOG_DEFINES_DEFAULT
     * @return  &util.log.LogCategory
     */ 
    function &getCategory($name= LOG_DEFINES_DEFAULT) {
      if (!isset($this->category[$name])) $name= LOG_DEFINES_DEFAULT;
      return $this->category[$name];
    }
    
    /**
     * Configure this logger
     *
     * @access  public
     * @param   &util.Properties prop instance of a Properties object
     */
    function configure(&$prop) {
    
      // Read default properties
      $this->defaultIdentifier= $prop->readString(LOG_DEFINES_DEFAULT, 'identifier', $this->defaultIdentifier);
      $this->defaultFormat= $prop->readString(LOG_DEFINES_DEFAULT, 'format', $this->defaultFormat);
      $this->defaultDateformat= $prop->readString(LOG_DEFINES_DEFAULT, 'date.format', $this->defaultDateformat);
      $this->defaultFlags= $prop->readInteger(LOG_DEFINES_DEFAULT, 'flags', $this->defaultFlags);
      $this->defaultAppenders= $prop->readArray(LOG_DEFINES_DEFAULT, 'appenders', $this->defaultAppenders);
      
      // Read all other properties
      $section= $prop->getFirstSection();
      do {
        // Create new
        $this->category[$section]= &new LogCategory(
          $this->defaultIdentifier,
          $prop->readString($section, 'format', $this->defaultFormat),
          $prop->readString($section, 'date.format', $this->defaultDateformat),
          $prop->readInteger($section, 'flags', $this->defaultFlags)
        );
        
        // Has an appender?
        $param_section= $section;
        if (NULL === ($appenders= $prop->readArray($section, 'appenders', NULL))) {
          $appenders= $this->defaultAppenders;
          $param_section= LOG_DEFINES_DEFAULT;
        }
        
        // Go through all of the appenders
        foreach ($appenders as $appender) {
          try(); {
            $reflect= ClassLoader::loadClass($appender);
          } if (catch('Exception', $e)) {
            return throw($e);
          }
          $a= &$this->category[$section]->addAppender(new $reflect());
          $params= $prop->readArray($param_section, 'appender.'.$appender.'.params', array());
          
          // Params
          foreach ($params as $param) {
            $a->{$param}= strftime(
              $prop->readString(
                $param_section, 
                'appender.'.$appender.'.param.'.$param,
                ''
              )
            );
          }
        }
      } while ($section= $prop->getNextSection());
    }
    
    /**
     * Tells all categories to finalize themselves
     *
     * @access  public
     */
    function finalize() {
      foreach (array_keys($this->category) as $name) {
        $this->category[$name]->finalize();
      }
    }
  
    /**
     * Returns an instance of this class
     *
     * @access  public
     * @return  &util.log.Logger a logger object
     */
    function &getInstance() {
      static $__instance;
  
      if (!isset($__instance)) {
        $__instance= new Logger();
        $__instance->defaultIdentifier= getmypid();
        $__instance->defaultFormat= '[%1$s %2$s %3$5s]';
        $__instance->defaultDateformat= 'H:i:s';
        $__instance->defaultFlags= LOGGER_FLAG_ALL;
        $__instance->defaultAppenders= array();
        
        // Create an empty LogCategory
        $__instance->category[LOG_DEFINES_DEFAULT]= &new LogCategory(
          $__instance->defaultIdentifier,
          $__instance->defaultFormat,
          $__instance->defaultDateformat,
          $__instance->defaultFlags
        );

      }
      return $__instance;
    }
  }
?>
