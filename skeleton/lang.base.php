<?php
/* This file provides the core for the XP framework
 * 
 * $Id$
 */

  // {{{ final class xp
  class xp {
  
    // {{{ public string nameOf(string name)
    //     Returns the fully qualified name
    function nameOf($name) {
      if (!($n= xp::registry('class.'.$name))) {
        return $name ? 'php.'.$name : NULL;
      }
      return $n;
    }
    // }}}

    // {{{ public string typeOf(&mixed arg)
    //     Returns the fully qualified type name
    function typeOf(&$arg) {
      return is_object($arg) ? xp::nameOf(get_class($arg)) : gettype($arg);
    }
    // }}}

    // {{{ public string stringOf(&mixed arg [, string indent default ''])
    //     Returns a string representation of the given argument
    function stringOf(&$arg, $indent= '') {
      static $protect= array();

      if (is_string($arg)) {
        return '"'.$arg.'"';
      } else if (is_bool($arg)) {
        return $arg ? 'true' : 'false';
      } else if (is_null($arg)) {
        return 'null';
      } else if (is_a($arg, 'null')) {
        return '<null>';
      } else if (is_int($arg) || is_float($arg)) {
        return (string)$arg;
      } else if (is_a($arg, 'Object') && !isset($protect[$arg->__id])) {
        $protect[$arg->__id]= TRUE;
        $s= $arg->toString();
        unset($protect[$arg->__id]);
        return $s;
      } else if (is_array($arg)) {
        $ser= serialize($arg);
        if (isset($protect[$ser])) return '->{:recursion:}';
        $protect[$ser]= TRUE;
        $r= "[\n";
        foreach (array_keys($arg) as $key) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($arg[$key], $indent.'  ')."\n";
        }
        unset($protect[$ser]);
        return $r.$indent.']';
      } else if (is_object($arg)) {
        $ser= serialize($arg);
        if (isset($protect[$ser])) return '->{:recursion:}';
        $protect[$ser]= TRUE;
        $r= xp::nameOf(get_class($arg))." {\n";
        $vars= (array)$arg;
        foreach (array_keys($vars) as $key) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($vars[$key], $indent.'  ')."\n";
        }
        unset($protect[$ser]);
        return $r.$indent.'}';
      } else if (is_resource($arg)) {
        return 'resource(type= '.get_resource_type($arg).', id= '.(int)$arg.')';
      }
    }
    // }}}
    
    // {{{ public void implements(string class, string[] interfaces)
    //     Checks the implementation of the class against the interfaces and registers the connection
    function implements($class, $interfaces) {
      $signature= array_flip(get_class_methods($class));
      $implements= xp::registry('implements');

      for ($i= 0, $s= sizeof($interfaces); $i < $s; $i++) {
        $interface= $interfaces[$i];
        uses($interface);
        $name= xp::reflect($interface);
        $methods= array_flip(get_class_methods($name));
        
        // Get rid of constructors
        $c= $name;
        do {
          unset($methods[$c]);
          $implements[$class][$c]= 1;
        } while ($c= get_parent_class($c));
  
        // Pop off 'lang.Interface'
        array_pop($implements[$class]);
  
        // Check implementation
        foreach (array_keys($methods) as $method) {
          if (!isset($signature[$method])) {
            xp::error('Interface method '.$interface.'::'.$method.'() not implemented by class '.$class);
          }
        }
      }
      
      xp::registry('implements', $implements);
    }
    // }}}

    // {{{ public void gc()
    //     Runs the garbage collector
    function gc() {
      xp::registry('errors', array());
      xp::registry('exceptions', array());
    }
    // }}}

    // {{{ public <null> null()
    //     Runs a fatal-error safe version of NULL
    function &null() {
      return xp::registry('null');
    }
    // }}}

    // {{{ public bool errorAt(string file [, int line)
    //     Returns whether an error occured at the specified position
    function errorAt($file, $line= -1) {
      $errors= &xp::registry('errors');
      
      // If no line is given, check for an error in the file
      if ($line < 0) return !empty($errors[$file]);
      
      // Otherwise, check for an error in the file on a certain line
      return !empty($errors[$file][$line]);
    }
    // }}}
    
    // {{{ public mixed sapi(string* sapis)
    //     Sets an SAPI
    function sapi() {
      foreach ($a= func_get_args() as $name) {
        foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path) {
          $filename= 'sapi'.DIRECTORY_SEPARATOR.strtr($name, '.', DIRECTORY_SEPARATOR).'.sapi.php';
          $xarfile= 'sapi/'.strtr($name, '.', '/').'.sapi.php';
          
          if (is_dir($path) && file_exists($path.DIRECTORY_SEPARATOR.$filename)) {
            require_once($path.DIRECTORY_SEPARATOR.$filename);
            continue(2);
          } else if (is_file($path) && XpXarLoader::stream_provides_file('xar://'.$path.'?'.$xarfile)) {
            require_once('xar://'.$path.'?'.$xarfile);
            continue(2);
          }
        }
        
        xp::error('Cannot open SAPI '.$name.' (include_path='.ini_get('include_path').')');
      }
      
      xp::registry('sapi', $a);
    }
    // }}}
    
    // {{{ internal mixed registry(mixed args*)
    //     Stores static data
    function &registry() {
      static $registry= array();
      static $nullref= NULL;
      
      switch (func_num_args()) {
        case 0: return $registry;
        case 1: return $registry[func_get_arg(0)];
        case 2: $registry[func_get_arg(0)]= func_get_arg(1); break;
      }
      return $nullref;
    }
    // }}}
    
    // {{{ internal string reflect(string str)
    //     Retrieve PHP conformant name for fqcn
    function reflect($str) {
      return strtolower(substr($str, (FALSE === $p= strrpos($str, '.')) ? 0 : $p+ 1));
    }
    // }}}

    // {{{ internal void error(string message)
    //     Throws a fatal error and exits with exitcode 61
    function error($message) {
      restore_error_handler();
      trigger_error($message, E_USER_ERROR);
      exit(0x3d);
    }
  }
  // }}}
  
  // {{{ final class xploader
  class XpXarLoader {
    var
      $position     = 0,
      $archive      = '',
      $filename     = '';
      
    // {{{ static &mixed[] acquire(string archive)
    //     Archive instance handling pool function, opens an archive and reads header only once
    function &acquire($archive) {
      static $archives= array();
      if (!isset($archives[$archive])) {
        $archives[$archive]= array();
        $current= &$archives[$archive];

        // Bootstrap loading, only to be used for core classes.
        $current['handle']= fopen($archive, 'rb');
        $header= unpack('a3id/c1version/i1indexsize/a*reserved', fread($current['handle'], 0x0100));
        for ($current['index']= array(), $i= 0; $i < $header['indexsize']; $i++) {
          $entry= unpack(
            'a80id/a80filename/a80path/i1size/i1offset/a*reserved', 
            fread($current['handle'], 0x0100)
          );
          $current['index'][$entry['id']]= array($entry['size'], $entry['offset']);
        }
      }

      return $archives[$archive];
    }
    // }}}
    
    // {{{ function bool stream_open(string path, string mode, int options, string opened_path)
    //     Open the given stream and check if file exists
    function stream_open($path, $mode, $options, &$opened_path) {
      list($archive, $file)= sscanf($path, 'xar://%[^?]?%[^$]');
      $this->archive= $archive;
      $this->filename= $file;
      
      $current= &XpXarLoader::acquire($this->archive);
      return isset($current['index'][$this->filename]);
    }
    // }}}
    
    // {{{ string stream_read(int count)
    //     Read $count bytes up-to-length of file
    function stream_read($count) {
      $current= &XpXarLoader::acquire($this->archive);
      if (!isset($current['index'][$this->filename])) return FALSE;
      if ($current['index'][$this->filename][0] == $this->position || 0 == $count) return FALSE;

      fseek($current['handle'], 0x0100 + sizeof($current['index']) * 0x0100 + $current['index'][$this->filename][1] + $this->position, SEEK_SET);
      $bytes= fread($current['handle'], min($current['index'][$this->filename][0]- $this->position, $count));
      $this->position+= strlen($bytes);
      return $bytes;
    }
    // }}}
    
    // {{{ bool stream_eof()
    //     Returns whether stream is at end of file
    function stream_eof() {
      $current= &XpXarLoader::acquire($this->archive);
      return $this->position >= $current['index'][$this->filename][0];
    }
    // }}}
    
    // {{{ static bool stream_provides_file(string path)
    //     Check whether file lives in archive. This method must be provided as PHP only supports
    //     file_exists() on streams with PHP 5
    function stream_provides_file($path) {
      list($archive, $filename)= sscanf($path, 'xar://%[^?]?%[^$]');
      
      $current= &XpXarLoader::acquire($archive, $filename);
      return isset($current['index'][$filename]);
    }
    // }}}
  }
  // }}}

  // {{{ final class null
  class null {

    // {{{ public object null(void)
    //     Constructor to avoid magic __call invokation
    function null() {
      if (NULL !== xp::registry('null')) {
        throw(new IllegalAccessException('Cannot create new instances of xp::null()'));
      }
    }
    // }}}
    
    // {{{ magic bool __call(string name, mixed[] args, &mixed return)
    //     Call proxy
    function __call($name, $args, &$return) {
      $return= &throw(new NullPointerException('Method.invokation('.$name.')'));
      return FALSE;
    }
    // }}}

    // {{{ magic bool __set(string name, mixed value)
    //     Set proxy
    function __set($name, $value) {
      throw(new NullPointerException('Property.write('.$name.')'));
      return FALSE;
    }
    // }}}

    // {{{ magic bool __get(string name, &mixed value)
    //     Set proxy
    function __get($name, &$value) {
      $value= &throw(new NullPointerException('Property.read('.$name.')'));
      return FALSE;
    }
    // }}}
  }
  // }}}

  // {{{ internal void __error(int code, string msg, string file, int line)
  //     Error callback
  function __error($code, $msg, $file, $line) {
    if (0 == error_reporting() || is_null($file)) return;

    $errors= &xp::registry('errors');
    @$errors[$file][$line][$msg]++;
    xp::registry('errors', $errors);
  }
  // }}}
  
  // {{{ void uses (string* args)
  //     Uses one or more classes
  function uses() {
    $include= explode(PATH_SEPARATOR, ini_get('include_path'));
    foreach (func_get_args() as $str) {
      if (class_exists($class= xp::reflect($str))) continue;

      if ($p= strpos($str, '+xp://')) {
        $type= substr($str, 0, $p);
        
        // Load stream wrapper implementation and register it if not done so before
        if (!class_exists('uwrp�'.$type)) {
          require('sapi'.DIRECTORY_SEPARATOR.$type.'.uwrp.php');
          stream_wrapper_register($type.'+xp', 'uwrp�'.$type);
        }

        // Load using wrapper
        if (FALSE === include($str)) {
          xp::error(xp::stringOf(new Error('Cannot include '.$str.' (include_path='.ini_get('include_path').')')));
        }
        $str= substr($str, strrpos($str, '/')+ 1);
        $class= xp::reflect($str);
        
        continue;
      }


      foreach ($include as $path) {

        // If path is a directory and the included file exists, load it
        if (is_dir($path)) {
          if (!file_exists($f= $path.DIRECTORY_SEPARATOR.strtr($str, '.', DIRECTORY_SEPARATOR).'.class.php')) {
            continue;
          }
          
          if (FALSE === ($r= include_once($f))) {
            xp::error(xp::stringOf(new Error('Cannot include '.$str)));
          }
          
          break;
        } else if (is_file($path) && XpXarLoader::stream_provides_file($fname= 'xar://'.$path.'?'.strtr($str, '.', '/').'.class.php')) {

          // To to load via bootstrap class loader, if the file cannot provide the class-to-load
          // skip to the next include_path part
          if (FALSE === ($r= include($fname))) {
            continue;
          }
          
          xp::registry('classloader.'.$str, 'lang.archive.ArchiveClassLoader://'.$path);
          break;
        }
      }
      
      if (!class_exists(xp::reflect($str))) {
        xp::error('Cannot include '.$str.' (include_path='.ini_get('include_path').')');
      }
            
      // Register class name and call static initializer if available and if it has not been
      // done before (through an ArchiveClassLoader)
      if (NULL === xp::registry('class.'.$class)) {
        xp::registry('class.'.$class, $str);
        is_callable(array($class, '__static')) && call_user_func(array($class, '__static'));
      }
    }
  }
  // }}}

  // {{{ void try (void)
  //     Begins a try ... catch block
  function try() {
  }
  // }}}

  // {{{ bool catch (string name, &lang.Exception e)
  //     Ends a try ... catch block
  function catch($name, &$e) {
    $exceptions= &xp::registry('exceptions');
    
    $return= FALSE;
    foreach (array_keys($exceptions) as $i) {
      if (is($name, $exceptions[$i])) {
        $e= $exceptions[$i];       // Intentional copy
        unset($exceptions[$i]);
        $return= TRUE;
      }
    }
    return $return;
  }
  // }}}

  // {{{ void finally (void)
  //     Syntactic sugar. Intentionally empty
  function finally() {
  }
  // }}}

  // {{{ null throw (lang.Exception e)
  //     throws an exception
  function &throw(&$e) {
    $exceptions= &xp::registry('exceptions');
    $exceptions[]= &$e;
    xp::registry('exceptions', $exceptions);
    return xp::registry('null');
  }
  // }}}

  // {{{ null raise (string classname, string message)
  //     throws an exception by a given class name
  function &raise($classname, $message) {
    try(); {
      $class= &XPClass::forName($classname);
    } if (catch('ClassNotFoundException', $e)) {
      xp::error($e->getMessage());
    }
    $exceptions= &xp::registry('exceptions');
    $exceptions[]= &$class->newInstance($message);
    xp::registry('exceptions', $exceptions);
    return xp::registry('null');
  }
  // }}}

  // {{{ mixed cast (&mixed var, mixed type default NULL)
  //     Casts. If var === NULL, it won't be touched
  function &cast(&$var, $type= NULL) {
    if (NULL === $var) return NULL;

    switch ($type) {
      case NULL: 
        break;

      case 'int':
      case 'integer':
      case 'float':
      case 'double':
      case 'string':
      case 'bool':
      case 'null':
        if (is_a($var, 'Object')) $var= $var->toString();
        settype($var, $type);
        break;

      case 'array':
      case 'object':
        settype($var, $type);
        break;

      default:
        // Cast to an object of "$type"
        $o= &new $type;
        if (is_object($var) || is_array($var)) {
          foreach ($var as $k => $v) {
            $o->$k= $v;
          }
        } else {
          $o->scalar= $var;
        }
        return $o;
        break;
    }
    return $var;
  }
  // }}}

  // {{{ proto void implements(string file, string interface [, string interface [, ...]]) 
  //     Defines that the class this is called in implements certain interface(s)
  function implements() {
    $args= func_get_args();
    $class= strtolower(substr(basename(array_shift($args)), 0, -10));
    
    xp::implements($class, $args);
  }
  // }}}
  
  // {{{ proto bool is(string class, &lang.Object object)
  //     Checks whether a given object is of the class, a subclass or implements an interface
  function is($class, &$object) {
    $p= get_class($object);
    if (is_null($class) && 'null' == $p) return TRUE;
    $class= xp::reflect($class);
    if (is_a($object, $class)) return TRUE;
    $implements= xp::registry('implements');
    
    
    do {
      if (isset($implements[$p][$class])) return TRUE;
    } while ($p= get_parent_class($p));
    return FALSE;
  }
  // }}}

  // {{{ proto void delete(&lang.Object object)
  //     Destroys an object
  function delete(&$object) {
    is_a($object, 'Object') && method_exists($object, '__destruct') && $object->__destruct();
    $object= NULL;
  }
  // }}}

  // {{{ proto lang.Object &clone(lang.Object object) throws CloneNotSupportedException
  //     Clones an object
  function &clone($object) {
    if (is(NULL, $object)) return throw(new NullPointerException('Cannot clone NULLs'));
    if (!is_a($object, 'Object')) return raise('lang.CloneNotSupportedException', 'Cannot clone non-objects');

    $object->__id= microtime();
    if (is_callable(array(&$object, '__clone'))) {
      try(); {
        call_user_func(array(&$object, '__clone'));
      } if (catch('CloneNotSupportedException', $e)) {
        return throw($e);
      }
    }
    return $object;
  }
  // }}}

  // {{{ proto void with(expr)
  //     Syntactic sugar. Intentionally empty
  function with() {
  }
  // }}}
  
  // {{{ proto &mixed ref(&mixed object)
  //     Creates a "reference" to an object
  function &ref(&$object) {
    return array(&$object);
  }
  // }}}

  // {{{ proto &mixed deref(&mixed expr)
  //     Dereferences an expression
  function &deref(&$expr) {
    if (is_array($expr)) return $expr[0]; else return $expr;
  }
  // }}}

  // {{{ proto &lang.Object newinstance(string classname, mixed[] args, string bytes)
  //     Anonymous instance creation
  function &newinstance($classname, $args, $bytes) {
    static $u= 0;

    $class= xp::reflect($classname);
    if (!class_exists($class)) {
      xp::error(xp::stringOf(new Error('Class "'.$classname.'" does not exist')));
      // Bails
    }

    $name= $class.'�'.(++$u);
    xp::registry('class.'.$name, $name);
    
    // Build paramstr for evaluation
    for ($paramstr= '', $i= 0, $m= sizeof($args); $i < $m; $i++) {
      $paramstr.= ', $args['.$i.']';
    }

    // Checks whether an interface or a class was given
    $c= $class;
    while ($c= get_parent_class($c)) {
      if ('interface' != $c) continue;
      
      // It's an interface
      eval('class '.$name.' extends Object '.$bytes.' $instance= &new '.$name.'('.substr($paramstr, 2).');');
      implements($name.'.class.php', $class);
      return $instance;
    }
    
    // It's a class
    eval('class '.$name.' extends '.$class.' '.$bytes.' $instance= &new '.$name.'('.substr($paramstr, 2).');');
    return $instance;
  }
  // }}}

  // {{{ initialization
  error_reporting(E_ALL);
  
  // Get rid of magic quotes 
  get_magic_quotes_gpc() && xp::error('[xp::core] magic_quotes_gpc enabled');
  ini_set('magic_quotes_runtime', FALSE);
  
  // Constants
  defined('PATH_SEPARATOR') || define('PATH_SEPARATOR',  0 == strncasecmp('WIN', PHP_OS, 3) ? ';' : ':');
  define('SKELETON_PATH', (getenv('SKELETON_PATH')
    ? getenv('SKELETON_PATH')
    : dirname(__FILE__).DIRECTORY_SEPARATOR
  ));
  define('LONG_MAX', is_int(2147483648) ? 9223372036854775807 : 2147483647);
  define('LONG_MIN', -LONG_MAX - 1);

  // Hooks
  extension_loaded('overload') && overload('null');
  set_error_handler('__error');
  
  // Registry initialization
  xp::registry('null', new null());
  xp::registry('errors', array());
  xp::registry('exceptions', array());
  xp::registry('class.xp', '<xp>');
  xp::registry('class.null', '<null>');

  // Register stream wrapper for .xar class loading
  stream_wrapper_register('xar', 'XpXarLoader');

  // Omnipresent classes
  uses(
    'lang.Object',
    'lang.Error',
    'lang.Exception',
    'lang.Interface',
    'lang.XPClass',
    'lang.NullPointerException',
    'lang.IllegalAccessException',
    'lang.IllegalArgumentException',
    'lang.IllegalStateException',
    'lang.FormatException',
    'lang.ClassLoader',
    'lang.archive.ArchiveReader',
    'lang.archive.ArchiveClassLoader'
  );
  // }}}
?>
