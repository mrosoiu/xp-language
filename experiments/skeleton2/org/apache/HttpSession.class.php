<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  define('SESS_CREATE',    '__PHP_SessionCreatedAt');

  /**
   * A session provides a way of identifying users across a website
   * and having data associated to this person available on every page.
   * Since HTTP is a stateless protocol, sessions where implemented
   * to persist across more than one connection and request. A session
   * usually corresponds to exactly one user, "identifying" him/her
   * to the web pages displayed.
   *
   * Sessions may also be used to "cache" data from an RDBMS underlying
   * the web site. E.g., a user logs on to his service provider's 
   * administration site where he/she is allowed to configure a number
   * of - say - server settings. In this case, in the login site, we
   * read all of his/her contracts from the database and store these
   * objects to the session. On preceding pages displayed, these objects
   * are simply read from the session, saving time and database load.
   * Of course, in any case the data may have changed, we must unset
   * it from the session and reread it from the database.
   *
   * If you are using this session class from within a HttpScriptlet
   * or a class extended from it, simply set the public variable
   * <pre>needSession</pre> to TRUE. The HttpScriptlet implementation
   * will take care of creating the session and checking its validity
   * for you. As default, the session is prepended to the query string
   * as in <pre>&psessionid=ID</pre>. Of course, you will have to prepend
   * this to all links appearing on your pages. You may use the predefined
   * constant <pre>SID</sid> which contains the value "psessionid=ID" to
   * accomplish this. If you are uncontent whith this, have a look at
   * <pre>sessionURIFormat</pre>. This will allow you to change the way
   * the session's ID is "inserted" into the query string if none exists.
   * Have a look at Apache's RewriteRule for building sexier query strings
   * 
   * As an underlying layer, PHP's builtin session functions are used.
   * Variables are kept in the auto-superglobal <pre>_SESSION</pre> Variable,
   * so a recent version of PHP (>4.2) is required.
   *
   * Please have a look into "Sessions and security" on PHP's session 
   * documentation page.
   *
   * @see      php://session
   * @see      xp://org.apache.HttpScriptlet
   * @see      http://httpd.apache.org/docs/mod/mod_rewrite.html#RewriteRule
   * @see      http://www.engelschall.com/pw/apache/rewriteguide/
   * @purpose  Session
   */
  class HttpSession extends Object {
    public
      $id,
      $isNew=   FALSE;
      
    /**
     * Constructor
     *
     * @access  public
     */
    public function __construct() {
      
      ini_set('session.use_cookies', 0);
      session_name('psessionid');
    }
    
    /**
     * Initializes the session
     *
     * @access  public
     * @param   string id session id
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function initialize($id) {
      if (!empty($id)) {
        $this->id= $id;
        session_id($this->id);
        session_start();
        
        if (FALSE === self::getCreationTime()) return FALSE;
        
        // OK
        return TRUE;
      }
      
      // New Session
      session_start();
      $this->isNew= TRUE;
      $this->id= session_id();
      
      // Remember when we started this session
      $_SESSION[SESS_CREATE]= time();
    }
    
    /**
     * Returns if this session is valid
     *
     * @access  public
     * @return  bool valid
     */
    public function isValid() {
      return isset($_SESSION[SESS_CREATE]);
    }
    
    /**
     * Returns if this session is a new session
     *
     * @access  public
     * @return  bool new
     */
    public function isNew() {
      return $this->isNew;
    }
    
    /**
     * Returns this session's Id
     *
     * @access  public
     * @return  string id
     */
    public function getId() {
      return $this->id;
    }
    
    /**
     * Retrieves the time when this session was created, as Unix-
     * timestamp
     *
     * @access  public
     * @return  int Unix-timestamp
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function getCreationTime() {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      return $_SESSION[SESS_CREATE];
    }
    
    /**
     * Resets the session and deletes all variables 
     *
     * @access  public
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function reset() {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      $_SESSION= array(SESS_CREATE => $_SESSION[SESS_CREATE]);
    }
    

    /**
     * Registers a variable. If another variable is already registered
     * under the specified name, it is replaced
     *
     * @access  public
     * @param   string name
     * @param   mixed& value Any data type
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function putValue($name, $value) {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      $_SESSION[$name]= serialize($value);
    }
    
    /**
     * Retrieves a value previously registered with the specified name
     * or NULL in case this name does not exist
     *
     * @access  public
     * @param   string name 
     * @return  mixed value
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function getValue($name) {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      if (isset($_SESSION[$name])) return unserialize($_SESSION[$name]); else return NULL;
    }
    
    /**
     * Checks whether a value by specified name exists
     *
     * @access  public
     * @param   string name 
     * @return  bool TRUE if the value exists, FALSE otherwiese
     */
    public function hasValue($name) {
      return isset($_SESSION[$name]);
    }
    
    /**
     * Removes a value from the session. If no value is found for
     * the specified name, nothing happens
     *
     * @access  public
     * @param   name The name of the value to delete
     */
    public function removeValue($name) {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      if (isset($_SESSION[$name])) {
        $_SESSION[$name]= NULL;
      }
    }
    
    /**
     * Return an array of all names registered in this session
     *
     * @access  public
     * @return  string[] names
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function getValueNames() {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      $names= array_keys($_SESSION);
      unset($names[SESS_CREATE]);
      return $names;
    }
    
    /**
     * Invalidates a session and deletes all values
     *
     * @access  public
     * @throws  lang.IllegalStateException when session is invalid
     */
    public function invalidate() {
      if (!self::isValid()) throw (new IllegalStateException('Session is invalid'));
      session_destroy();
      $_SESSION= array();
    }
  }
?>
