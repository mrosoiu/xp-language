<?xml version="1.0" encoding="iso-8859-1"?>
<!--
 ! Stylesheet for home page
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
  
  <!--
   ! Template for pager
   !
   ! @purpose  Links to previos 
   !-->
  <xsl:template name="pager">
    <center>
      <a title="Newer entries" class="pager{/formresult/pager/@offset &gt; 0}" id="previous">
        <xsl:if test="/formresult/pager/@offset &gt; 0">
          <xsl:attribute name="href"><xsl:value-of select="func:link(concat(
            'static?page', 
            /formresult/pager/@offset - 1
          ))"/></xsl:attribute>
        </xsl:if>
        <img alt="&#xab;" src="/image/prev.gif" border="0" width="19" height="15"/>
      </a>
      <a title="Older entries" class="pager{(/formresult/pager/@offset + 1) * /formresult/pager/@perpage &lt; /formresult/pager/@total}" id="next">
        <xsl:if test="(/formresult/pager/@offset + 1) * /formresult/pager/@perpage &lt; /formresult/pager/@total">
          <xsl:attribute name="href"><xsl:value-of select="func:link(concat(
            'static?page', 
            /formresult/pager/@offset + 1
          ))"/></xsl:attribute>
        </xsl:if>
        <img alt="&#xbb;" src="/image/next.gif" border="0" width="19" height="15"/>
      </a>
    </center>
  </xsl:template>
  
  <!--
   ! Template for content
   !
   ! @see      ../layout.xsl
   ! @purpose  Define main content
   !-->
  <xsl:template name="content">
    <h3>
      <a href="{func:link('static')}">Home</a>
      <xsl:if test="/formresult/pager/@offset &gt; 0">
        &#xbb;
        <a href="{func:link(concat('static?page', /formresult/pager/@offset))}">
          Page #<xsl:value-of select="/formresult/pager/@offset"/>
        </a>
      </xsl:if>
    </h3>
    <br clear="all"/>
    <xsl:call-template name="pager"/>
  
    <xsl:for-each select="/formresult/albums/album">
      <div class="datebox">
        <h2><xsl:value-of select="created/mday"/></h2> 
        <xsl:value-of select="substring(created/month, 1, 3)"/>&#160;
        <xsl:value-of select="created/year"/>
      </div>
      <h2>
        <a href="{func:link(concat('album/view?', @name))}">
          <xsl:value-of select="@title"/>
        </a>
      </h2>
      <p align="justify">
        <xsl:copy-of select="description"/>
        <br clear="all"/>
      </p>
      
      <h4>Highlights</h4>
      <table class="highlights" border="0">
        <tr>
          <xsl:for-each select="highlights/highlight">
            <td>
              <a href="{func:link(concat('image/view?', ../../@name, ',h,0,', position()- 1))}">
                <img width="150" height="113" border="0" src="/albums/{../../@name}/thumb.{name}"/>
              </a>
            </td>
          </xsl:for-each>
        </tr>
      </table>
      <p><a href="{func:link(concat('album/view?', @name))}">See more</a></p>
      <br/><br clear="all"/>
    </xsl:for-each>
    
    <br clear="all"/>
    <xsl:call-template name="pager"/>
  </xsl:template>
  
</xsl:stylesheet>
