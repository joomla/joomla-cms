<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

$doc       = $app->getDocument();
$class     = $enabled ? 'nav flex-column main-nav' : 'nav flex-column main-nav disabled';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $doc->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_cpanel');
$wa->useScript('metismenujs')
	->registerAndUseScript('mod_menu.admin-menu', 'mod_menu/admin-menu.min.js', [], ['defer' => true], ['metismenujs'])
	->useScript('com_cpanel.admin-system-loader');

// Recurse through children of root node if they exist
if ($root->hasChildren())
{
	echo '<nav class="main-nav-container" aria-label="' . Text::_('MOD_MENU_ARIA_MAIN_MENU') . '">';
	echo '<ul id="menu' . $module->id . '" class="' . $class . '">' . "\n";

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$menu->renderSubmenu(ModuleHelper::getLayoutPath('mod_menu', 'default_submenu'), $root);

	echo "</ul></nav>\n";
}
