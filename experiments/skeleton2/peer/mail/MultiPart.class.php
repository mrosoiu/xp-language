<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  uses('peer.mail.MimePart');

  /**
   * Mail message
   *
   * @see
   * @purpose  Wrap
   */
  class MultiPart extends MimePart {
    public 
      $parts     = array(),
      $charset   = '',
      $boundary  = '';
      
    public
      $_ofs      = 0;
      
    /**
     * Constructor. Also generates a boundary of the form
     * <pre>
     * ----=_Alternative_10424693873e22d20b43b490.00112051
     * </pre>
     *
     * @param   &peer.mail.MimePart* parts
     * @access  public
     */
    public function __construct() {
      parent::__construct();
      $this->charset= '';
      for ($i= 0, $s= func_num_args(); $i < $s; $i++) {
        self::addPart(func_get_arg($i));
      }
      self::setBoundary('----=_Alternative_'.uniqid(time(), TRUE));
    }

    /**
     * Set boundary and updates Content-Type header. Note: A boundary is generated 
     * upon instanciation, so this is usually not needed!
     *
     * Also sets the charset to an empty string.
     *
     * @access  public
     * @param   string b the new boundary
     */
    public function setBoundary($b) {
      $this->boundary= $b;
      // $this->contenttype= 'multipart/alternative; boundary="'.$this->boundary.'"';
      self::setContenttype('multipart/alternative');
      $this->charset= '';
    }
    
    /**
     * Sets content-type and updates the header. The boundary will be appended
     * to the content-type header.
     *
     * @access  public
     * @param   string content-type
     */
    public function setContenttype($c) {
      $this->contenttype= $c;
    }
    
    /**
     * Add a Mime Part
     *
     * @access  public
     * @param   &peer.mail.MimePart part
     * @throws  IllegalArgumentException if part argument is not a peer.mail.MimePart
     */
    public function addPart(&$part) {
      if (!is_a($part, 'MimePart')) {
        throw (new IllegalArgumentException(
          'Parameter part is not a peer.mail.MimePart (given: '.xp::typeOf($part).')'
        ));
      }
      $this->parts[]= $part;
    }

    /**
     * Get a part
     *
     * @access  public
     * @param   int id default -1
     * @return  &peer.mail.MimePart part
     */
    public function getPart($id= -1) {

      // Iterative use
      if (-1 == $id) $id= $this->_ofs++;
      
      // EOL
      if (!isset($this->parts[$id])) {
        $this->_ofs= 0;
        return NULL;
      }
      
      return $this->parts[$id];
    }
    
    /**
     * Get message body.
     *
     * @see     xp://peer.mail.Message#getBody
     * @access  public
     * @return  string
     */
    public function getBody() {
      $body= '';
      
      if (1 == ($size= sizeof($this->parts)) && $this->parts[0]->isInline()) {
        return (
          $this->parts[0]->getHeaderString().
          "\n".
          $this->parts[0]->getBody()
        );
      }

      for ($i= 0, $s= sizeof($this->parts); $i < $s; $i++) {
        $body.= (
          '--'.trim($this->boundary)."\n".
          $this->parts[$i]->getHeaderString().
          "\n".
          $this->parts[$i]->getBody().
          "\n\n"
        );
      }
      
      // End boundary
      return $body.'--'.$this->boundary."--\n";
    }

  }
?>
