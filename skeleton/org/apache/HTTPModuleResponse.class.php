<?php
/* Diese Klasse ist Teil des XP-Frameworks
 *
 * $Id$
 */
 
  define('HTTP_OK',                     200);
  define('HTTP_MOVED_TEMPORARILY',      302);
  define('HTTP_NOT_FOUND',              404);
  define('HTTP_INTERNAL_SERVER_ERROR',  500);

  /**
   * Kapselt den HTTP-Response des HTTP-Moduls
   *
   * @see org.apache.HTTPModule
   */  
  class HTTPModuleResponse extends Object {
    var
      $content=         '',
      $statusCode=      200,
      $headers=         array();
    
    /**
     * F�gt dem Response einen Header hinzu
     *
     * @access  public
     * @param   string name Header
     * @param   string value Header-Wert
     */
    function addHeader($name, $value) {
      $this->headers[$name]= $value;
    }
    
    /**
     * Diese Methode schickt die HTTP-Response-Header
     *
     * @access  public
     */  
    function sendHeaders() {

      // Statuscode senden
      header('HTTP/1.1 '.$this->statusCode);
      
      // Weitere Header
      foreach ($this->headers as $key=> $val) {
        header($key.': '.$val);
      }
    }
    
    function sendContent() {
      echo $this->getContent();
    }

    /**
     * Gibt den Seiten-Content zur�ck
     *
     * @access  public
     * @return  string Content
     */
    function getContent() {
      return $this->content;
    }
  }
?>
