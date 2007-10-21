<?xml version="1.0" encoding="iso-8859-1"?>
<!--
 ! Stylesheet for shots/view
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
  <xsl:import href="../layout.xsl"/>
  
  <!--
   ! Template for page title
   !
   ! @see       ../layout.xsl
   !-->
  <xsl:template name="page-title">
    <xsl:value-of select="concat(
      'Shot ', /formresult/selected/name,
      ' (', /formresult/selected/@mode, ') @ ',
      /formresult/config/title
    )"/>
  </xsl:template>
  
  <!--
   ! Template for content
   !
   ! @see      ../../layout.xsl
   ! @purpose  Define main content
   !-->
  <xsl:template name="content">
    <h3>
      <a href="{func:linkPage(0)}">Home</a> &#xbb; 

      <xsl:if test="/formresult/selected/@page &gt; 0">
        <a href="{func:linkPage(/formresult/selected/@page)}">
          Page #<xsl:value-of select="/formresult/selected/@page"/>
        </a>
        &#xbb;
      </xsl:if>

      Featured image: <xsl:value-of select="/formresult/selected/name"/>
    </h3>

    <br clear="all"/> 
    <center>
      <a title="Color version" class="pager{/formresult/selected/@mode = 'gray'}" id="previous">
        <xsl:if test="/formresult/selected/@mode = 'gray'">
          <xsl:attribute name="href"><xsl:value-of select="func:linkShot(
            /formresult/selected/name, 
            0
          )"/></xsl:attribute>
        </xsl:if>
        <img alt="&#xab;" src="/image/prev.gif" border="0" width="19" height="15"/>
      </a>
      <a title="Black and white version" class="pager{/formresult/selected/@mode = 'color'}" id="next">
        <xsl:if test="/formresult/selected/@mode = 'color'">
          <xsl:attribute name="href"><xsl:value-of select="func:linkShot(
            /formresult/selected/name, 
            1
          )"/></xsl:attribute>
        </xsl:if>
        <img alt="&#xbb;" src="/image/next.gif" border="0" width="19" height="15"/>
      </a>
    </center>
    
    <!-- Selected image -->
    <table width="800" border="0">
      <tr>
        <td id="image" align="center">
          <img border="0" src="/shots/{/formresult/selected/@mode}.{/formresult/selected/fileName}"/>
        </td>
      </tr>
    </table>
    
    <p>
      Originally taken on <xsl:value-of select="php:function('XSLCallback::invoke', 'xp.date', 'format', string(/formresult/selected/image/exifData/dateTime), 'D, d M H:i')"/>
      with <xsl:value-of select="/formresult/selected/image/exifData/make"/>'s
      <xsl:value-of select="/formresult/selected/image/exifData/model"/>.

      (<small>
      <xsl:if test="/formresult/selected/image/exifData/apertureFNumber != ''">
        <xsl:value-of select="/formresult/selected/image/exifData/apertureFNumber"/>
      </xsl:if>
      <xsl:if test="/formresult/selected/image/exifData/exposureTime != ''">
        <xsl:text>, </xsl:text>
        <xsl:value-of select="/formresult/selected/image/exifData/exposureTime"/> sec.
      </xsl:if>  
      <xsl:if test="/formresult/selected/image/exifData/isoSpeedRatings != ''">
        <xsl:text>, ISO </xsl:text>
        <xsl:value-of select="/formresult/selected/image/exifData/isoSpeedRatings"/>
      </xsl:if>  
      <xsl:if test="/formresult/selected/image/exifData/focalLength != '0'">
        <xsl:text>, focal length: </xsl:text>
        <xsl:value-of select="/formresult/selected/image/exifData/focalLength"/>
        <xsl:text> mm</xsl:text>
      </xsl:if>
      <xsl:if test="(/formresult/selected/image/exifData/flash mod 8) = 1">
        <xsl:text>, flash fired</xsl:text>
      </xsl:if>
      </small>)
    </p>
    
  </xsl:template>
  
</xsl:stylesheet>
