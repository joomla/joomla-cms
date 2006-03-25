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
<link href="templates/{TEMPLATE}/css/icon.css" rel="stylesheet" type="text/css" />
<jdoc:tmpl name="isRTL" varscope="index.php" type="condition" conditionvar="LANG_ISRTL">
	<jdoc:sub condition="1">
		<link href="templates/{TEMPLATE}/css/template_rtl.css" rel="stylesheet" type="text/css" />
		<link href="templates/{TEMPLATE}/css/theme_rtl.css" rel="stylesheet" type="text/css" />
	</jdoc:sub>
	<jdoc:sub condition="0">
		<link href="templates/{TEMPLATE}/css/theme.css" rel="stylesheet" type="text/css" />
	</jdoc:sub>
</jdoc:tmpl>

<!--[if !IE]> <-->

<!--> <![endif]-->
<style type="text/css" media="screen, tv, projection">
	@import "templates/{TEMPLATE}/css/menu.css";
</style>
<!--[if lte IE 6]>
<style type="text/css" media="screen, tv, projection">
	@import "templates/{TEMPLATE}/css/menu4ie.css";
	body { behavior:url("templates/{TEMPLATE}/js/ADxMenu.htc"); }
</style>
<![endif]-->

</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="joomla"><jdoc:translate>Administration</jdoc:translate></div>
		<div id="version"><jdoc:translate>Version#</jdoc:translate></div>
	</div>
</div>
<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="menubackgr" style="padding-<?php echo $lang->isRTL() ? 'right' : 'left'; ?>:5px;">
		<jdoc:include type="module" name="fullmenu" />
	</td>
	<td class="menubackgr" align="<?php echo $lang->isRTL() ? 'left' : 'right'; ?>">
		<div id="wrapper1">
			<jdoc:include type="modules" name="header" style="2" />
		</div>
	</td>
	<td class="menubackgr" align="<?php echo $lang->isRTL() ? 'left' : 'right'; ?>" style="padding-<?php echo $lang->isRTL() ? 'left' : 'right'; ?>:5px;">
		<a href="index2.php?option=logout" style="color: #333333; font-weight: bold">
			<jdoc:translate>Logout</jdoc:translate></a>
		<strong><?php echo $my->username;?></strong>
	</td>
</tr>
</table>

<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="menudottedline" align="<?php echo $lang->isRTL() ? 'left' : 'right'; ?>">
		<jdoc:include type="modules" name="toolbar" />
		<jdoc:include type="modules" name="title" />
	</td>
</tr>
</table>
<div class="centermain">
	<div class="main">
		<jdoc:include type="component" />
	</div>
</div>

<div align="center" class="footer">
	<table width="99%" border="0">
	<tr>
		<td align="center">
			<span style="color: red; font-weight: bold;">
			** This is NOT meant to be used for a `live` or `production` site **
			</span>
			<jdoc:include type="modules" name="footer" />
		</td>
	</tr>
	</table>
</div>
<jdoc:include type="modules" name="debug" />
</body>
</html>