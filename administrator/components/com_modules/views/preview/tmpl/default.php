<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// @deprecated  4.0 not used for a long time

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration(
	'
	var form = window.top.document.adminForm
	var title = form.title.value;
	var alltext = window.top.' . $this->editor->getContent('text') . ';

	jQuery(document).ready(function() {
		document.getElementById("td-title").innerHTML = title;
		document.getElementById("td-text").innerHTML = alltext;
	});'
);
?>

<table class="center" width="90%">
	<tr>
		<td class="contentheading" colspan="2" id="td-title"></td>
	</tr>
<tr>
	<td valign="top" height="90%" colspan="2" id="td-text"></td>
</tr>
</table>
