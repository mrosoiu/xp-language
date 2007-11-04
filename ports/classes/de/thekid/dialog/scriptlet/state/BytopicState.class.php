<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('de.thekid.dialog.scriptlet.AbstractDialogState');

  /**
   * Handles /xml/bytopic
   *
   * @purpose  State
   */
  class BytopicState extends AbstractDialogState {
    protected
      $nodeHandlers = array();
  
    /**
     * Constructor
     *
     */
    public function __construct() {
      for ($c= $this->getClass(), $m= $c->getMethods(), $i= 0, $s= sizeof($m); $i < $s; $i++) {
        $m[$i]->hasAnnotation('handles') && (
          $this->nodeHandlers[$m[$i]->getAnnotation('handles')]= $m[$i]
        );
      }
    }
    
    /**
     * Handler for albums
     *
     * @param   de.thekid.dialog.Album album
     * @return  xml.Node node
     */
    #[@handles('de.thekid.dialog.Album')]
    public function albumNode($album) {
      $child= new Node('entry', NULL, array(
        'name'          => $album->getName(),
        'title'         => $album->getTitle(),
        'num_images'    => $album->numImages(),
        'num_chapters'  => $album->numChapters()
      ));
      $child->addChild(Node::fromObject($album->createdAt, 'created'));
      return $child;
    }

    /**
     * Handler for updates
     *
     * @param   de.thekid.dialog.Update update
     * @return  xml.Node node
     */
    #[@handles('de.thekid.dialog.Update')]
    public function updateNode($update) {
      $child= new Node('entry', NULL, array(
        'album'         => $update->getAlbumName(),
        'title'         => $update->getTitle()
      ));
      $child->addChild(Node::fromObject($update->date, 'created'));
      return $child;
    }

    /**
     * Handler for single shots
     *
     * @param   de.thekid.dialog.SingleShot shot
     * @return  xml.Node node
     */
    #[@handles('de.thekid.dialog.SingleShot')]
    public function shotNode($shot) {
      $child= new Node('entry', NULL, array(
        'name'      => $shot->getName(),
        'title'     => $shot->getTitle()
      ));
      $child->addChild(Node::fromObject($shot->date, 'created'));
      return $child;
    }

    /**
     * Handler for entry collections
     *
     * @param   de.thekid.dialog.EntryCollection collection
     * @return  xml.Node node
     */
    #[@handles('de.thekid.dialog.EntryCollection')]
    public function collectionNode($collection) {
      $node= new Node('entry', NULL, array(
        'name'          => $collection->getName(),
        'title'         => $collection->getTitle(),
        'num_entries'   => $collection->numEntries()
      ));
      $node->addChild(Node::fromObject($collection->created, 'created'));
      return $node;
    }
    
    /**
     * Process this state.
     *
     * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
     * @param   scriptlet.xml.XMLScriptletResponse response
     * @param   scriptlet.xml.workflow.Context context
     */
    public function process($request, $response, $context) {
      sscanf($request->getQueryString(), 'page%d', $page);
      $index= $this->getIndexTopics((int)$page);

      // Add paging information
      $response->addFormResult(new Node('pager', NULL, array(
        'offset'  => (int)$page,
        'total'   => $index['total'],
        'perpage' => $index['perpage']
      )));

      $node= $response->addFormResult(new Node('topics'));
      foreach ($index['entries'] as $name) {
        $topic= $this->getEntryFor($name);
        $t= $node->addChild(new Node('topic', NULL, array(
          'name'       => $topic->getName(),
          'title'      => $topic->getTitle()
        )));
        $t->addChild(Node::fromObject($topic->getCreatedAt(), 'created'));
        
        // Add origins grouped by year
        $o= $t->addChild(new Node('origins'));
        $origins= $classes= array();
        foreach ($topic->origins() as $name) {
          $entry= $this->getEntryFor($name);
          if (!isset($this->nodeHandlers[$entry->getClassName()])) {
            throw new FormatException('Index contains unknown element "'.$entry->getClassName().'"');
          }

          if ($child= $this->nodeHandlers[$entry->getClassName()]->invoke($this, array($entry))) {
            $y= $entry->getDate()->toString('Y');
            if (!isset($origins[$y])) {
              $origins[$y]= new Node('year', NULL, array('num' => $y));
            }
            $origins[$y]->addChild($child)->setAttribute('type', $entry->getClassName());
          }

          $classes[$name]= $entry->getClassName();
        }
        krsort($origins);
        foreach ($origins as $byYear) {
          $o->addChild($byYear);
        }
        
        // Add featured images
        $images= $t->addChild(new Node('featured'));
        foreach ($topic->featuredImages() as $origin => $image) {
          $image= $images->addChild(Node::fromObject($image, 'image'));
          $image->setAttribute('origin-name', $origin);
          $image->setAttribute('origin-class', $classes[$origin]);
        }
      }
    }
  }
?>
