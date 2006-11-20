<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
 
  uses(
    'unittest.TestCase',
    'net.xp_framework.unittest.xml.DialogType',
    'xml.meta.Marshaller'
  );

  /**
   * Test Marshaller API
   *
   * @see      xp://xml.meta.Marshaller
   * @purpose  Unit Test
   */
  class MarshallerTest extends TestCase {

    /**
     * Compares XML after stripping all whitespace between tags of both 
     * expected and actual strings.
     *
     * @see     xp://unittest.TestCase#assertEquals
     * @access  public
     * @param   string expect
     * @param   string actual
     * @return  bool
     */
    function assertXmlEquals($expect, $actual) {
      return $this->assertEquals(
        preg_replace('#>[\s\r\n]+<#', '><', trim($expect)),
        preg_replace('#>[\s\r\n]+<#', '><', trim($actual))
      );
    }

    /**
     * Tests the dialog's id member gets serialized as an id attribute
     *
     * @access  public
     */
    #[@test]
    function idAttribute() {
      $dialog= &new DialogType();
      $dialog->setId('file.open');
      
      $this->assertXmlEquals('
        <dialogtype id="file.open">
          <caption/>
        </dialogtype>', 
        Marshaller::marshal($dialog)
      );
    }
    
    /**
     * Tests the dialog's caption member gets serialized as a node
     *
     * @access  public
     */
    #[@test]
    function captionNode() {
      $dialog= &new DialogType();
      $dialog->setCaption('Open a file > Choose');
      
      $this->assertXmlEquals('
        <dialogtype id="">
          <caption>Open a file &gt; Choose</caption>
        </dialogtype>', 
        Marshaller::marshal($dialog)
      );
    }

    /**
     * Tests the dialog's buttons member gets serialized as a nodeset
     *
     * @access  public
     */
    #[@test]
    function buttonsNodeSet() {
      $dialog= &new DialogType();
      $dialog->setCaption('Really delete the file "�"?');

      with ($ok= &$dialog->addButton(new ButtonType())); {
        $ok->setId('ok');
        $ok->setCaption('Yes, go ahead');
      }
      with ($cancel= &$dialog->addButton(new ButtonType())); {
        $cancel->setId('cancel');
        $cancel->setCaption('No, please don\'t!');
      }

      $this->assertXmlEquals('
        <dialogtype id="">
          <caption>Really delete the file &quot;�&quot;?</caption>
          <button id="ok">Yes, go ahead</button>
          <button id="cancel">No, please don\'t!</button>
        </dialogtype>', 
        Marshaller::marshal($dialog)
      );
    }
    
    /**
     * Tests for a new dialog without any members set
     *
     * @access  public
     */
    #[@test]
    function emptyMembers() {
      $dialog= &new DialogType();
      $this->assertXmlEquals('
        <dialogtype id="">
          <caption/>
        </dialogtype>', 
        Marshaller::marshal($dialog)
      );
    }
  }
?>
