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
 xmlns:php="http://php.net/xsl"
 extension-element-prefixes="func"
 exclude-result-prefixes="exsl func php"
>
  <xsl:include href="../master.xsl"/>
  <xsl:include href="links.inc.xsl"/>
  
  <!--
   ! Template that matches on the root node
   !
   ! @purpose  Define the site layout
   !-->
  <xsl:template match="/">
    <html>
      <head>
        <title>
          <xsl:call-template name="page-title"/>
        </title>
        <link rel="stylesheet" href="/{/formresult/config/style}.css"/>
        <link rel="alternate" type="application/rss+xml" title="RSS - {/formresult/config/title}" href="/rss/"/>
        <script language="JavaScript"><![CDATA[
          function handleKey(event) {
            if (event.ctrlKey || event.altKey || event.shiftKey) return false;

            switch (event.keyCode) {
              case 37:  // Left arrow
                if (
                  (element= document.getElementById('previous')) &&
                  (element.href)
                ) {
                  document.location.href= element.href;
                  return false;
                }
                break;
              
              case 39:  // Right arrow
                if (
                  (element= document.getElementById('next')) &&
                  (element.href)
                ) {
                  document.location.href= element.href;
                  return false;
                }
                break;
              
            }
            
            return true;
          }
        ]]></script>
      </head>
      <body onKeyUp="handleKey(event)">
        <center>
          <!-- main content -->
          <table border="0" cellspacing="0" class="main">
            <tr id="header">
              <xsl:call-template name="gutter"/>
              <td width="690" class="gutter" id="menu">
                <a id="active" href="{func:linkPage(0)}">
                  Home
                </a><a href="{func:link('bydate')}">
                  By Date
                </a><a href="{func:link('bytopic')}">
                  By Topic
                </a>
              </td>
            </tr>
            <tr>
              <td colspan="4">
                <div class="content">
                  <xsl:call-template name="content"/>
                </div>
              </td>
            </tr>
          </table>
          
          <!-- footer -->
          <table border="0" cellspacing="0" cellpadding="2" class="footer">
            <tr>
              <td><small>&#169; <xsl:value-of select="/formresult/config/copyright"/></small></td>
              <td align="right">
                <xsl:for-each select="/formresult/links/link">
                  <a href="{@href}">
                    <img border="0" src="/image/{@id}.png" hspace="1" width="80" height="15" alt="{@id}"/>
                  </a>
                </xsl:for-each>
              </td>
            </tr>
          </table>
        </center>
      </body>
    </html>
  </xsl:template>
  
  <xsl:template name="gutter">
    <xsl:param name="current" select="0"/>
    <xsl:param name="max" select="/formresult/config/gutters"/>
    
    <xsl:if test="$current &lt; $max">
      <td width="60" class="gutter" id="gutter{$current+ 1}">&#160;</td>
      <xsl:call-template name="gutter">
        <xsl:with-param name="current" select="$current+ 1"/>
        <xsl:with-param name="max" select="$max"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

  <xsl:template name="page-title">
    <xsl:value-of select="$__state"/> - 
    <xsl:value-of select="/formresult/config/title"/>
  </xsl:template>
</xsl:stylesheet>
