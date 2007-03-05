<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'remote.protocol.Serializer',
    'net.xp_framework.unittest.remote.Person',
    'util.Hashmap'
  );

  /**
   * Unit test for Serializer class
   *
   * @see      xp://remote.Serializer
   * @purpose  TestCase
   */
  class SerializerTest extends TestCase {

    /**
     * Setup testcase
     *
     */
    public function setUp() {
      $this->serializer= new Serializer();
    }
    
    /**
     * Test serialization of NULL
     *
     */
    #[@test]
    public function representationOfNull() {
      $this->assertEquals('N;', $this->serializer->representationOf($var= NULL));
    }

    /**
     * Test serialization of Shorts
     *
     */
    #[@test]
    public function representationOfShorts() {
      $this->assertEquals('S:300;', $this->serializer->representationOf(new Short(300)));
      $this->assertEquals('S:-300;', $this->serializer->representationOf(new Short(-300)));
    }

    /**
     * Test serialization of longs
     *
     */
    #[@test]
    public function representationOfBytes() {
      $this->assertEquals('B:127;', $this->serializer->representationOf(new Byte(127)));
      $this->assertEquals('B:-128;', $this->serializer->representationOf(new Byte(-128)));
    }

    /**
     * Test serialization of booleans
     *
     */
    #[@test]
    public function representationOfBooleans() {
      $this->assertEquals('b:1;', $this->serializer->representationOf($var= TRUE));
      $this->assertEquals('b:0;', $this->serializer->representationOf($var= FALSE));
    }

    /**
     * Test serialization of integers
     *
     */
    #[@test]
    public function representationOfIntegers() {
      $this->assertEquals('i:6100;', $this->serializer->representationOf($var= 6100));
      $this->assertEquals('i:-6100;', $this->serializer->representationOf($var= -6100));
    }

    /**
     * Test serialization of longs
     *
     */
    #[@test]
    public function representationOfLongs() {
      $this->assertEquals('l:6100;', $this->serializer->representationOf(new Long(6100)));
      $this->assertEquals('l:-6100;', $this->serializer->representationOf(new Long(-6100)));
    }

    /**
     * Test serialization of floats
     *
     */
    #[@test]
    public function representationOfFloats() {
      $this->assertEquals('d:0.1;', $this->serializer->representationOf($var= 0.1));
      $this->assertEquals('d:-0.1;', $this->serializer->representationOf($var= -0.1));
    }

    /**
     * Test serialization of doubles
     *
     */
    #[@test]
    public function representationOfDoubles() {
      $this->assertEquals('d:0.1;', $this->serializer->representationOf(new Double(0.1)));
      $this->assertEquals('d:-0.1;', $this->serializer->representationOf(new Double(-0.1)));
    }

    /**
     * Test serialization of the string "Hello World"
     *
     */
    #[@test]
    public function representationOfString() {
      $this->assertEquals('s:11:"Hello World";', $this->serializer->representationOf($var= 'Hello World'));
    }
    
    /**
     * Test serialization of an array containing three integers 
     * (1, 2 and 5)
     *
     */
    #[@test]
    public function representationOfIntegerArray() {
      $this->assertEquals(
        'a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}', 
        $this->serializer->representationOf($var= array(1, 2, 5))
      );
    }
    
    /**
     * Test serialization of an array containing two strings 
     * ("More" and "Power")
     *
     */
    #[@test]
    public function representationOfStringArray() {
      $this->assertEquals(
        'a:2:{i:0;s:4:"More";i:1;s:5:"Power";}', 
        $this->serializer->representationOf($var= array('More', 'Power'))
      );
    }
    
    /**
     * Test serialization of a date object
     *
     */
    #[@test]
    public function representationOfDate() {
      $this->assertEquals('T:1122644265;', $this->serializer->representationOf(new Date(1122644265)));
    }

    /**
     * Test serialization of a hashmap
     *
     */
    #[@test]
    public function representationOfHashmap() {
      $h= new Hashmap();
      $h->put('key', 'value');
      $h->put('number', '6100');

      $this->assertEquals(
        'a:2:{s:3:"key";s:5:"value";s:6:"number";s:4:"6100";}', 
        $this->serializer->representationOf($h)
      );
    }

    /**
     * Test serialization of a hashmap with mixed values
     *
     */
    #[@test]
    public function representationOfMixedHashmap() {
      $h= new Hashmap();
      $h->put('key', 'value');
      $h->put('number', 6100);

      $this->assertEquals(
        'a:2:{s:3:"key";s:5:"value";s:6:"number";i:6100;}', 
        $this->serializer->representationOf($h)
      );
    }
    
    /**
     * Test serialization of a generic value object
     *
     * @see     xp://Person
     */
    #[@test]
    public function representationOfValueObject() {
      $this->assertEquals(
        'O:39:"net.xp_framework.unittest.remote.Person":2:{s:2:"id";i:1549;s:4:"name";s:11:"Timm Friebe";}',
        $this->serializer->representationOf(new Person())
      );
    }

    /**
     * Test deserialization of an integer
     *
     */
    #[@test]
    public function valueOfInt() {
      $this->assertEquals(
        1,
        $this->serializer->valueOf(new SerializedData('i:1;'))
      );
    }

    /**
     * Test deserialization of a byte
     *
     */
    #[@test]
    public function valueOfByte() {
      $this->assertEquals(
        new Byte(1),
        $this->serializer->valueOf(new SerializedData('B:1;'))
      );
    }

    /**
     * Test deserialization of a long
     *
     */
    #[@test]
    public function valueOfLong() {
      $this->assertEquals(
        new Long(12345),
        $this->serializer->valueOf(new SerializedData('l:12345;'))
      );
    }

    /**
     * Test deserialization of a float
     *
     */
    #[@test]
    public function valueOfFloat() {
      $this->assertEquals(
        new Float(1.5),
        $this->serializer->valueOf(new SerializedData('f:1.5;'))
      );
    }

    /**
     * Test deserialization of a double
     *
     */
    #[@test]
    public function valueOfDouble() {
      $this->assertEquals(
        1.5,
        $this->serializer->valueOf(new SerializedData('d:1.5;'))
      );
    }

    /**
     * Test deserialization of an integer
     *
     * @see     xp://Person
     */
    #[@test]
    public function valueOfShorts() {
      $this->assertEquals(
        new Short(1),
        $this->serializer->valueOf(new SerializedData('S:1;'))
      );
    }
    
    /**
     * Test deserialization of a date
     *
     */
    #[@test]
    public function valueOfDates() {
      $this->assertEquals(
        new Date(328312800),
        $this->serializer->valueOf(new SerializedData('T:328312800;'))
      );
    }

    /**
     * Test deserialization of an integer
     *
     */
    #[@test]
    public function valueOfException() {
      $exception= $this->serializer->valueOf(new SerializedData(
        'E:46:"java.lang.reflect.UndeclaredThrowableException":3:{'.
        's:7:"message";s:12:"*** BLAM ***";'.
        's:5:"trace";a:1:{i:0;t:4:{s:4:"file";s:9:"Test.java";s:5:"class";s:4:"Test";s:6:"method";s:4:"main";s:4:"line";i:10;}}'.
        's:5:"cause";N;'.
        '}'
      ));
      $this->assertClass($exception, 'remote.ExceptionReference');
      $this->assertEquals('java.lang.reflect.UndeclaredThrowableException', $exception->referencedClassname);
      $this->assertEquals('*** BLAM ***', $exception->getMessage());
      with ($trace= $exception->getStackTrace()); {
        $this->assertEquals(1, sizeof($trace));
        $this->assertClass($trace[0], 'remote.RemoteStackTraceElement');
        $this->assertEquals('Test.java', $trace[0]->file);
        $this->assertEquals('Test', $trace[0]->class);
        $this->assertEquals('main', $trace[0]->method);
        $this->assertEquals(10, $trace[0]->line);
      }
      $this->assertNull($exception->getCause());
    }

    /**
     * Test deserialization of an arraylist
     *
     * @see     xp://Person
     */
    #[@test]
    public function valueOfArrayList() {
      $return= $this->serializer->valueOf(
        new SerializedData('A:2:{O:39:"net.xp_framework.unittest.remote.Person":2:{s:2:"id";i:1549;s:4:"name";s:11:"Timm Friebe";}s:5:"World";}'
      ));
      $this->assertClass($return, 'lang.types.ArrayList');
      $this->assertEquals(2, sizeof($return->values));
      $this->assertEquals(new Person(), $return->values[0]);
      $this->assertEquals('World', $return->values[1]);
    }

    /**
     * Test deserialization of an encapsed arraylist
     *
     */
    #[@test]
    public function arrayList() {
      $list= $this->serializer->valueOf(
        new SerializedData('A:1:{a:2:{s:2:"la";s:2:"la";s:3:"foo";A:2:{a:1:{s:13:"verschachteln";s:7:"istToll";}s:6:"barbar";}}}')
      );
      $this->assertEquals($list, new ArrayList(array(
        array(
          'la'  => 'la',
          'foo' => new ArrayList(array(
            array('verschachteln' => 'istToll'),
            'barbar'
          ))
        ))
      ));
    }
    
    /**
     * Test deserialization of a classreference
     *
     */
    #[@test]
    public function genericClass() {
      $class= $this->serializer->valueOf(new SerializedData('C:47:"net.xp_framework.easc.reflect.MethodDescription"'));
      $this->assertTrue(is('remote.ClassReference', $class));
      $this->assertEquals("net.xp_framework.easc.reflect.MethodDescription", $class->referencedName());
    }
    
    /**
     * Test deserialization of a package-mapped classreference
     *
     */
    #[@test]
    public function genericPackageMappedClass() {
      $this->serializer->packageMapping('net.xp_framework.easc.reflect', 'remote.reflect');
      
      $class= $this->serializer->valueOf(new SerializedData('C:47:"net.xp_framework.easc.reflect.MethodDescription"'));
      $this->assertTrue(is('remote.ClassReference', $class));
      $this->assertEquals("remote.reflect.MethodDescription", $class->referencedName());
    }

    /**
     * Check serialization through custom class mappings. Check that the serialization
     * is always carried through by the best matching serializer mapping.
     *
     */
    #[@test]
    public function bestMapping() {
      $cl= ClassLoader::getDefault();
      $fooClass= $cl->defineClass('net.xp_framework.unittest.remote.FooClass', 'class FooClass extends Object { }');
      $barClass= $cl->defineClass('net.xp_framework.unittest.remote.BarClass', 'class BarClass extends FooClass { }');
      
      // Both must be serialized with the FOO mapping, because both are Foo or Foo-derived objects.
      $this->serializer->mapping('FOO', newinstance('remote.protocol.SerializerMapping', array(), '{
        function handledClass() { return XPClass::forName("net.xp_framework.unittest.remote.FooClass"); }
        function representationOf($serializer, $value, $context= array()) { return "FOO:"; }
        public function valueOf($serializer, $serialized, $context= array()) { return NULL; }
      }'));
      $this->assertEquals('FOO:', $this->serializer->representationOf(new FooClass()));
      $this->assertEquals('FOO:', $this->serializer->representationOf(new BarClass()));
      
      // Add more concrete mapping for BAR. Foo must still be serialized with FOO, but the BarClass-object
      // has a better matching mapping.
      $this->serializer->mapping('BAR', newinstance('remote.protocol.SerializerMapping', array(), '{
        function handledClass() { return XPClass::forName("net.xp_framework.unittest.remote.BarClass"); }
        function representationOf($serializer, $value, $context= array()) { return "BAR:"; }
        function valueOf($serializer, $serialized, $context= array()) { return NULL; }
      }'));
      $this->assertEquals('FOO:', $this->serializer->representationOf(new FooClass()));
      $this->assertEquals('BAR:', $this->serializer->representationOf(new BarClass()));
    }
  }
?>
