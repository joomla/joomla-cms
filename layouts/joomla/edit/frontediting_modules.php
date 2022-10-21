<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// JLayout for standard handling of the edit modules:

$moduleHtml   = &$displayData['moduleHtml'];
$mod          = $displayData['module'];
$position     = $displayData['position'];
$menusEditing = $displayData['menusediting'];
$parameters   = ComponentHelper::getParams('com_modules');
$redirectUri  = '&return=' . urlencode(base64_encode(Uri::getInstance()->toString()));
$target       = '_blank';
$itemid       = Factory::getApplication()->input->get('Itemid', '0', 'int');
$editUrl      = Uri::base() . 'administrator/index.php?option=com_modules&task=module.edit&id=' . (int) $mod->id;

// If Module editing site
if ($parameters->get('redirect_edit', 'site') === 'site') {
    $editUrl = Uri::base() . 'index.php?option=com_config&view=modules&id=' . (int) $mod->id . '&Itemid=' . $itemid . $redirectUri;
    $target  = '_self';
}

// Add link for editing the module
$count = 0;
$moduleHtml = preg_replace(
    // Find first tag of module
    '/^(\s*<(?:div|span|nav|ul|ol|h\d|section|aside|address|article|form) [^>]*>)/',
    // Create and add the edit link and tooltip
    '\\1 <a class="btn btn-link jmodedit" href="' . $editUrl . '" target="' . $target . '" aria-describedby="tip-' . (int) $mod->id . '">
	<span class="icon-edit" aria-hidden="true"></span><span class="visually-hidden">' . Text::_('JGLOBAL_EDIT') . '</span></a>
	<div role="tooltip" id="tip-' . (int) $mod->id . '">' . Text::_('JLIB_HTML_EDIT_MODULE') . '<br>' . htmlspecialchars($mod->title, ENT_COMPAT, 'UTF-8') . '<br>' . sprintf(Text::_('JLIB_HTML_EDIT_MODULE_IN_POSITION'), htmlspecialchars($position, ENT_COMPAT, 'UTF-8')) . '</div>',
    $moduleHtml,
    1,
    $count
);

// If menu editing is enabled and allowed and it's a menu module add link for editing
if ($menusEditing && $mod->module === 'mod_menu') {
    // find the menu item id
    $regex = '/\bitem-(\d+)\b/';

    preg_match_all($regex, $moduleHtml, $menuItemids);
    if ($menuItemids) {
        foreach ($menuItemids[1] as $menuItemid) {
                $menuitemEditUrl = Uri::base() . 'administrator/index.php?option=com_menus&view=item&client_id=0&layout=edit&id=' . (int) $menuItemid;
                $moduleHtml = preg_replace(
                    // Find the link
                    '/(<li.*?\bitem-' . $menuItemid . '.*?>)/',
                    // Create and add the edit link
                    '\\1 <a class="jmenuedit small" href="' . $menuitemEditUrl . '" target="' . $target . '" title="' . Text::_('JLIB_HTML_EDIT_MENU_ITEM') . ' ' . sprintf(Text::_('JLIB_HTML_EDIT_MENU_ITEM_ID'), (int) $menuItemid) . '">
					<span class="icon-edit" aria-hidden="true"></span></a>',
                    $moduleHtml
                );
        }
    }
}
