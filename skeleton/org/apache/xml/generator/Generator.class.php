<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'xml.XML', 
    'org.apache.xml.generator.DependencyTracker',
    'org.apache.xml.generator.DependencyFactory',
    'org.apache.xml.generator.DependencyStorage',
    'org.apache.xml.generator.GeneratorObserver',
    'util.Properties'
  );

  define('GENERATOR_START',      'start');
  define('GENERATOR_SUCCESS',    'success');
  define('GENERATOR_FAILURE',    'failure');
  define('GENERATOR_OMIT',       'omit');
  define('GENERATOR_NEWDEP',     'newdependency');
  define('GENERATOR_UPDATEDDEP', 'dependencyupdated');

  /**
   * Generator
   *
   * @purpose  Generator
   */
  class Generator extends Object {
    var
      $processor    = NULL,
      $tracker      = NULL;
      
    var
      $_target      = '',
      $_obs         = array();
      
    /**
     * Configure
     *
     * @access  public
     * @param   &util.Properties prop
     * @return  bool
     */
    function configure(&$prop) {
      try(); {
        $this->config= $prop->readSection('generator');
        $proc= &XPClass::forName($this->config['xsl.processor']);
      } if (catch('ClassNotFoundException', $e)) {
        return throw($e);
      } if (catch('Exception', $e)) {
        return throw($e);
      }
      
      // Set up XSL processor
      $this->setProcessor($proc->newInstance());
      
      // Set up depency tracker
      $this->tracker= &new DependencyTracker(new DependencyStorage());
      $this->tracker->initialize($this->config['dependency.storage']);
      
      return TRUE;
    }
    
    /**
     * Set the XSL processor to use
     *
     * @access  protected
     * @param   &xsl.XSLProcessor processor
     */
    function setProcessor(&$processor) {
      $this->processor= &$processor;
      $this->processor->setSchemeHandler(array(
        'get_all' => array(&$this, 'schemeHandler')
      ));
      $this->processor->setXSLFile($this->config['xsl.source'].'/custom.xsl');
    }
    
    /**
     * Adds an observer
     *
     * @access  public
     * @param   &org.apache.xml.generator.GeneratorObserver o
     * @return  &org.apache.xml.generator.GeneratorObserver the added observer
     */
    function &addObserver(&$o) {
      $this->_obs[]= &$o;
      return $o;
    }
    
    /**
     * Notify observers
     *
     * @access  protected
     * @param   string status one of the GENERATOR_* constants
     * @param   string target
     * @param   lang.Exception error default NULL
     */
    function notifyObservers($status, $target, $error= NULL) {
      for ($i= 0, $s= sizeof($this->_obs); $i < $s; $i++) {
        call_user_func(array(&$this->_obs[$i], 'on'.$target), $target, $error);
      }
    }

    /**
     * Invokes generator
     *
     * @access  public
     * @param   string target target file name
     * @return  bool success
     */
    function generate($target) {
      if (!$this->processor) return GENERATOR_FAILURE;
      
      // Notify observers of start
      $this->notifyObservers(GENERATOR_START, $target);
      
      $this->_target= $target;
      $needsGeneration= FALSE;
      
      // Get dependencies for this target and loop through them
      $dependencies= $this->tracker->getDependencies($target);
      foreach (array_keys($dependencies) as $key) {
        switch ($this->generate($dependencies[$key]->getName())) {
          case GENERATOR_SUCCESS:       
            // Generation of dependency succeeded, we need to regenerate this target
            $needsGeneration= TRUE; 
            break;
            
          case GENERATOR_FAILURE:       
            // Generation failed, stop immediately
            return GENERATOR_FAILURE;
            
          case GENERATOR_OMIT:          
            // Generation ommitted - this is empty intentionally
        }
      }
      
      // Check if the target we were called for needs to be generated
      $targetDep= &DependencyFactory::factory('include', $target, $this->config);
      if ($needsGeneration || $targetDep->hasChangedSince($this->config['lastrun'])) {
        try(); {
          $targetDep->generate($this);
        } if (catch('Exception', $e)) {
          // Notify observers of failure and rethrow exception
          $this->notifyObservers(GENERATOR_FAILURE, $target, $e);
          throw($e);
          return GENERATOR_FAILURE;
        }
        
        // Notify observers: Generation succeeded
        $this->notifyObservers(GENERATOR_SUCCESS, $target);
        return GENERATOR_SUCCESS;
      }
      
      // Notify observers: Generation ommitted, target is up-to-date
      $this->notifyObservers(GENERATOR_OMIT, $target);
      return GENERATOR_OMIT;
    }

    /**
     * Finalize the generator
     *
     * @access  public
     */    
    function finalize() {
      $this->tracker->finalize();
      
      // Prevent "Warning [code:81]  cannot unregister message handler, none registered
      // and "php in free(): warning: chunk is already free"
      // and therefor: Segmentation fault (core dumped)
      $this->processor->setSchemeHandler(array('get_all' => NULL));
    }
    
    /**
     * Save a file -- TBD: does this belong here? --
     *
     * @access  public
     * @param   string dest
     * @param   string contents
     * @return  bool success
     */
    function save($dest, $contents) {
      if (empty($contents)) {
        return throw(new IllegalArgumentException('Will not create empty file'));
      }
      
      // Save contents to file
      try(); {
        // DEBUG printf("---> Saving: "); var_dump($contents);
        FileUtil::setContents($dest, $contents);
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      
      return TRUE;  // Indicate success
    }
    
    /**
     * Handles callbacks
     *
     * @access  private
     * @param   resource p
     * @param   string scheme
     * @param   string rest
     * @return  string xml
     */
    function schemeHandler($p, $scheme, $rest) {
      printf("===> Scheme handler for <%s> <%s>\n", $scheme, $rest);
      list($name, $params)= explode('?', substr($rest, 1), 2);
      try(); {
        if ($dep= &$this->tracker->addDependency(
          $this->_target,
          DependencyFactory::factory($scheme, $name, $this->config)
        )) {
          $this->notifyObservers(GENERATOR_NEWDEP, $name);
          $return= $dep->process($params);
        }
      } if (catch('Exception', $e)) {
        $this->notifyObservers(GENERATOR_FAILURE, $name, $e);
        $return= sprintf(
          '<fault fatal="%d" id="%s"><![CDATA[%s]]></fault>',
          TRUE,
          $e->getClassName(),
          $e->toString()
        );
      }
      
      $this->notifyObservers(GENERATOR_UPDATEDDEP, $name);
      $this->tracker->updateDependency($dep->name, time());
      
      // DEBUG printf("---> Returning for %s: %s\n", var_export($dep, 1), $return);
      return (empty($return) ? '<call/>' : $return);
    }
  }
?>
