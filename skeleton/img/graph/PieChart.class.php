<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses('img.shapes.Arc', 'img.graph.PieSlice');
  
  /**
   * Shape class representing a 3D Pie chart
   *
   * @see img.Image
   */
  class PieChart extends Object {
    var
      $slices       = array(),
      $perspective  = 0,
      $shadow       = 0,
      $fill         = 0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   int perspective default 0
     * @param   int shadow default 10 
     * @param   int fill default IMG_ARC_PIE one of IMG_ARC_* constants
     */ 
    function __construct($perspective= 0, $shadow= 10, $fill= IMG_ARC_PIE) {
      $this->perspective= $perspective;
      $this->shadow= $shadow;
      $this->fill= $fill;
    }
    
    /**
     * Add a pie slice to the data
     *
     * @access  public
     * @param   string key
     * @param   &img.graph.PieSlice a slice object
     * @return  &img.graph.PieSlice the slice object put in
     */
    function &add(&$slice) {
      $this->slices[]= &$slice;
      return $slice;
    }

    /**
     * Draws this object onto an image
     *
     * @access  public
     * @param   &img.Image image
     * @return  mixed
     */
    function draw(&$image) {
      $arc= &new Arc(
        NULL,
        $image->getWidth() / 2, 
        $image->getHeight() / 2, 
        $image->getWidth() / 2, 
        $image->getHeight() / 2 - $this->perspective, 
        0,
        0,
        $this->fill
      );
      $y= $arc->cy;
      for ($i= $arc->cy+ $this->shadow; $i >= $y; $i--) {
        $arc->s= 0;
        $arc->cy= $i;
        foreach (array_keys($this->slices) as $key) {
          $arc->col= &$this->slices[$key]->colors[$i != $y];
          $arc->e= $arc->s+ $this->slices[$key]->value * 3.6;
          $arc->draw($image);
          $arc->s= $arc->e;
        }
      }
    }

  } implements(__FILE__, 'img.Drawable');
?>
