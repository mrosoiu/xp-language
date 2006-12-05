<?php
/*
 *
 * $Id:$
 */

  class TestMarshaller extends Object {

    /**
     *
     * @param   array classnames List of fully qualified class names
     * @param   int recurse default 0
     * @param   bool depend default FALSE
     * @return  &org.dia.DiaDiagram
     */
    function &marshal($classnames, $recurse= 0, $depend= FALSE) {
      // create new DiaDiagram
      $Dia= &new DiaDiagram();

      // check classnames?
      foreach ($classnames as $classname) {
        try (); {
          $Class= &XPClass::forName($Classname);
        } if (catch('Exception', $e)) {
          Console::writeLine("CLASS NOT FOUND: $classname!");
        }
      }

      return TestMarshaller::recurse($Dia, $classnames, $recurse, $depend);
    }

    /**
     *
     * @param   &org.dia.DiaDiagram Dia
     * @param   string[] classnames
     * @param   int recurse
     * @param   bool depend
     * @return  &org.dia.DiaDiagram
     */
    function &recurse(&$Dia, $classnames, $recurse, $depend) {
      $Layer= &$Dia->getLayer();


      return $Dia;
    }

  }
?>
