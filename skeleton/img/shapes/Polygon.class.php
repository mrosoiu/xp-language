<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Shape class representing a polygon
   *
   * <code>
   *   $i= &new PngImage(300, 300);
   *   $i->create();
   *   $blue= &$i->allocate(new Color('#0000cc'));
   *   $i->draw(new PolygonShape($blue, array(
   *     40,    // x1
   *     50,    // y1
   *     20,    // x2
   *     240,   // y2
   *     60,    // x3
   *     60,    // y3
   *     240,   // x4
   *     20,    // y4
   *     50,    // x5
   *     40,    // y5
   *     10,    // x6
   *     10,    // y6    
   *   )));
   *   $i->toFile(new File('out.png'));
   * </code>
   *
   * @see xp://img.Image
   */
  class Polygon extends Object {
    var
      $col=     NULL,
      $points=  array(),
      $fill=    FALSE;

    /**
     * Constructor
     *
     * @access  public
     * @param   &img.Color col color
     * @param   int[] points
     * @param   bool fill default FALSE
     */ 
    function __construct(&$col, $points, $fill= FALSE) {
      $this->col= &$col;
      $this->points= $points;
      $this->fill= $fill;
      
    }
    
    /**
     * Draws this object onto an image
     *
     * @access  public
     * @param   &img.Image image
     * @return  mixed
     */
    function draw(&$image) { }
      if ($this->fill) return imagefilledpolygon(
        $image->handle,
        $this->points,
        sizeof($this->points) / 2,
        $this->col->handle
      ); else return imagepolygon(
        $image->handle,
        $this->points,
        sizeof($this->points) / 2,
        $this->col->handle
      );
    }

  } implements(__FILE__, 'img.Drawable');
?>
