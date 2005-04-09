<?xml version="1.0" encoding="iso-8859-1"?>
<!--
 ! Master stylesheet
 !
 ! $Id$
 !-->
<xsl:stylesheet
 version="1.0"
 xmlns:exsl="http://exslt.org/common"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:func="http://exslt.org/functions"
 extension-element-prefixes="func"
>

  <xsl:include href="layout.xsl"/>
  
  <xsl:template name="context">
  </xsl:template>
  
  <xsl:template name="content">
    <h3>Anmeldung</h3>
    <p>
      Der angeforderte Bereich der Seite ist nur nach erfolgreicher Anmeldung zug�nglich.<br/>
      Bitte gib hier deinen Usernamen und Passwort ein, um dich anzumelden:
    </p>
    
    <form method="post" action="{$__state}">
    <input type="hidden" name="__handler" value="{/formresult/handlers/handler[@name= 'loginhandler']/@id}"/>
    
    <table width="400" cellpadding="0" cellspacing="5" class="login">
      <tr>
        <td align="right">Username:</td>
        <td><input type="text" name="username" value="{/formresult/formvalues/param[@name= 'username']}" size="20"/></td>
      </tr>
      <tr>
        <td align="right">Passwort:</td>
        <td><input type="password" name="password" value="{/formresult/formvalues/param[@name= 'password']}" size="20"/></td>
      </tr>
      <tr>
        <td colspan="2" align="right">
          <input type="submit" name="submit" value="Anmelden"/>
        </td>
      </tr>
    </table>
    </form>
  </xsl:template>
</xsl:stylesheet>
