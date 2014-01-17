<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// JLayout for standard handling of the edit modules:

$moduleHtml   =& $displayData['moduleHtml'];
$mod          = $displayData['module'];
$position     = $displayData['position'];
$menusEditing = $displayData['menusediting'];


if (preg_match('/<(?:div|span|nav|ul|ol|h\d) [^>]*class="[^"]* jmoddiv"/', $moduleHtml))
{
	// Module has already module edit button:
	return;
}

// Add css class jmoddiv and data attributes for module-editing URL and for the tooltip:
$editUrl = JUri::base() . 'administrator/index.php?option=com_modules&view=module&layout=edit&id=' . (int) $mod->id;

// Add class, editing URL and tooltip, and if module of type menu, also the tooltip for editing the menu item:
$count = 0;
$moduleHtml = preg_replace(
	// Replace first tag of module with a class
	'/^(\s*<(?:div|span|nav|ul|ol|h\d) [^>]*class="[^"]*)"/',
	// By itself, adding class jmoddiv and data attributes for the url and tooltip:
	'\\1 jmoddiv" data-jmodediturl="' . $editUrl . '" data-jmodtip="'
	.	JHtml::tooltipText(
			JText::_('JLIB_HTML_EDIT_MODULE'),
			htmlspecialchars($mod->title) . '<br />' . sprintf(JText::_('JLIB_HTML_EDIT_MODULE_IN_POSITION'), htmlspecialchars($position)),
			0
		)
	. '"'
	// And if menu editing is enabled and allowed and it's a menu module, add data attributes for menu editing:
	.	($menusEditing && $mod->module == 'mod_menu' ?
			'" data-jmenuedittip="' . JHtml::tooltipText('JLIB_HTML_EDIT_MENU_ITEM', 'JLIB_HTML_EDIT_MENU_ITEM_ID') . '"'
			:
			''
		),
	$moduleHtml,
	1,
	$count
);

if ($count)
{
	// Load once booststrap tooltip and add stylesheet and javascript to head:
	JHtml::_('bootstrap.tooltip');
	JHtml::_('bootstrap.popover');

	JHtml::_('stylesheet', 'system/frontediting.css', array(), true);
	JHtml::_('script', 'system/frontediting.js', false, true);
}
