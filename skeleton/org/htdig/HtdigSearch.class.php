<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'org.htdig.HtdigResultset',
    'org.htdig.HtdigEntry',
    'lang.Process'
  );

  // Defines for sorting methods
  define('SORT_SCORE',         'score');
  define('SORT_TIME',          'time');
  define('SORT_TITLE',         'title');
  define('SORT_REVSCORE',      'revscore');
  define('SORT_REVTIME',       'revtime');
  define('SORT_REVTITLE',      'revtitle');

  /**
   * Encapsulates a htdig query. This class needs a working 
   * htdig installation on the executing host.
   *
   * Special requirements need to be posed upon the configuration
   * which are supposed to work with this class: it must use a 
   * configuration that does not create HTML output but instead
   * produces some sort of CSV output. The delimiter must match
   * the one set in this class.
   *
   * <code>
   *   try(); {
   *     $search= &new HtdigSearch();
   *     $search->setConfig('/path/to/htdig-configuration');
   *     $search->setExecutable('/usr/local/bin/htdig');
   *     $search->setWords(array('foo', '-bar'));
   *     $resultset= &$search->invoke();
   *   } if (catch('IOException', $e)) {
   *     $e->printStackTrace();
   *     exit(1);
   *   } if (catch('IllegalArgumentException', $e)) {
   *     $e->printStackTrace();
   *     exit(1);
   *   }
   *
   *   Console::writeLine($result->toString());
   * </code>
   *
   * The aforementioned requirements for the ht://dig setup consist of rules for
   * the output of htdig:
   * The header file containing the csv-definition looks like:
   * <pre>
   *   LOGICAL_WORDS:$(LOGICAL_WORDS)
   *   MATCHES:$(MATCHES)
   *   PAGES:$(PAGES)
   *   CSV:CURRENT|DOCID|NSTARS|SCORE|URL|TITLE|EXCERPT|METADESCRIPTION|MODIFIED|SIZE|HOPCOUNT|PERCENT          
   * </pre>
   *
   * The used template must use the following template-syntax (one line):
   * <pre>
   *   $(CURRENT)|$(DOCID)|$(NSTARS)|$(SCORE)|$%(URL)|$%(TITLE)|$%(EXCERPT)|
   *   $%(METADESCRIPTION)|$(MODIFIED)|$(SIZE)|$(HOPCOUNT)|$(PERCENT)
   * </pre>
   *
   * Note also that you can set query parameters which may or may not be overwriteable
   * by a client - this depends on the actual ht://dig - configuration.
   *
   * @see      http://htdig.org
   * @purpose  Wrap htdig query
   */
  class HtdigSearch extends Object {
    var
      $config=      NULL,    
      $params=      array(), 
      $words=       array(),
      $excludes=    array(),
      $algorithms=  '',
      $sort=        SORT_SCORE,
      $method=      'boolean',
      $maxresults=  0;
    
    var
      $delimiter=   '|',
      $executable=  '';

    /**
     * Set Config
     *
     * @access  public
     * @param   string config
     */
    function setConfig($config) {
      $this->config= $config;
    }

    /**
     * Get Config
     *
     * @access  public
     * @return  string
     */
    function getConfig() {
      return $this->config;
    }

    /**
     * Set Params
     *
     * @access  public
     * @param   mixed[] params
     */
    function setParams($params) {
      $this->params= $params;
    }

    /**
     * Get Params
     *
     * @access  public
     * @return  mixed[]
     */
    function getParams() {
      return $this->params;
    }
    
    /**
     * Set a single param
     *
     * @access  public
     * @param   string name
     * @param   string value
     */
    function setParam($name, $value) {
      $this->params[$name]= $value;
    }
    
    /**
     * Get a single param
     *
     * @access  public
     * @param   string name
     * @return  string value or NULL if isset
     */
    function getParam($name) {
      return (isset($this->params[$name])
        ? $this->params[$name]
        : NULL
      );
    }    

    /**
     * Set Words
     *
     * @access  public
     * @param   mixed[] words
     */
    function setWords($words) {
      $this->words= $words;
    }

    /**
     * Get Words
     *
     * @access  public
     * @return  mixed[]
     */
    function getWords() {
      return $this->words;
    }

    /**
     * Set Excludes
     *
     * @access  public
     * @param   mixed[] excludes
     */
    function setExcludes($excludes) {
      $this->excludes= $excludes;
    }

    /**
     * Get Excludes
     *
     * @access  public
     * @return  mixed[]
     */
    function getExcludes() {
      return $this->excludes;
    }

    /**
     * Set Algorithm
     *
     * @access  public
     * @param   string algorithm
     */
    function setAlgorithms($algorithm) {
      $this->algorithms= $algorithm;
    }

    /**
     * Get Algorithm
     *
     * @access  public
     * @return  string
     */
    function getAlgorithms() {
      return $this->algorithms;
    }

    /**
     * Set Sort
     *
     * @access  public
     * @param   mixed sort
     */
    function setSort($sort) {
      $this->sort= $sort;
    }

    /**
     * Get Sort
     *
     * @access  public
     * @return  mixed
     */
    function getSort() {
      return $this->sort;
    }

    /**
     * Set Maxresults
     *
     * @access  public
     * @param   int maxresults
     */
    function setMaxresults($maxresults) {
      $this->maxresults= $maxresults;
    }

    /**
     * Get Maxresults
     *
     * @access  public
     * @return  int
     */
    function getMaxresults() {
      return $this->maxresults;
    }

    /**
     * Set Method
     *
     * @access  public
     * @param   mixed method
     */
    function setMethod($method) {
      $this->method= $method;
    }

    /**
     * Get Method
     *
     * @access  public
     * @return  mixed
     */
    function getMethod() {
      return $this->method;
    }

    /**
     * Build the query string for the search.
     *
     * @access  protected
     * @return  string query
     */
    function _getWordString() {
      $str= '';
      foreach ($this->getWords() as $w) { 
        if ($w{0} != '-') {
          $str.= ' AND '.$w;
        } else {
          $str.= ' NOT '.substr($w, 1);
        }
      }
      
      return substr ($str, 5);
    }
    
    /**
     * Build query string.
     *
     * @access  protected
     * @return  string query
     */
    function _getQuery() {
      $params= $this->getParams();

      // If excludes are given, add them to the query
      if (strlen ($this->getExcludes()))
        $params['exclude']= implode('|', $this->getExcludes());
      
      // Only overwrite algorithms, when they are set  
      if (strlen ($this->getAlgorithms()))
        $params['search_algorithm']= $this->getAlgorithms();

      // Set the search method (regular / boolean)
      $params['method']= $this->getMethod();

      // If maxresults is 0, use 1000 as matchesperpage. We'll only receive
      // the first page, so this replaces 'all results' which htdig does not support.
      $params['matchesperpage']= (empty($this->maxresults) 
        ? 1000 : 
        $this->maxresults
      );

      $params['sort']= $this->getSort();
      $params['words']= $this->_getWordString();
      
      $str= '';
      foreach (array_keys($params) as $key) {
        if (strlen($str)) $str.= '&';
        $str.= $key.'='.urlencode($params[$key]);
      }

      return $str;
    }

    /**
     * Set Executable
     *
     * @access  public
     * @param   string executable
     */
    function setExecutable($executable) {
      $this->executable= $executable;
    }

    /**
     * Get Executable
     *
     * @access  public
     * @return  string
     */
    function getExecutable() {
      return $this->executable;
    }

    /**
     * Invoke the search.
     *
     * @access  public
     * @return  &org.htdig.HtdigResultset
     * @throws  io.IOException in case the invocation of htdig failed
     * @throws  lang.IllegalArgumentException in case search entry was invalid
     */
    function &invoke() {
      $log= &Logger::getInstance();
      $cat= &$log->getCategory();

      try(); {
        $cmdline= sprintf('%s -v %s %s',
          $this->getExecutable(),
          strlen($this->getConfig()) ? '-c '.$this->getConfig() : '',
          "'".$this->_getQuery()."' 2>&1"
        );
        $p= &new Process($cmdline);

        // Read standard output
        $output= array();
        while (!$p->out->eof()) { $output[]= $p->out->readLine(); }
      } if (catch('IOException', $e)) {
        return throw ($e);
      }
      
      $result= &new HtdigResultset();
      $metaresult= array();
      $hasCsv= FALSE;

      try(); {
      
        // Parse metadata result (search result header)
        while (FALSE !== current($output) && !$hasCsv) {
          $meta= explode(':', trim(current($output)));

          // Check for header-data; the last line of header is the CSV definition
          if ('CSV' != trim($meta[0])) {
            $metaresult[trim(strtolower($meta[0]))]= trim($meta[1]);
          } else {
            $result->setCsvdef(explode($this->delimiter, substr(current($output), 4)));
            $hasCsv= TRUE;
          }

          next($output);
        }

        $result->setMetaresult($metaresult);

        // Now parse resultset
        $cnt= 0;
        while (FALSE !== current ($output)) {
        
          // Don't exceed maxresults
          if ($this->maxresults && $cnt > $this->maxresults)
            break;
            
          // Do not take empty lines into account
          if (current($output)) {
            $result->addResult(explode($this->delimiter, current($output)));
            $cnt++;
          }
          next ($output);
        }
      } if (catch('IllegalArgumentException', $e)) {
        return throw ($e);
      }

      return $result;
    }
  }
?>
