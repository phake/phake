<?xml version='1.0'?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0' xmlns:exslt="http://exslt.org/common">
	<xsl:import href="docbook-xsl/xhtml/chunk.xsl"/>
  <xsl:import href="docbook-xsl/xhtml/highlight.xsl"/>

	<xsl:param name="highlight.source" select="1"/>
  <xsl:param name="highlight.default.language">php</xsl:param>
  <xsl:param name="use.id.as.filename" select="1" />
	<xsl:param name="html.stylesheet">style.css</xsl:param>
	<xsl:param name="navig.showtitles">0</xsl:param>
	<xsl:param name="header.rule" select="0"></xsl:param>
	<xsl:param name="footer.rule" select="0"></xsl:param>


<xsl:template name="chunk-element-content">
  <xsl:param name="prev"/>
  <xsl:param name="next"/>
  <xsl:param name="nav.context"/>
  <xsl:param name="content">
    <xsl:apply-imports/>
  </xsl:param>

  <xsl:call-template name="user.preroot"/>

  <html>
    <xsl:call-template name="html.head">
      <xsl:with-param name="prev" select="$prev"/>
      <xsl:with-param name="next" select="$next"/>
    </xsl:call-template>

		<body>
			<div id="header">
				<h1><xsl:value-of select="/book/title" /></h1>
			</div>
			<div class="colmask leftmenu">
				<div class="colleft">
					<div id="col1">
						<div class="content">
							<xsl:call-template name="body.attributes"/>
							<xsl:call-template name="user.header.navigation"/>

							<xsl:call-template name="header.navigation">
								<xsl:with-param name="prev" select="$prev"/>
								<xsl:with-param name="next" select="$next"/>
								<xsl:with-param name="nav.context" select="$nav.context"/>
							</xsl:call-template>

							<xsl:call-template name="user.header.content"/>

							<xsl:copy-of select="$content"/>

							<xsl:call-template name="user.footer.content"/>

							<xsl:call-template name="header.navigation">
								<xsl:with-param name="prev" select="$prev"/>
								<xsl:with-param name="next" select="$next"/>
								<xsl:with-param name="nav.context" select="$nav.context"/>
							</xsl:call-template>

							<xsl:call-template name="user.footer.navigation"/>
						</div>
					</div>
					<div id="col2">
						<div class="content">
							<dl>
								<xsl:apply-templates select="/book/preface|/book/chapter" mode="toc">
								</xsl:apply-templates>
							</dl>
						</div>
					</div>
				</div>
			</div>
			<div id="footer">
				<xsl:apply-templates select="//copyright[1]" mode="titlepage.mode"/>
				<p>This page uses the <a href="http://matthewjamestaylor.com/blog/perfect-2-column-left-menu.htm">Perfect 'Left Menu' 2 Column Liquid Layout</a> by <a href="http://matthewjamestaylor.com">Matthew James Taylor</a>.</p>
			</div>
		</body>
  </html>
  <xsl:value-of select="$chunk.append"/>
</xsl:template>

</xsl:stylesheet>
