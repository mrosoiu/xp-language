<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('text.doclet.ModelTag');

  /**
   * A taglet that represents the model tag. 
   *
   * @see      xp://text.doclet.TagletManager
   * @purpose  Taglet
   */
  class ModelTaglet extends Object {
     
    /**
     * Create tag from text
     *
     * @access  public
     * @param   &text.doclet.Doc holder
     * @param   string kind
     * @param   string text
     * @return  &text.doclet.Tag
     */ 
    function &tagFrom(&$holder, $kind, $text) {
      return new ModelTag($kind, $text);
    }

  } implements(__FILE__, 'text.doclet.Taglet');
?>
