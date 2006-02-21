<jdoc:comment>
@copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
			<link href="templates/{TEMPLATE}/css/theme_rtl.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
		<jdoc:sub condition="0">
			<link href="templates/{TEMPLATE}/css/theme.css" rel="stylesheet" type="text/css" />
		</jdoc:sub>
	</jdoc:tmpl>
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty.css">
	<link rel="stylesheet" type="text/css" href="templates/{TEMPLATE}/css/nifty_print.css" media="print">
	<script type="text/javascript" src="../includes/js/moofx/prototype.lite.js"></script>
	<script type="text/javascript" src="../includes/js/moofx/moo.fx.js"></script>
	<script type="text/javascript" src="../includes/js/moofx/moo.fx.pack.js"></script>
	<script type="text/javascript" src="templates/{TEMPLATE}/js/moo.fx.effect.js"></script>
	<script type="text/javascript" src="templates/{TEMPLATE}/js/nifty.js"></script>
	<script type="text/javascript">
	window.onload=function(){
		if(!NiftyCheck()) alert("hello");
		Rounded("div.sidemenu-box","all","#fff","#f7f7f7","border #ccc");
		Rounded("div.component","all","#fff","#fff","border #ccc");
		Rounded("div.element-box","all","#fff","#fbfbfb","border #ccc");
		setFocus();
	}
	</script>
	<script language="javascript" type="text/javascript">
		function setFocus() {
			document.loginForm.username.select();
			document.loginForm.username.focus();
		}
	</script>
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
		<div id="content-box">
			<div id="content-pad">
			
			<div class="sidemenu-box">
				<div class="sidemenu-pad">
					<!-- toolbox module here -->
					<h2><jdoc:translate>Toolbox</jdoc:translate></h2>
					<jdoc:include type="modules" name="status" style="3" />
					<div class="status-divider"></div>
					<jdoc:include type="modules" name="menu" />
				</div>
			</div>
			<div class="content-area">
				<div class="toolbar-box">
					<div class="toolbar-pad">
					
					</div>
				</div>
			
				<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="menudottedline" align="<?php echo $lang->isRTL() ? 'left' : 'right'; ?>">
						<jdoc:include type="modules" name="toolbar" />
					</td>
				</tr>
				<tr>
					<td class="menudottedline" align="<?php echo $lang->isRTL() ? 'left' : 'right'; ?>">
						<jdoc:include type="modules" name="submenu" />
					</td>
				</tr>
				</table>
				<div class="centermain">
					<div class="main">
						<jdoc:include type="component" />
					</div>
				</div>


				<noscript>
					<jdoc:translate key="WARNJAVASCRIPT" />
				</noscript>
			</div>
		</div>
		<div class="clr"></div>
	</div>	
	<div id="footer1">
		<div id="footer2">
			<div id="footer3"></div>
		</div>
	</div>	
	
	<div id="copyright"><a href="http://www.joomla.org" target="_blank">Joomla!</a>
		<jdoc:translate key="isFreeSoftware">is Free Software released under the GNU/GPL License.</jdoc:translate>	
	</div>
	
	<script type="text/javascript">
		init_moofx();
	</script>
</body>
</html>
