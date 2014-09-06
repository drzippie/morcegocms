<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
	<html>
	<head>
	<title>Etiquetas MorcegoCMS</title>
	<link rel="stylesheet" type="text/css" href="tags.css" />
	</head>
	<body>
	
	<xsl:apply-templates select="tags"></xsl:apply-templates>
	</body>
	</html>
	
</xsl:template>
	<xsl:template match="tags">
		<dl><xsl:apply-templates select="tag"></xsl:apply-templates></dl>
	</xsl:template>
	<xsl:template match="tag">
		<dt><xsl:value-of select="family" />:<xsl:value-of select="function" /></dt>
		<dd>Devuelve: <xsl:value-of select="returns" /></dd>
		
		<xsl:apply-templates select="parameters"></xsl:apply-templates>
		
		<dd>ejemplo: <xsl:value-of select="example" /></dd>
		<dd><xsl:value-of select="help" /></dd>
	</xsl:template>
	<xsl:template match="parameters">
		<dd>Parametros</dd>
		<dd>
			<ol><xsl:apply-templates select="parameter"></xsl:apply-templates></ol>
		</dd>
	</xsl:template>
	<xsl:template match="parameter">
		<li><xsl:value-of select="name" /> (<em><xsl:value-of select="type" /></em>)</li>
	</xsl:template>
	
</xsl:stylesheet>