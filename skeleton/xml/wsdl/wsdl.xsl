<?xml version="1.0" ?>
<!--
 ! Stylesheet that generates an XP class from a WSDL
 !
 ! $Id$
 !-->
<xsl:stylesheet
 version="1.0"
 xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
 xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
 xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
 xmlns:http="http://schemas.xmlsoap.org/wsdl/http/"
 xmlns:mime="http://schemas.xmlsoap.org/wsdl/mime/"
 xmlns:xpdoc="http://xp-framework.net/TR/apidoc/"
>
  <xsl:output method="text" indent="no"/>
  <xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyz</xsl:variable>
  <xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>

  <!--
   ! Type mapping for what the SOAP API maps automatically
   !
   ! @see   xp://xml.soap.SOAPClient
   !-->
  <xsl:variable name="typemap">
    <mapping for="xsd:string">string</mapping>
    <mapping for="xsd:long">int</mapping>
    <mapping for="xsd:int">int</mapping>
    <mapping for="xsd:float">float</mapping>
    <mapping for="xsd:double">float</mapping>
    <mapping for="xsd:boolean">bool</mapping>
    <mapping for="soapenc:Array">array</mapping>
    <mapping for="xsd:base64Binary">xml.soap.types.SOAPBase64Binary</mapping>
    <mapping for="apachesoap:Map">xml.soap.types.SOAPHashmap</mapping>
  </xsl:variable>

  <!--
   ! Template for class name.
   !
   ! @type   named
   ! @param  string string
   !-->
  <xsl:template name="lcfirst">
    <xsl:param name="string"/>
  
    <xsl:value-of select="concat(
      translate(substring($string, 1, 1), $ucletters, $lcletters),
      substring($string, 2)
    )"/>
  </xsl:template>  

  <!--
   ! Template for class name.
   !
   ! @type   named
   ! @param  string name
   !-->
  <xsl:template name="class">
    <xsl:param name="name"/>
    
    <xsl:choose>
      <xsl:when test="contains($name, 'Service')">
        <xsl:value-of select="substring-before($name, 'Service')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$name"/>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:text>Client</xsl:text>
  </xsl:template>

  <!--
   ! Template for creating API doc comments
   !
   ! @type   named
   ! @param  string name
   ! @param  string indent default '  '
   !-->
  <xsl:template name="xpdoc:comment">
    <xsl:param name="string"/>
    <xsl:param name="indent" select="'  '"/>
 
    <xsl:value-of select="concat($indent, ' * ')"/>
   
    <xsl:choose>
      <xsl:when test="normalize-space($string) = ''">
        <xsl:text>(Insert documentation here)&#10;</xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="remaining" select="substring-after($string, '&#xA;')"/>
        <xsl:value-of select="concat(
          normalize-space(substring($string, 1, string-length($string) - string-length($remaining))),
          '&#10;'
        )"/>
        <xsl:if test="$remaining != ''">  
          <xsl:call-template name="xpdoc:comment">
            <xsl:with-param name="string" select="$remaining"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template for return value documentation
   !
   ! @type   named
   ! @param  string for
   ! @param  string indent default '    '
   !-->
  <xsl:template name="xpdoc:return">
    <xsl:param name="for"/>
    <xsl:param name="indent" select="'    '"/>

    <xsl:value-of select="concat(
      $indent,
      ' * @return  '
    )"/>
    <xsl:choose>
      <xsl:when test="contains($for, ':')">
        <xsl:call-template name="parttype">
          <xsl:with-param name="node" select="/wsdl:definitions/wsdl:message[@name = substring-after($for, ':')]/wsdl:part[1]"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="parttype">
          <xsl:with-param name="node" select="/wsdl:definitions/wsdl:message[@name = $for]/wsdl:part[1]"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template for input arguments documentation
   !
   ! @type   named
   ! @param  string for
   ! @param  string indent default '    '
   !-->
  <xsl:template name="xpdoc:arguments">
    <xsl:param name="for"/>
    <xsl:param name="indent" select="'    '"/>
    
    <xsl:choose>
      <xsl:when test="contains($for, ':')">
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = substring-after($for, ':')]/wsdl:part">
          <xsl:value-of select="concat(
            $indent,
            ' * @param   '
          )"/>
          <xsl:call-template name="parttype">
            <xsl:with-param name="node" select="."/>
          </xsl:call-template>
          <xsl:text> </xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>&#10;</xsl:text>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = $for]/wsdl:part">
          <xsl:value-of select="concat(
            $indent,
            ' * @param   '
          )"/>
          <xsl:call-template name="parttype">
            <xsl:with-param name="node" select="."/>
          </xsl:call-template>
          <xsl:text> </xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>&#10;</xsl:text>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template for input value messages
   !
   ! @type   named
   ! @param  string for
   !-->
  <xsl:template name="arguments">
    <xsl:param name="for"/>
    
    <xsl:choose>
      <xsl:when test="contains($for, ':')">
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = substring-after($for, ':')]/wsdl:part">
          <xsl:text>$</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:if test="position() &lt; last()"><xsl:text>, </xsl:text></xsl:if>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = $for]/wsdl:part">
          <xsl:text>$</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:if test="position() &lt; last()"><xsl:text>, </xsl:text></xsl:if>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template for input value messages
   !
   ! @type   named
   ! @param  string for
   ! @param  string indent default '        '
   !-->
  <xsl:template name="argumentnames">
    <xsl:param name="for"/>
    <xsl:param name="indent" select="'        '"/>
    
    <xsl:choose>
      <xsl:when test="contains($for, ':')">
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = substring-after($for, ':')]/wsdl:part">
          <xsl:value-of select="$indent"/>
          <xsl:text>new SOAPNamedItem('</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>', $</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>)</xsl:text>
          <xsl:if test="position() &lt; last()"><xsl:text>,&#10;</xsl:text></xsl:if>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="/wsdl:definitions/wsdl:message[@name = $for]/wsdl:part">
          <xsl:value-of select="$indent"/>
          <xsl:text>new SOAPNamedItem('</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>', $</xsl:text>
          <xsl:value-of select="@name"/>
          <xsl:text>)</xsl:text>          
          <xsl:if test="position() &lt; last()"><xsl:text>,&#10;</xsl:text></xsl:if>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template for a part's type name
   !
   ! @type   named
   ! @param  node-set node default node(NULL)
   !-->
  <xsl:template name="parttype"> 
    <xsl:param name="node" select="/.."/>

    <xsl:choose>
      <xsl:when test="$typemap/mapping[@for = $node/@type]">
        <xsl:value-of select="$typemap/mapping[@for = $node/@type]"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>mixed (</xsl:text>
        <xsl:value-of select="$node/@type"/>
        <xsl:text>)</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!--
   ! Template to match on root node
   !
   ! @type   match
   !-->
  <xsl:template match="/">
    <xsl:apply-templates select="wsdl:definitions"/>
  </xsl:template>

  <!--
   ! Template to match on port types
   !
   ! @type   match
   !-->
  <xsl:template match="wsdl:portType">
    <xsl:for-each select="wsdl:operation">      
      <xsl:text><![CDATA[
    /**
     * Invokes the method "]]></xsl:text><xsl:value-of select="@name"/><xsl:text><![CDATA["
     *
     * @access  public
]]></xsl:text>
      <xsl:call-template name="xpdoc:arguments">
        <xsl:with-param name="for" select="wsdl:input/@message"/>
      </xsl:call-template>
      <xsl:call-template name="xpdoc:return">
        <xsl:with-param name="for" select="wsdl:output/@message"/>
      </xsl:call-template>
      <xsl:text><![CDATA[
     * @throws  xml.soap.SOAPFaultException in case a fault occurs
     */
    function ]]></xsl:text>
      <xsl:call-template name="lcfirst">
        <xsl:with-param name="string" select="@name"/>
      </xsl:call-template>
      <xsl:text>(</xsl:text>
      <xsl:call-template name="arguments">
        <xsl:with-param name="for" select="wsdl:input/@message"/>
      </xsl:call-template>
      <xsl:text><![CDATA[) {
      return $this->invoke(
        ']]></xsl:text>
      <xsl:value-of select="@name"/>
      <xsl:text>',
</xsl:text>
      <xsl:call-template name="argumentnames">
        <xsl:with-param name="for" select="wsdl:input/@message"/>
      </xsl:call-template>
      <xsl:text>
      );
    }
</xsl:text>
    </xsl:for-each>
  </xsl:template>
  
  <!--
   ! Template for WSDL definitions
   !
   ! @type   match
   !-->
  <xsl:template match="wsdl:definitions">
    <xsl:text><![CDATA[<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
  uses(
    'xml.soap.SOAPClient', 
    'xml.soap.transport.SOAPHTTPTransport'
  );
  
  /**
]]></xsl:text>
    <xsl:call-template name="xpdoc:comment">
      <xsl:with-param name="string" select="/comment()"/>
    </xsl:call-template>
    <xsl:text><![CDATA[   *
   * @purpose  SOAP service wrapper class
   */  
  class ]]></xsl:text>
    <xsl:call-template name="class">
      <xsl:with-param name="name" select="wsdl:service/@name"/>
    </xsl:call-template>
    <xsl:text><![CDATA[ extends SOAPClient {
    
    /**
     * Constructor
     *
     * @access  public
     * @param   string endpoint
     */
    function __construct($endpoint= ']]></xsl:text>
    <xsl:value-of select="wsdl:service/wsdl:port/soap:address/@location"/>
    <xsl:text><![CDATA[') {
      parent::__construct(
        new SOAPHTTPTransport($endpoint),
        ']]></xsl:text><xsl:value-of select="@targetNamespace"/><xsl:text><![CDATA['
      );
    }
]]></xsl:text>
    <xsl:apply-templates select="wsdl:portType"/>
    <xsl:text><![CDATA[  }
?>
]]></xsl:text>
  </xsl:template>
</xsl:stylesheet>
