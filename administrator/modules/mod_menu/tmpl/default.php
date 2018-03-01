<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc       = JFactory::getDocument();
$direction = $doc->direction == 'rtl' ? 'pull-right' : '';
$class     = $enabled ? 'nav ' . $direction : 'nav disabled ' . $direction;

// Recurse through children of root node if they exist
$menuTree = $menu->getTree();
$root     = $menuTree->reset();

if ($root->hasChildren())
{
	echo '<ul id="menu" class="' . $class . '">' . "\n";

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$menu->renderSubmenu(JModuleHelper::getLayoutPath('mod_menu', 'default_submenu'));

	echo "</ul>\n";

	echo '<ul id="nav-empty" class="dropdown-menu nav-empty hidden-phone"></ul>';

	if ($css = $menuTree->getCss())
	{
		$doc->addStyleDeclaration(implode("\n", $css));
	}
}
