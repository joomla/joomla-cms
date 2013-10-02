<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// JLayout for standard handling of the edit modules:

$moduleHtml =& $displayData['moduleHtml'];
$mod = $displayData['module'];
$position = $displayData['position'];

static $jsNotOut =true;

$app = JFactory::getApplication();

$cannotEditFrontend = $app->isAdmin() || !JFactory::getUser()->authorise('core.manage', 'com_modules');


if (!$moduleHtml)
{
	return;
}

if ($cannotEditFrontend
	|| preg_match('/<(?:div|span|nav|ul) [^>]*class="[^"]* jmoddiv"/', $moduleHtml))
{
	// Module isn't enclosing in a div with class or handles already module edit button:
	return;
}

// Add css class jmoddiv and data attributes for module-editing URL and for the tooltip:
$editUrl = JURI::base() . 'administrator/' . 'index.php?option=com_modules&view=module&layout=edit&id=' . (int) $mod->id;

// Add class, editing URL and tooltip, and if module of type menu, also the tooltip for editing the menu item:
$moduleHtml = preg_replace('/^(<(?:div|span|nav|ul) [^>]*class="[^"]*)"/',
	'\\1 jmoddiv" data-jmodediturl="' . $editUrl. '"'
	. ' data-jmodtip="'
	. JHtml::tooltipText(JText::_('JLIB_HTML_EDIT_MODULE'), htmlspecialchars($mod->title) . '<br />' . sprintf(JText::_('JLIB_HTML_EDIT_MODULE_IN_POSITION'), htmlspecialchars($position)), 0)
	. '"'
	. ($mod->module == 'mod_menu' ? '" data-jmenuedittip="' . JHtml::tooltipText('JLIB_HTML_EDIT_MENU_ITEM', 'JLIB_HTML_EDIT_MENU_ITEM_ID') . '"' : ''),
	$moduleHtml);

if ($jsNotOut)
{
	// Load once booststrap tooltip and add stylesheet and javascript to head:
	$jsNotOut = false;
	JHtml::_('bootstrap.tooltip');
	JHtml::_('bootstrap.popover');

	JFactory::getDocument()->addStyleSheet('media/system/css/frontediting.css')
		->addScript('media/system/js/frontediting.js');
}

?>
