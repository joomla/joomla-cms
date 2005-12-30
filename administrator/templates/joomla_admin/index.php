<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

$lang =& $mainframe->getLanguage();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang->getTag();?>" lang="<?php echo $lang->getTag();?>" dir="<?php echo $lang->isRTL() ? 'rtl' : 'ltr'; ?>">
<head>
<jdoc:placeholder type="head" />

<link href="templates/{TEMPLATE}/css/template.css" rel="stylesheet" type="text/css" />
<link href="templates/{TEMPLATE}/css/theme.css" rel="stylesheet" type="text/css" />
<?php if ($lang->isRTL()){ ?>
<link href="templates/{TEMPLATE}/css/template_rtl.css" rel="stylesheet" type="text/css" />
<link href="templates/{TEMPLATE}/css/theme_rtl.css" rel="stylesheet" type="text/css" />
<?php } ?>
</head>
<body>
<div id="langdirection">
<div id="wrapper">
	<div id="header">
		<div id="joomla"><?php echo JText::_( 'Administration' ); ?></div>
		<div id="version"><?php echo JText::_( 'Version#' ); ?></div>
	</div>
</div>
<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="menubackgr" style="padding-left:5px;">
		<jdoc:placeholder type="module" name="fullmenu" />
	</td>
	<td class="menubackgr" align="right">
		<div id="wrapper1">
			<jdoc:placeholder type="modules" name="header" style="2" />
		</div>
	</td>
	<td class="menubackgr" align="right" style="padding-right:5px;">
		<a href="index2.php?option=logout" style="color: #333333; font-weight: bold">
			<?php echo JText::_( 'Logout' ); ?></a>
		<strong><?php echo $my->username;?></strong>
	</td>
</tr>
</table>

<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="menudottedline" align="right">
		<jdoc:placeholder type="modules" name="toolbar" />
	</td>
</tr>
</table>
<div class="centermain">
	<div class="main">
		<jdoc:placeholder type="component" />
	</div>
</div>

<div align="center" class="footer">
	<table width="99%" border="0">
	<tr>
		<td align="center">
			<jdoc:placeholder type="modules" name="footer" />
		</td>
	</tr>
	</table>
</div>
<jdoc:placeholder type="modules" name="debug" />
</div>
</body>
</html>
