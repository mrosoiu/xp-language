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
  <xsl:output method="html" encoding="iso-8859-1" indent="no"/>
  <xsl:param name="__page"/>
  <xsl:param name="__frame"/>
  <xsl:param name="__state"/>
  <xsl:param name="__lang"/>
  <xsl:param name="__product"/>
  <xsl:param name="__sess"/>
  <xsl:param name="__query"/>

  <!--
   ! Function to display a serialized date object (date only)
   !
   ! @param  node-set date
   !-->
  <func:function name="func:date">
    <xsl:param name="date"/>
    
    <func:result>
      <xsl:choose>
        <xsl:when test="not(exsl:node-set($date)/_utime)">
          <!-- Intentionally empty -->
        </xsl:when>
        <xsl:when test="$__lang = 'de_DE' or $__lang = 'en_UK'">
          <xsl:value-of select="concat(
            format-number(exsl:node-set($date)/mday, '00'), '.',
            format-number(exsl:node-set($date)/mon, '00'), '.',
            exsl:node-set($date)/year
          )"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat(
            exsl:node-set($date)/year, '-',
            format-number(exsl:node-set($date)/mon, '00'), '-',
            format-number(exsl:node-set($date)/mday, '00')
          )"/>
        </xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>

  <!--
   ! Function to display a human readable date.
   !
   ! Dates within the last two days will be written as "Today",
   ! "Yesterday, <time>" or "The day before yesterday".
   !
   ! Dates not within that range but in the last week will be written
   ! using the day-name and time.
   !
   ! All other days are written with date and time.
   !
   ! @param   node-set date
   ! @return  string
   !-->
  <func:function name="func:smartdate">
    <xsl:param name="date"/>
    
    <xsl:variable name="diff" select="(ceiling(/formresult/@serial div 86400) - ceiling(exsl:node-set($date)/_utime div 86400))"/>
    
    <func:result>
      <!-- DEBUG (<xsl:value-of select="$diff"/>) -->
      <xsl:choose>
        <xsl:when test="$diff = 0">Today</xsl:when>
        <xsl:when test="$diff = 1">Yesterday</xsl:when>
        <xsl:when test="$diff &lt;= 7"><xsl:value-of select="$date/weekday"/></xsl:when>
        <xsl:when test="$diff &lt;= 14">Last <xsl:value-of select="$date/weekday"/></xsl:when>
        <xsl:otherwise><xsl:value-of select="func:date(exsl:node-set($date))"/></xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>

  <!--
   ! Function to display a serialized date object (time only)
   !
   ! @param  node-set date
   !-->
  <func:function name="func:time">
    <xsl:param name="date"/>
    
    <func:result>
      <xsl:choose>
        <xsl:when test="not(exsl:node-set($date)/_utime)">
          <!-- Intentionally empty -->
        </xsl:when>
        <xsl:when test="$__lang = 'de_DE' or $__lang = 'en_UK'">
          <xsl:value-of select="concat(
            format-number(exsl:node-set($date)/hours, '00'), ':',
            format-number(exsl:node-set($date)/minutes, '00')
          )"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:choose>
            <xsl:when test="exsl:node-set($date)/hours = 0">
              <xsl:value-of select="concat(
                '12:',
                format-number(exsl:node-set($date)/minutes, '00'),
                ' AM'
              )"/>
            </xsl:when>
            <xsl:when test="exsl:node-set($date)/hours &lt; 13">
              <xsl:value-of select="concat(
                format-number(exsl:node-set($date)/hours, '00'), ':',
                format-number(exsl:node-set($date)/minutes, '00'), 
                ' AM'
              )"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="concat(
                format-number(exsl:node-set($date)/hours - 12, '00'), ':',
                format-number(exsl:node-set($date)/minutes, '00'),
                ' PM'
              )"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>

  <!--
   ! Function to display a serialized date object (date AND time)
   !
   ! @param  node-set date
   !-->
  <func:function name="func:datetime">
    <xsl:param name="date"/>
    
    <func:result>
      <xsl:if test="exsl:node-set($date)/_utime">
        <xsl:value-of select="concat(func:date($date), ' ', func:time($date))"/>
      </xsl:if>
    </func:result>
  </func:function>

  <!--
   ! Function to display a human readable date.
   !
   ! Dates within the last two days will be written as "Today, <time>",
   ! "Yesterday, <time>" or "The day before yesterday, <time>".
   !
   ! Dates not within that range but in the last week will be written
   ! using the day-name and time.
   !
   ! All other days are written with date and time.
   !
   ! @param   node-set date
   ! @return  string
   !-->
  <func:function name="func:smartdatetime">
    <xsl:param name="date"/>
    
    <func:result><xsl:value-of select="concat(func:smartdate($date), ', ', func:time($date))"/></func:result>
  </func:function>

  <!--
   ! Function that trims characters off the beginning of a string
   !
   ! @param  string text
   ! @param  string chars
   !-->  
  <func:function name="func:ltrim">
    <xsl:param name="text"/>
    <xsl:param name="chars"/>
    
    <func:result>
      <xsl:choose>
        <xsl:when test="contains(substring($text, 1, 1), $chars)">
          <xsl:value-of select="func:ltrim(substring($text, 2, string-length($text)), $chars)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$text"/>
        </xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>

  <!--
   ! Function to display a link to the type's api doc
   !
   ! @param  string type
   !-->
  <func:function name="func:typehref">
    <xsl:param name="type"/>

    <xsl:variable name="name" select="func:ltrim(substring-before(concat($type, '['), '['), '&amp;')"/>
    <func:result>
      <xsl:choose>
        <xsl:when test="contains($name, '.')">
          <xsl:value-of select="concat('/xml/', $__product, '.', $__lang, '/lookup?', $name)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="concat('http://php3.de/types.', $name)"/>
        </xsl:otherwise>
      </xsl:choose>
    </func:result>
  </func:function>

  <!--
   ! Function that concatenates a text conditionally
   !
   ! @param  bool condition
   ! @param  string text
   !-->  
  <func:function name="func:concatif">
    <xsl:param name="condition"/>
    <xsl:param name="text"/>
    
    <func:result>
      <xsl:if test="$condition">
        <xsl:value-of select="$text"/>
      </xsl:if>
    </func:result>
  </func:function>

  <!--
   ! Function that returns a fully qualified link to a specified target
   !
   ! @param  string target
   !-->
  <func:function name="func:link">
    <xsl:param name="target"/>

    <func:result>
      <xsl:value-of select="concat(
        '/xml/', 
        $__product, 
        '.', 
        $__lang,
        func:concatif($__sess != '', concat('.psessionid=', $__sess)),
        '/',
        $target
      )"/>
    </func:result>
  </func:function>
  
  <!--
   ! Template for links with scheme "xp"
   !
   !-->
  <xsl:template match="reference/link[child::*[name() = 'scheme']/text() = 'xp']">
    <a href="/xml/{$__product}.{$__lang}/lookup?{host}"><xsl:value-of select="host"/></a>
  </xsl:template>

  <!--
   ! Template for links with scheme "rfc"
   !
   !-->
  <xsl:template match="reference/link[child::*[name() = 'scheme']/text() = 'rfc']">
    <a href="http://www.faqs.org/rfcs/rfc{host}.html#{fragment}" target="_blank">
      RFC <xsl:value-of select="host"/>
      <xsl:if test="string-length(fragment) != 0">
        Section <xsl:value-of select="fragment"/>
      </xsl:if>
    </a>
  </xsl:template>

  <!--
   ! Template for links with scheme "mailto"
   !
   !-->
  <xsl:template match="reference/link[child::*[name() = 'scheme']/text() = 'mailto']">
    <a href="mailto:{path}" target="_blank"><xsl:value-of select="path"/></a>
  </xsl:template>  

  <!--
   ! Template for links with scheme "php"
   !
   !-->  
  <xsl:template match="reference/link[child::*[name() = 'scheme']/text() = 'php']">
    <a href="http://php3.de/{host}" target="_blank">PHP Manual: <xsl:value-of select="host"/></a>
  </xsl:template>

  <!--
   ! Template for links with scheme "http"
   !
   !-->
  <xsl:template match="reference/link[child::*[name() = 'scheme']/text() = 'http']">
    <xsl:variable name="href" select="concat(
      scheme, 
      '://', 
      host, 
      path, 
      func:concatif(query != '', concat('?', query)), 
      func:concatif(fragment != '', concat('#', fragment))
    )"/>
    <a href="{$href}" target="_blank">
      <xsl:value-of select="$href"/>
    </a>
  </xsl:template>
  
  <!--
   ! Template that matches on everything and copies it through
   ! one to one.
   !
   !-->
  <xsl:template match="*">
    <xsl:copy>
      <xsl:copy-of select="@*"/>
      <xsl:apply-templates/>
    </xsl:copy> 
  </xsl:template>
  
</xsl:stylesheet>
