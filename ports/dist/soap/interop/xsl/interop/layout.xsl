<?xml version="1.0" encoding="iso-8859-1"?>
<!--
 ! Layout stylesheet
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
  <xsl:include href="master.xsl"/>
  
  <xsl:variable name="navigation">
    <nav target="static">Home</nav>
    <nav target="overview">Overview</nav>
  </xsl:variable>
  
  <xsl:variable name="area" select="substring-before(concat($__state, '/'), '/')"/>

  <!--
   ! Template that matches on the root node
   !
   ! @purpose  Define the site layout
   !-->
  <xsl:template match="/">
    <html>
      <head>
        <title>XP Framework Interop Suite | <xsl:value-of select="$__state"/> | <xsl:value-of select="$__page"/></title>
        <link rel="stylesheet" href="/interop.css"/>
      </head>
      <body>
        <form name="search" method="GET" action="/xml/{$__product}.{$__lang}/lookup">
        
          <!-- top navigation -->
          <table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td colspan="6"><img src="/image/logo.png" width="202" height="60"/></td>
            </tr>
            <tr>
              <xsl:for-each select="exsl:node-set($navigation)/nav">
                <xsl:variable name="class">nav<xsl:if test="@target = $area">active</xsl:if></xsl:variable>
                <td width="5%" class="{$class}">
                  <a class="{$class}" href="/xml/{$__product}.{$__lang}/{@target}">
                    <xsl:value-of select="."/>
                  </a>
                </td>
              </xsl:for-each>
              <td class="nav">&#160;</td>
              <td width="5%" class="nav" align="right">
                <input class="search" type="text" name="q" size="24"/>
              </td>
              <td width="1%" class="nav" align="right">
                <input type="image" src="/image/submit_search.gif" border="0" width="11" height="11" alt="search"/>
              </td>
            </tr>
          </table>
        </form>

        <!-- main content -->
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top">
              <xsl:call-template name="content"/>
            </td>
            <td width="2%">&#160;</td>
            <td width="15%" valign="top" nowrap="nowrap">
              <xsl:call-template name="context"/>
            </td>
          </tr>
        </table>
        <br/>

        <!-- footer -->
        <br/>
        <table width="100%" border="0" cellspacing="0" cellpadding="2" class="footer">
          <tr>
            <td><small>(c) 2001-2004 the XP team</small></td>
            <td align="right"><small>
              <a href="http://xp-framework.net/credits.html">credits</a> |
              <a href="#feedback">feedback</a>
            </small></td>
          </tr>
        </table>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
