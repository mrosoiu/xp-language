<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  define('BCS_DEFAULT_CHUNK_SIZE', 0xFFFF);

  /**
   * Byte counted string. The layout is the following:
   *
   * <pre>
   *      1     2     3     4     5   ...
   *   +-----+-----+-----+-----+-----+...+-----+-----+
   *   |   length  | mor |  0  |  1  |...| n-1 |  n  |
   *   +-----+-----+-----+-----+-----+...+-----+-----+
   *   |<--- 3 bytes --->|<-------- n bytes -------->|
   * </pre>
   *
   * The first three bytes are "control bytes":
   * <ul>
   *   <li>The first two bytes contain the chunk's length</li>
   *   <li>The third byte contains whether there are more chunks</li>
   * </ul>
   *
   * The rest of the bytes contains the string.
   * 
   * @purpose  Wire format encoding
   */
  class ByteCountedString extends Object {
    var
      $string= '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string string default ''
     */
    function __construct($string= '') {
      $this->string= utf8_encode($string);
    }
    
    /**
     * Return length of encoded string based on specified chunksize
     *
     * @access  public
     * @param   int chunksize default BCS_DEFAULT_CHUNK_SIZE
     * @return  int
     */
    function length($chunksize= BCS_DEFAULT_CHUNK_SIZE) {
      return strlen($this->string) + 3 * (int)ceil(strlen($this->string) / $chunksize);
    }

    /**
     * Write to a given stream using a specified chunk size
     *
     * @access  public
     * @param   &io.Stream stream
     * @param   int chunksize default BCS_DEFAULT_CHUNK_SIZE
     */
    function writeTo(&$stream, $chunksize= BCS_DEFAULT_CHUNK_SIZE) {
      $length= strlen($this->string);
      $offset= 0;

      do {
        $chunk= $length > $chunksize ? $chunksize : $length;
        $stream->write(pack('nc', $chunk, $length- $chunk > 0));
        $stream->write(substr($this->string, $offset, $chunk));

        $offset+= $chunk;
        $length-= $chunk;
      } while ($length > 0);
    }
    
    /**
     * Read a specified number of bytes from a given stream
     *
     * @model   static
     * @access  protected
     * @param   &io.Stream stream
     * @param   int length
     * @return  string
     */
    function readFully(&$stream, $length) {
      $return= '';
      while (strlen($return) < $length) {
        if (0 == strlen($buf= $stream->readBinary($length - strlen($return)))) return;
        $return.= $buf;
      }
      return $return;
    }
    
    /**
     * Read from a stream
     *
     * @model   static
     * @access  public
     * @param   &io.Stream stream
     * @return  string
     */
    function readFrom(&$stream) {
      $s= '';
      do {
        if (FALSE === ($ctl= unpack('nlength/cnext', ByteCountedString::readFully($stream, 3)))) return;
        $s.= ByteCountedString::readFully($stream, $ctl['length']);
      } while ($ctl['next']);
      
      return utf8_decode($s);
    }
  }
?>
