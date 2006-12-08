<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'remote.protocol.SerializedData',
    'remote.protocol.DateMapping',
    'remote.protocol.LongMapping',
    'remote.protocol.ByteMapping',
    'remote.protocol.ShortMapping',
    'remote.protocol.FloatMapping',
    'remote.protocol.DoubleMapping',
    'remote.protocol.IntegerMapping',
    'remote.protocol.HashmapMapping',
    'remote.protocol.ArrayListMapping',
    'remote.protocol.ExceptionMapping',
    'remote.protocol.StackTraceElementMapping',
    'remote.UnknownRemoteObject',
    'remote.ExceptionReference',
    'remote.ClassReference'
  );

  /**
   * Class that reimplements PHP's builtin serialization format.
   *
   * @see      php://serialize
   * @test     xp://net.xp_framework.unittest.remote.SerializerTest
   * @purpose  Serializer
   */
  class Serializer extends Object {
    var
      $mappings   = array(),
      $packages   = array(),
      $exceptions = array();
    
    var
      $_classMapping  = array();

    /**
     * Constructor. Initializes the default mappings
     *
     * @access  public
     */
    function __construct() {
      $this->mapping('T', $m= &new DateMapping());
      $this->mapping('l', $m= &new LongMapping());
      $this->mapping('B', $m= &new ByteMapping());
      $this->mapping('S', $m= &new ShortMapping());
      $this->mapping('f', $m= &new FloatMapping());
      $this->mapping('d', $m= &new DoubleMapping());
      $this->mapping('i', $m= &new IntegerMapping());
      $this->mapping('A', $m= &new ArrayListMapping());
      $this->mapping('e', $m= &new ExceptionMapping());
      $this->mapping('t', $m= &new StackTraceElementMapping());
      
      // A hashmap doesn't have its own token, because it'll be serialized
      // as an array. We use HASHMAP as the token, so it will never match
      // another one (can only be one char). This is a little bit hackish.
      $this->mapping('HASHMAP', $m= &new HashmapMapping());
      
      // Setup default exceptions
      $this->exceptionName('IllegalArgument', 'lang.IllegalArgumentException');
      $this->exceptionName('IllegalAccess',   'lang.IllegalAccessException');
      $this->exceptionName('ClassNotFound',   'lang.ClassNotFoundException');
      $this->exceptionName('NullPointer',     'lang.NullPointerException');
    }

    /**
     * Retrieve serialized representation of a variable
     *
     * @access  public
     * @param   &mixed var
     * @return  string
     * @throws  lang.FormatException if an error is encountered in the format 
     */  
    function representationOf(&$var, $ctx= array()) {
      switch (gettype($var)) {
        case 'NULL':    return 'N;';
        case 'boolean': return 'b:'.($var ? 1 : 0).';';
        case 'integer': return 'i:'.$var.';';
        case 'double':  return 'd:'.$var.';';
        case 'string':  return 's:'.strlen($var).':"'.$var.'";';
        case 'array':
          $s= 'a:'.sizeof($var).':{';
          foreach (array_keys($var) as $key) {
            $s.= serialize($key).$this->representationOf($var[$key], $ctx);
          }
          return $s.'}';
        case 'object': {
          if (FALSE !== ($m= &$this->mappingFor($var))) {
            return $m->representationOf($this, $var, $ctx);
          }
          
          // Default object serializing
          $name= xp::typeOf($var);
          $props= get_object_vars($var);
          unset($props['__id']);
          $s= 'O:'.strlen($name).':"'.$name.'":'.sizeof($props).':{';
          foreach (array_keys($props) as $name) {
            $s.= serialize($name).$this->representationOf($var->{$name}, $ctx);
          }
          return $s.'}';
        }
        case 'resource': return ''; // Ignore (resources can't be serialized)
        default: throw(new FormatException(
          'Cannot serialize unknown type '.xp::typeOf($var)
        ));
      }
    }
    
    /**
     * Fetch best fitted mapper for the given object
     *
     * @access  protected
     * @param   &lang.Object var
     * @return  &mixed FALSE in case no mapper could be found, &remote.protocol.SerializerMapping otherwise
     */
    function &mappingFor(&$var) {
      if (!is('lang.Object', $var)) return FALSE;
      
      // Check the mapping-cache for an entry for this object's class
      if (isset($this->_classMapping[$var->getClassName()])) {
        return $this->_classMapping[$var->getClassName()];
      }
      
      // Find most suitable mapping by calculating the distance in the inheritance
      // tree of the object's class to the class being handled by the mapping.
      $cinfo= array();
      foreach (array_keys($this->mappings) as $token) {
        $class= &$this->mappings[$token]->handledClass();
        if (!is($class->getName(), $var)) continue;

        $distance= 0;
        do {

          // Check for direct match
          if ($class->getName() != $var->getClassName()) $distance++;
        } while (0 < $distance && NULL !== ($class= &$class->getParentclass()));

        // Register distance to object's class in cinfo
        $cinfo[$distance]= &$this->mappings[$token];

        if (isset($cinfo[0])) break;
      }
      
      // No handlers found...
      if (0 == sizeof($cinfo)) return FALSE;

      ksort($cinfo, SORT_NUMERIC);
      
      // First class is best class
      $handlerClass= &$cinfo[key($cinfo)];

      // Remember this, so we can take shortcut next time
      $this->_classMapping[$var->getClassName()]= &$cinfo[key($cinfo)];
      return $this->_classMapping[$var->getClassName()];
    }

    /**
     * Register or retrieve a mapping for a token
     *
     * @access  public
     * @param   string token
     * @param   &remote.protocol.SerializerMapping mapping
     * @return  &remote.protocol.SerializerMapping mapping
     * @throws  lang.IllegalArgumentException if the given argument is not a SerializerMapping
     */
    function &mapping($token, &$mapping) {
      if (NULL !== $mapping) {
        if (!is('SerializerMapping', $mapping)) return throw(new IllegalArgumentException(
          'Given argument is not a SerializerMapping ('.xp::typeOf($mapping).')'
        ));

        $this->mappings[$token]= &$mapping;
        $this->_classMapping= array();
      }
      
      return $this->mappings[$token];
    }
    
    /**
     * Register or retrieve a mapping for a token
     *
     * @access  public
     * @param   string token
     * @param   string exception fully qualified class name
     * @return  string 
     */
    function exceptionName($name, $exception= NULL) {
      if (NULL !== $exception) $this->exceptions[$name]= $exception;
      return $this->exceptions[$name];
    }
  
    /**
     * Register or retrieve a mapping for a package
     *
     * @access  public
     * @param   string token
     * @param   string class fully qualified class name
     * @return  string fully qualified class name
     */
    function packageMapping($name, $replace= NULL) {
      if (NULL !== $replace) $this->packages[$name]= $replace;
      return strtr($name, $this->packages);
    }
    
    /**
     * Retrieve serialized representation of a variable
     *
     * @access  public
     * @param   &remote.protocol.SerializedData serialized
     * @param   array context default array()
     * @return  &mixed
     * @throws  lang.ClassNotFoundException if a class cannot be found
     * @throws  lang.FormatException if an error is encountered in the format 
     */  
    function &valueOf(&$serialized, $context= array()) {
      static $types= NULL;
      
      if (!$types) $types= array(
        'N'   => 'void',
        'b'   => 'boolean',
        'i'   => 'integer',
        'd'   => 'double',
        's'   => 'string',
        'B'   => new ClassReference('lang.types.Byte'),
        'S'   => new ClassReference('lang.types.Short'),
        'f'   => new ClassReference('lang.types.Float'),
        'l'   => new ClassReference('lang.types.Long'),
        'a'   => 'array',
        'A'   => new ClassReference('lang.types.ArrayList'),
        'T'   => new ClassReference('util.Date')
      );

      $token= $serialized->buffer{$serialized->offset};
      $serialized->offset+= 2; 
      switch ($token) {
        case 'N': {     // null
          $value= NULL;
          return $value;
        }

        case 'b': {     // booleans
          $value= (bool)$serialized->consumeWord();
          return $value;
        }

        case 'i': {     // integers
          $value= (int)$serialized->consumeWord();
          return $value;
        }

        case 'd': {     // decimals
          $value= (float)$serialized->consumeWord();
          return $value;
        }

        case 's': {     // strings
          $value= $serialized->consumeString();
          return $value;
        }

        case 'a': {     // arrays
          $a= array();
          $size= $serialized->consumeSize();
          $serialized->offset++;  // Opening "{"
          for ($i= 0; $i < $size; $i++) {
            $key= $this->valueOf($serialized, $context);
            $a[$key]= &$this->valueOf($serialized, $context);
          }
          $serialized->offset++;  // Closing "}"
          return $a;
        }

        case 'E': {     // generic exceptions
          $instance= &new ExceptionReference($serialized->consumeString());
          $size= $serialized->consumeSize();
          $serialized->offset++;  // Opening "{"
          for ($i= 0; $i < $size; $i++) {
            $member= $this->valueOf($serialized, $context);
            $instance->{$member}= &$this->valueOf($serialized, $context);
          }
          $serialized->offset++; // Closing "}"
          return $instance;
        }
        
        case 'O': {     // generic objects
          $name= $serialized->consumeString();
          try(); {
            $class= &XPClass::forName($this->packageMapping($name));
          } if (catch('ClassNotFoundException', $e)) {
            $instance= &new UnknownRemoteObject($name);
            $size= $serialized->consumeSize();
            $serialized->offset++;  // Opening "{"
            for ($i= 0; $i < $size; $i++) {
              $member= $this->valueOf($serialized, $context);
              $members[$member]= &$this->valueOf($serialized, $context);
            }
            $serialized->offset++; // Closing "}"
            $instance->__members= $members;
            return $instance;
          }

          $instance= &$class->newInstance();
          $size= $serialized->consumeSize();
          $serialized->offset++;  // Opening "{"
          for ($i= 0; $i < $size; $i++) {
            $member= $this->valueOf($serialized, $context);
            $instance->{$member}= &$this->valueOf($serialized, $context);
          }
          $serialized->offset++; // Closing "}"
          return $instance;
        }

        case 'c': {     // builtin classes
          $type= $serialized->consumeWord();
          if (!isset($types[$type])) {
            return throw(new FormatException('Unknown type token "'.$type.'"'));
          }
          return $types[$type];
        }
        
        case 'C': {     // generic classes
          $value= &new ClassReference($this->packageMapping($serialized->consumeString()));
          return $value;
        }

        default: {      // default, check if we have a mapping
          if (!($mapping= &$this->mapping($token, $m= NULL))) {
            return throw(new FormatException(
              'Cannot deserialize unknown type "'.$token.'" ('.$serialized->toString().')'
            ));
          }

          return $mapping->valueOf($this, $serialized, $context);
        }
      }
    }
  }
?>
