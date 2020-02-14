<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

// JLayout for standard handling of the edit modules:

$moduleHtml   = &$displayData['moduleHtml'];
$mod          = $displayData['module'];
$position     = $displayData['position'];
$menusEditing = $displayData['menusediting'];
$parameters   = JComponentHelper::getParams('com_modules');
$redirectUri  = '&return=' . urlencode(base64_encode(JUri::getInstance()->toString()));
$target       = '_blank';

if (preg_match('/<(?:div|span|nav|ul|ol|h\d) [^>]*class="[^"]* jmoddiv"/', $moduleHtml))
{
	// Module has already module edit button:
	return;
}

// Add css class jmoddiv and data attributes for module-editing URL and for the tooltip:
$editUrl = JUri::base() . 'administrator/index.php?option=com_modules&task=module.edit&id=' . (int) $mod->id;

if ($parameters->get('redirect_edit', 'site') === 'site')
{
	$editUrl = JUri::base() . 'index.php?option=com_config&controller=config.display.modules&id=' . (int) $mod->id . $redirectUri;
	$target  = '_self';
}

// Add class, editing URL and tooltip, and if module of type menu, also the tooltip for editing the menu item:
$count = 0;
$moduleHtml = preg_replace(
	// Replace first tag of module with a class
	'/^(\s*<(?:div|span|nav|ul|ol|h\d|section|aside|nav|address|article) [^>]*class="[^"]*)"/',
	// By itself, adding class jmoddiv and data attributes for the URL and tooltip:
	'\\1 jmoddiv" data-jmodediturl="' . $editUrl . '" data-target="' . $target . '" data-jmodtip="'
	.	JHtml::_('tooltipText',
			JText::_('JLIB_HTML_EDIT_MODULE'),
			htmlspecialchars($mod->title, ENT_COMPAT, 'UTF-8') . '<br />' . sprintf(JText::_('JLIB_HTML_EDIT_MODULE_IN_POSITION'), htmlspecialchars($position, ENT_COMPAT, 'UTF-8')),
			0
		)
	. '"'
	// And if menu editing is enabled and allowed and it's a menu module, add data attributes for menu editing:
	.	($menusEditing && $mod->module === 'mod_menu' ?
			'" data-jmenuedittip="' . JHtml::_('tooltipText', 'JLIB_HTML_EDIT_MENU_ITEM', 'JLIB_HTML_EDIT_MENU_ITEM_ID') . '"'
			:
			''
		),
	$moduleHtml,
	1,
	$count
);

if ($count)
{
	JHtml::_('stylesheet', 'frontend-edit.css', array('version' => 'auto', 'relative' => true));
	JHtml::_('script', 'system/frontediting.js', array('version' => 'auto', 'relative' => true));
}
