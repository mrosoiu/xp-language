<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'text.doclet.SeeTaglet', 
    'text.doclet.ParamTaglet', 
    'text.doclet.ReturnTaglet', 
    'text.doclet.ThrowsTaglet',
    'text.doclet.SimpleTaglet',
    'text.doclet.ModelTaglet'
  );

  /**
   * Manages the taglets used by doclets. 
   *
   * @purpose  purpose
   */
  class TagletManager extends Object {
    var
      $taglets= array();
      
    /**
     * Static initializer. Registers builtin taglets
     *
     * @model   static
     * @access  public
     */
    function __static() {
      with ($self= &TagletManager::getInstance()); {
        $self->taglets['see']= &new SeeTaglet();
        $self->taglets['param']= &new ParamTaglet();
        $self->taglets['return']= &new ReturnTaglet();
        $self->taglets['throws']= &new ThrowsTaglet();
        $self->taglets['model']= &new ModelTaglet;
        
        // Simple taglets
        $s= &new SimpleTaglet();
        $self->taglets['purpose']= &$s;
        $self->taglets['access']= &$s;
        $self->taglets['deprecated']= &$s;
        $self->taglets['experimental']= &$s;
        $self->taglets['platform']= &$s;
        $self->taglets['test']= &$s;
        $self->taglets['doc']= &$s;
        $self->taglets['ext']= &$s;
      }
    }

    /**
     * Return the TagletManager's instance
     * 
     * @model   static
     * @access  public
     * @return  &text.doclet.TagletManager
     */
    function &getInstance() {
      static $instance= NULL;
      
      if (!$instance) $instance= new TagletManager();
      return $instance;
    }
    
    /**
     * Add a new tag
     *
     * @access  public
     * @param   string kind
     * @param   &text.doclet.Taglet taglet
     */
    function addCustomTag($kind, &$taglet) {
      $this->taglets[$kind]= &$taglet;
    }

    /**
     * Factory method
     *
     * @access  public
     * @param   &text.doclet.Doc holder
     * @param   string kind
     * @param   string text
     * @return  &text.doclet.Tag
     */
    function &make(&$holder, $kind, $text) {
      if (!isset($this->taglets[$kind])) {
        return throw(new IllegalArgumentException('Unknown taglet kind "'.$kind.'"'));
      }
      
      return $this->taglets[$kind]->tagFrom($holder, $kind, $text);
    }
  }
?>
