<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  version="1.0"
>
  <xsl:output method="xhtml" encoding="iso-8859-1"/>
  <xsl:param name="mode" select="'apidoc-index'"/>
  <xsl:param name="package" select="''"/>
  <xsl:param name="collection" select="''"/>
  
  <xsl:include href="xsl-helper.xsl"/>
  
  <xsl:template name="navigation">
    Browse the class API documentation online.
    <br/><br/>

    <xsl:call-template name="nav-divider">
      <xsl:with-param name="caption">See also</xsl:with-param>
      <xsl:with-param name="colorcode">documentation</xsl:with-param>
    </xsl:call-template>
    <ul class="nav">
      <li><a href="inheritance.html">Inheritance Tree</a></li>
      <li><a href="/content/about.doc.html">Class documentation howto</a></li>
    </ul>
  </xsl:template>
  
  <xsl:template match="package">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <th valign="top" align="left">API Doc: <xsl:value-of select="./@name"/></th>
        <td valign="top" align="right">(<xsl:value-of select="count (collection/collection//class)"/> classes)</td>
	  </tr>
	  <tr bgcolor="#cccccc">
        <td colspan="2"><img src="/image/spacer.gif" height="1" border="0"/>
      </td></tr>
    </table>
    <br/>

    <!-- Begin Classlisting -->
    <xsl:variable name="mid" select="round(count(collection/collection) div 2)"/>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td width="50%" valign="top">
          <table border="0">
            <xsl:for-each select="collection/collection[position() &lt;= $mid]">
              <xsl:apply-templates select="."/>
            </xsl:for-each>
          </table>
        </td>
        <td width="50%" valign="top">
          <table border="0" cellspacing="0" cellpadding="0" width="100%">
            <xsl:for-each select="collection/collection[position() &gt; $mid]">
              <xsl:apply-templates select="."/>
            </xsl:for-each> 
          </table>
        </td>
      </tr>
    </table>
  </xsl:template>
  
  <xsl:template match="collection">
    <tr>
      <td width="1%" valign="top"><img src="/image/nav_overview.gif"/></td>
      <td width="50%" valign="top">
        <b><a href="collections/{./@prefix}.html"><xsl:value-of select="./@shortName"/></a></b> <img src="/image/caret-r.gif" border="0" height="7" width="11" alt="&gt;"/><br/>
        <xsl:for-each select="collection">
          <xsl:sort select="./@shortName"/>
          <a href="collections/{./@prefix}.html"><xsl:value-of select="./@shortName"/></a>
          <xsl:if test="position () &lt; count(../collection)">, </xsl:if>
        </xsl:for-each>
        <br/><br/>
      </td>
    </tr>
  </xsl:template>
  
  <xsl:template match="class">
    Class: <xsl:value-of select="./@className"/><br/>
  </xsl:template>
</xsl:stylesheet>
