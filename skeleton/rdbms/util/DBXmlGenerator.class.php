<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('rdbms.DBTable', 'xml.Tree', 'lang.System');
  
  /**
   * Generate an XML representation of a database table
   *
   * @see   xp://rdbms.DBTable
   */
  class DBXmlGenerator extends Object {
    var
      $doc= NULL;
      
    /**
     * Constructor
     *
     * @access  public
     */
    function __construct() {
      $this->doc= &new Tree();
      
    }

    /**
     * Create XML from a DBTable
     *
     * @model   static
     * @access  public
     * @param   &rdbms.DBTable table
     * @param   string dbhost
     * @param   string database
     * @return  &rdbms.util.DBXmlGenerator object
     */    
    function &createFromTable(&$table, $dbhost, $database) {
      if (!is_a($table, 'DBTable')) {
        return throw(new IllegalArgumentException('Argument table is not a DBTable object'));
      }
      
      $g= &new DBXmlGenerator();
      $g->doc->root->setAttribute('created_at', date('r'));
      $g->doc->root->setAttribute('created_by', System::getProperty('user.name'));
      
      $t= &$g->doc->root->addChild(new Node('table', NULL, array(
        'name'     => $table->name,
        'dbhost'   => $dbhost,
        'database' => $database
      )));
      
      // Attributes
      if ($attr= &$table->getFirstAttribute()) do {
        $t->addChild(new Node('attribute', NULL, array(
          'name'     => trim($attr->getName()),
          'type'     => $attr->getTypeString(),
          'identity' => $attr->isIdentity()  ? 'true' : 'false',
          'typename' => $attr->typeName(),
          'nullable' => $attr->isNullable() ? 'true' : 'false',
        )));
      } while ($attr= &$table->getNextAttribute());

      // Attributes
      if ($index= &$table->getFirstIndex()) do {
        $n= &$t->addChild(new Node('index', NULL, array(
          'name'    => trim($index->getName()),
          'unique'  => $index->isUnique() ? 'true' : 'false',
          'primary' => $index->isPrimaryKey() ? 'true' : 'false',
        )));
        foreach ($index->getKeys() as $key) {
          $n->addChild(new Node('key', $key));
        }
      } while ($index= &$table->getNextIndex());
      
      return $g;
    }

    /**
     * Get XML source
     *
     * @access  public
     * @return  string xml representation
     */    
    function getSource() {
      return $this->doc->getSource(FALSE);
    }
  }
?>
