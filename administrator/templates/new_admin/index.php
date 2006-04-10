<jdoc:comment>
@copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
Joomla! is free software. This version may have been modified pursuant
to the GNU General Public License, and as distributed it includes or
is derivative of works licensed under the GNU General Public License or
other free or open source software licenses.
See COPYRIGHT.php for copyright notices and details.
</jdoc:comment>

<?php
$lang =& $mainframe->getLanguage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANG_TAG}" lang="{LANG_TAG}" dir="{LANG_DIR}" >
	<head>
	<jdoc:include type="head" />
	<link href="templates/{TEMPLATE}/css/template.css" rel="stylesheet" type="text/css" />
	
	<jdoc:tmpl name="isRTL" varscope="index.php" type="condition" conditionvar="LANG_ISRTL">
		<jdoc:sub condition="1">
			<link href="templates/{TEMPLATE}/css/template_rtl.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
	</jdoc:tmpl>
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty.css">
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty_print.css" media="print">
	<script type="text/javascript" src="templates/{TEMPLATE}/js/menu.js"></script>
	<script type="text/javascript" src="templates/{TEMPLATE}/js/nifty.js"></script>
	<script type="text/javascript" src="templates/{TEMPLATE}/js/template.js"></script>

	</head>
	<body>
		<div id="header1">
			<div id="header2">
				<div id="header3">
					<div id="version"><jdoc:translate>Version#</jdoc:translate></div>
					<span><jdoc:translate>Administration</jdoc:translate></span>
				</div>
			</div>
		</div>
		<div id="top-box">
			<div id="status-box">
				<jdoc:include type="modules" name="status" style="3" />
			</div>
			<div id="menu-box">
				<jdoc:include type="module" name="cssmenu" />
			</div>
			<div class="clr"></div>
		</div>
		<div id="content-box">
			<div id="content-box2">
				<div id="content-pad">
					<div class="content-area-full">
						<div class="content-pad">
							<div class="toolbar-box">
								<div class="toolbar-pad">
										<jdoc:include type="modules" name="toolbar" />
										<jdoc:include type="modules" name="title" />
										<div class="clr"></div>
								</div>
								<div class="clr"></div>
							</div>
							<div class="spacer"></div>
							<div class="element-box">
								<div class="element-pad">
									<jdoc:include type="component" />
								</div>
							</div>
							<noscript>
								<jdoc:translate key="WARNJAVASCRIPT" />
							</noscript>
					
							<div class="clr"></div>
						</div>
					</div>
				</div>
				<div class="clr"></div>
			</div>
		</div>	
		<div id="footer1">
			<div id="footer2">
				<div id="footer3"></div>
			</div>
		</div>	
		
		<div id="copyright"><a href="http://www.joomla.org" target="_blank">Joomla!</a>
			<jdoc:translate key="isFreeSoftware">is Free Software released under the GNU/GPL License.</jdoc:translate>	
		</div>
	</body>
	</html>
