<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('script', 'vendor/metismenujs/metismenujs.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'mod_menu/admin-menu.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
HTMLHelper::_('script', 'com_cpanel/admin-system-loader.js', ['version' => 'auto', 'relative' => true]);

$doc       = $app->getDocument();
$direction = $doc->direction === 'rtl' ? 'float-right' : '';
$class     = $enabled ? 'nav flex-column main-nav ' . $direction : 'nav flex-column main-nav disabled ' . $direction;

// Recurse through children of root node if they exist
if ($root->hasChildren())
{
	echo '<nav class="main-nav-container" aria-label="' . Text::_('MOD_MENU_ARIA_MAIN_MENU') . '">';
	echo '<ul id="menu" class="' . $class . '" role="menu">' . "\n";

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$menu->renderSubmenu(ModuleHelper::getLayoutPath('mod_menu', 'default_submenu'), $root);

	echo "</ul></nav>\n";
}
