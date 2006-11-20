<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('lang.MethodNotImplementedException');

  /**
   * Handles SOAP Interop requests as proposed by
   * Whitemesa.
   *
   * @see      http://www.whitemesa.com/interop/proposal2.html
   * @purpose  SOAP Interop server
   */
  class Round2Handler extends Object {

    /**
     * Checks the type of given object.
     *
     * @access  private
     * @param   string type
     * @param   &mixed object
     * @throws  lang.IllegalArgumentException
     */
    function _assertType($type, &$object) {
      if ($type != xp::typeOf($object))
        return throw (new IllegalArgumentException('Object not of expected type '.$type.', but '.xp::typeOf($object).' with value '.var_export($object, 1)));
    }
    
    /**
     * Checks all entries in an array for correct type
     *
     * @access  private
     * @param   string type
     * @param   &array array
     * @throws  lang.IllegalArgumentException
     */
    function _assertSubtype($type, &$array) {
      foreach (array_keys($array) as $key) {
        if ($type != xp::typeOf($array[$key]))
          return throw (new IllegalArgumentException('Object (in array) not of expected type '.$type.', but '.xp::typeOf($array[$key]).' with value '.var_export($array[$key], 1)));
      }
    }
  
    /**
     * Echoes a given string.
     *
     * @access  public
     * @param   string inputString
     * @return  string
     */
    #[@webmethod]
    function echoString($inputString) {
      $this->_assertType('string', $inputString);
      return $inputString;
    }
    
    /**
     * Echoes a given string array.
     *
     * @access  public
     * @param   string[] inputStringArray
     * @return  string[]
     * @throws  lang.IllegalArgumentException
     */
    #[@webmethod]
    function echoStringArray($inputStringArray) {
      $this->_assertType('array', $inputStringArray);
      $this->_assertSubtype('string', $inputStringArray);
      return $inputStringArray;
    }
    
    /**
     * Echoes an integer
     *
     * @access  public
     * @param   int inputInteger
     * @return  int
     */
    #[@webmethod]
    function echoInteger($inputInteger) {
      $this->_assertType('integer', $inputInteger);
      return $inputInteger;
    }
    
    /**
     * Echoes an array of integers
     *
     * @access  public
     * @param   int[] inputIntegerArray
     * @return  int[]
     * @throws  lang.IllegalArgumentException
     */
    #[@webmethod]
    function echoIntegerArray($inputIntegerArray) {
      $this->_assertType('array', $inputIntegerArray);
      $this->_assertSubtype('integer', $inputIntegerArray);
      return $inputIntegerArray;
    }
    
    /**
     * Echoes a float
     *
     * @access  public
     * @param   float inputFloat
     * @return  float
     */
    #[@webmethod]
    function echoFloat($inputFloat) {
      $this->_assertType('double', $inputFloat);
      return $inputFloat;
    }
    
    /**
     * Echoes an array of floats
     *
     * @access  public
     * @param   float[] inputFloatArray
     * @return  float[]
     * @throws  lang.IllegalArgumentException
     */
    #[@webmethod]
    function echoFloatArray($inputFloatArray) {
      $this->_assertType('array', $inputFloatArray);
      $this->_assertSubtype('double', $inputFloatArray);
      return $inputFloatArray;
    }
    
    /**
     * Echoes a struct.
     *
     * @access  public
     * @param   mixed[] inputStruct
     * @return  mixed[]
     * @throws  lang.IllegalArgumentException
     */
    #[@webmethod]
    function echoStruct($inputStruct) {
      $this->_assertType('array',   $inputStruct);
      $this->_assertType('string',  $inputStruct['varString']);
      $this->_assertType('integer', $inputStruct['varInt']);
      $this->_assertType('double',  $inputStruct['varFloat']);
      return $inputStruct;
    }
    
    /**
     * Echoes an array of structs
     *
     * @access  public
     * @param   mixed[] inputStructArray
     * @return  mixed[]
     * @throws  lang.MethodNotImplementedException
     */
    #[@webmethod]
    function echoStructArray($inputStructArray) {
      $this->_assertType('array', $inputStructArray);
      foreach ($inputStructArray as $singleStruct) {
        $this->_assertType('string',  $singleStruct['varString']);
        $this->_assertType('integer', $singleStruct['varInt']);
        $this->_assertType('double',  $singleStruct['varFloat']);
      }
      return $inputStructArray;
    }
    
    /**
     * Echoes a void.
     *
     * @access  public
     * @return  NULL
     */
    #[@webmethod]
    function echoVoid() {
      return NULL;
    }
    
    /**
     * Echoes a base64 string
     *
     * @access  public
     * @param   string inputBase64
     * @return  string
     * @throws  lang.MethodNotImplementedException
     */
    #[@webmethod]
    function echoBase64($inputBase64) {
      $this->_assertType('webservices.soap.types.SOAPBase64Binary', $inputBase64);
      return $inputBase64;
    }
    
    /**
     * Echoes a hexbinary.
     *
     * @access  public
     * @param   string  inputHexBinary
     * @return  string
     * @throws  lang.MethodNotImplementedException
     */
    #[@webmethod]
    function echoHexBinary($inputHexBinary) {
      $this->_assertType('webservices.soap.types.SOAPHexBinary', $inputHexBinary);
      return $inputHexBinary;
    }
    
    /**
     * Echoes a date.
     *
     * @access  public
     * @param   &util.Date inputDate
     * @return  &util.Date
     * @throws  lang.IllegalArgumentException
     */
    #[@webmethod]
    function echoDate($inputDate) {
      $this->_assertType('util.Date', $inputDate);
      return $inputDate;
    }
    
    /**
     * Echoes a decimal.
     *
     * @access  public
     * @param   float inputDecimal
     * @return  float
     */
    #[@webmethod]
    function echoDecimal($inputDecimal) {
      $this->_assertType('double', $inputDecimal);
      return $inputDecimal;
    }
    
    /**
     * Echoes a boolean
     *
     * @access  public
     * @param   boolean inputBoolean
     * @return  boolean
     */
    #[@webmethod]
    function echoBoolean($inputBoolean) {
      $this->_assertType('boolean', $inputBoolean);
      return (bool)$inputBoolean;
    }
  }
?>
