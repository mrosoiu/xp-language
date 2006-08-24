<?php
/* This class is part of the XP framework
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
   * appender.util.log.FileAppender.flags="LOGGER_FLAG_ERROR|LOGGER_FLAG_WARN"
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
   * @test     xp://net.xp_framework.unittest.logging.LoggerTest
   * @purpose  Singleton logger
   */
  class Logger extends Object {
    var 
      $category     = array();
    
    var
      $defaultIdentifier,
      $defaultDateformat,
      $defaultFormat,
      $defaultFlags,
      $defaultAppenders;
  
    var
      $_finalized   = FALSE;

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
      $class= array();
      
      // Read default properties
      $this->defaultIdentifier= $prop->readString(LOG_DEFINES_DEFAULT, 'identifier', $this->defaultIdentifier);
      $this->defaultFormat= $prop->readString(LOG_DEFINES_DEFAULT, 'format', $this->defaultFormat);
      $this->defaultDateformat= $prop->readString(LOG_DEFINES_DEFAULT, 'date.format', $this->defaultDateformat);
      $this->defaultFlags= $prop->readInteger(LOG_DEFINES_DEFAULT, 'flags', $this->defaultFlags);
      $this->defaultAppenders= $prop->readArray(LOG_DEFINES_DEFAULT, 'appenders', $this->defaultAppenders);
      
      // Read all other properties
      $section= $prop->getFirstSection();
      do {
        try(); {
          $catclass= &XPClass::forName($prop->readString($section, 'category', 'util.log.LogCategory'));
        } if (catch('ClassNotFoundException', $e)) {
          return throw($e);
        }

        $this->category[$section]= &$catclass->newInstance(
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

        // Go through all of the appenders, loading classes as necessary
        foreach ($appenders as $appender) {
          if (!isset($class[$appender])) {
            try(); {
              $class[$appender]= &XPClass::forName($appender);
            } if (catch('ClassNotFoundException', $e)) {
              return throw($e);
            }
          }
          
          // Read flags string, evaluate it
          $flags= $prop->readArray($param_section, 'appender.'.$appender.'.flags', LOGGER_FLAG_ALL);
          if (!is_int ($flags)) {
            $arrflags= $flags; $flags= 0;
            foreach ($arrflags as $f) { if (defined ($f)) $flags |= constant ($f); }
          }
          
          $a= &$this->category[$section]->addAppender($class[$appender]->newInstance(), $flags);
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
      if (!$this->_finalized) foreach (array_keys($this->category) as $name) {
        $this->category[$name]->finalize();
      }
      $this->_finalized= TRUE;
    }
    
    /**
     * Returns an instance of this class
     *
     * @model   static
     * @access  public
     * @return  &util.log.Logger a logger object
     */
    function &getInstance() {
      static $instance;
  
      if (!isset($instance)) {
        $instance= new Logger();
        $instance->defaultIdentifier= getmypid();
        $instance->defaultFormat= '[%1$s %2$s %3$5s]';
        $instance->defaultDateformat= 'H:i:s';
        $instance->defaultFlags= LOGGER_FLAG_ALL;
        $instance->defaultAppenders= array();
        
        // Create an empty LogCategory
        $instance->category[LOG_DEFINES_DEFAULT]= &new LogCategory(
          $instance->defaultIdentifier,
          $instance->defaultFormat,
          $instance->defaultDateformat,
          $instance->defaultFlags
        );

      }
      return $instance;
    }

  } implements(__FILE__, 'util.Configurable');
?>
