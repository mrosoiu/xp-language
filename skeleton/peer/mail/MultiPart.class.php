<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */

  uses('peer.mail.MimePart');

  /**
   * Mail message
   *
   * @purpose  Wrap
   */
  class MultiPart extends MimePart {
    var 
      $parts     = array(),
      $charset   = '',
      $boundary  = '';

    var
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
    function __construct() {
      parent::__construct();
      $this->charset= '';
      for ($i= 0, $s= func_num_args(); $i < $s; $i++) {
        $this->addPart(func_get_arg($i));
      }
      $this->setBoundary('----=_Alternative_'.uniqid(time(), TRUE));
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
    function setBoundary($b) {
      $this->boundary= $b;
      // $this->contenttype= 'multipart/alternative; boundary="'.$this->boundary.'"';
      $this->setContenttype('multipart/alternative');
      $this->charset= '';
    }

    /**
     * Get Boundary
     *
     * @access  public
     * @return  string
     */
    function getBoundary() {
      return $this->boundary;
    }
    
    /**
     * Sets content-type and updates the header. The boundary will be appended
     * to the content-type header.
     *
     * @access  public
     * @param   string content-type
     */
    function setContenttype($c) {
      $this->contenttype= $c;
    }
    
    /**
     * Add a Mime Part
     *
     * @access  public
     * @param   &peer.mail.MimePart part
     * @return  &peer.mail.MimePart the part added
     * @throws  lang.IllegalArgumentException if part argument is not a peer.mail.MimePart
     */
    function &addPart(&$part) {
      if (!is_a($part, 'MimePart')) {
        return throw(new IllegalArgumentException(
          'Parameter part is not a peer.mail.MimePart (given: '.xp::typeOf($part).')'
        ));
      }
      $this->parts[]= &$part;
      return $part;
    }

    /**
     * Get a part
     *
     * @access  public
     * @param   int id default -1
     * @return  &peer.mail.MimePart part
     */
    function &getPart($id= -1) {

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
    function getBody() {
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
