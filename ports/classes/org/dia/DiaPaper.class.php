<?php
/*
 *
 * $Id:$
 */

  uses(
    'org.dia.DiaComposite'
  );

  /**
   * Represents a 'dia:composite type="paper"' node
   *
   */
  class DiaPaper extends DiaComposite {

    var
      $type= 'paper';

    /**
     * Initializes this Paper object
     *
     * @access  public
     */
    function initialize() {
      // default values
      $this->setName('A4');
      $this->setTopMargin(2.8);
      $this->setBottomMargin(2.8);
      $this->setLeftMargin(2.8);
      $this->setRightMargin(2.8);

      // default flags
      $this->setPortrait(TRUE);
      $this->setScaling(1);
      $this->setFitTo(TRUE);
      $this->setFitWidth(1);
      $this->setFitHeight(1);
    }

    /**
     * Returns the top margin of the paper
     *
     * @access  public
     * @return  float
     */
    function getTopMargin() {
      return $this->getChildValue('tmargin');
    }

    /**
     * Sets the top margin of the Paper
     *
     * @access  public
     * @param   float rmargin
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="tmargin"]/dia:real/@val', value= 'real')]
    function setTopMargin($tmargin) {
      $this->setReal('tmargin', $tmargin);
    }

    /**
     * Returns the bottom margin of the paper
     *
     * @access  public
     * @return  float
     */
    function getBottomMargin() {
      return $this->getChildValue('bmargin');
    }

    /**
     * Sets the bottom margin of the Paper
     *
     * @access  public
     * @param   float bmargin
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="bmargin"]/dia:real/@val', value= 'real')]
    function setBottomMargin($bmargin) {
      $this->setReal('bmargin', $bmargin);
    }

    /**
     * Returns the left margin of the paper
     *
     * @access  public
     * @return  float
     */
    function getLeftMargin() {
      return $this->getChildValue('lmargin');
    }

    /**
     * Sets the left margin of the Paper
     *
     * @access  public
     * @param   float lmargin
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="lmargin"]/dia:real/@val', value= 'real')]
    function setLeftMargin($lmargin) {
      $this->setReal('lmargin', $lmargin);
    }

    /**
     * Returns the right margin of the paper
     *
     * @access  public
     * @return  float
     */
    function getRightMargin() {
      return $this->getChildValue('rmargin');
    }

    /**
     * Sets the right margin of the Paper
     *
     * @access  public
     * @param   float rmargin
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="rmargin"]/dia:real/@val', value= 'real')]
    function setRightMargin($rmargin) {
      $this->setReal('rmargin', $rmargin);
    }

    /**
     * Returns TRUE if the paper has 'portrait' orientation, FALSE means
     * 'landscape'
     *
     * @access  public
     * @return  boole
     */
    function getPortrait() {
      return $this->getChildValue('is_portrait');
    }

    /**
     * Sets the 'is_portrait' attribute of the Paper object
     *
     * @access  public
     * @param   bool portrait
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="is_portrait"]/dia:boolean/@val', value= 'boolean')]
    function setPortrait($portrait) {
      $this->setBoolean('is_portrait', $portrait);
    }

    /**
     * Returns the scaling of the paper
     *
     * @access  public
     * @return  float
     */
    function getScaling() {
      return $this->getChildValue('scaling');
    }

    /**
     * Sets the 'scaling' of the Paper object
     *
     * @access  public
     * @param   float scaling
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="scaling"]/dia:real/@val', value= 'real')]
    function setScaling($scaling) {
      $this->setReal('scaling', $scaling);
    }

    /**
     * Returns TRUE if the paper is to be fittet onto a fixed numer of
     * horizontal and vertical sheets
     *
     * @access  public
     * @return  bool
     */
    function getFitTo() {
      return $this->getChildValue('fitto');
    }

    /**
     * If this is set to TRUE, two additional attributes names 'fitwidth' and
     * 'fitheight' tell on how many sheets to fit the diagram
     *
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="fitto"]/dia:boolean/@val', value= 'boolean')]
    function setFitTo($fitto) {
      $this->setBoolean('fitto', $fitto);
    }

    /**
     * Returns the number of sheets the diagram should be fit to horizontally
     *
     * @access  public
     * @return  int
     */
    function getFitWidth() {
      return $this->getChildValue('fitwidth');
    }

    /**
     * Sets the 'fitwidth' of this Paper object: specifies on how many sheets
     * (horizontal) the diagram should be fitted if 'fitto' is TRUE
     *
     * @access  public
     * @param   int width
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="fitwidth"]/dia:int/@val', value= 'int')]
    function setFitWidth($width) {
      $this->setInt('fitwidth', $width);
    }

    /**
     * Returns the number of sheets the diagram should be fit to vertically
     *
     * @access  public
     * @return  int
     */
    function getFitHeight() {
      return $this->getChildValue('fitheight');
    }

    /**
     * Sets the 'fitheight' of this Paper object: specifies on how many sheets
     * (vertical) the diagram should be fitted if 'fitto' is TRUE
     *
     * @access  public
     * @param   int height
     */
    #[@fromDia(xpath= 'dia:composite/dia:attribute[@name="fitheight"]/dia:int/@val', value= 'int')]
    function setFitHeight($height) {
      $this->setInt('fitheight', $height);
    }
            
  }
?>
