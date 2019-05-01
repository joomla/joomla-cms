<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * =========================================================================================================
 * IMPORTANT: The scope of this layout file is the `var  \Joomla\Module\Menu\Administrator\Menu\CssMenu` object
 * and NOT the module context.
 * =========================================================================================================
 */
/** @var  \Joomla\Module\Menu\Administrator\Menu\CssMenu  $this */
$class   = '';

// Build the CSS class suffix
if (!$this->enabled)
{
	$class = ' class="disabled"';
}
elseif ($current->type == 'separator')
{
	$class = $current->title ? ' class="menuitem-group"' : ' class="divider"';
}
elseif ($current->hasChildren())
{
	$class = ' class="dropdown-submenu"';

	if ($current->level == 1)
	{
		$class = ' class="parent"';
	}
	elseif ($current->get('class') === 'scrollable-menu')
	{
		$class = ' class="dropdown scrollable-menu"';
	}
}

// Set the correct aria role and print the item
if ($current->type == 'separator')
{
	echo '<li' . $class . ' role="presentation">';
}
else
{
	echo '<li' . $class . ' role="menuitem">';
}

// Print a link if it exists
$linkClass  = [];
$dataToggle = '';
$iconClass  = '';

if ($current->hasChildren())
{
	$linkClass[] = 'has-arrow';

	if ($current->level > 2)
	{
		$dataToggle  = ' data-toggle="dropdown"';
	}
}
else
{
	$linkClass[] = 'no-dropdown';
}

// Implode out $linkClass for rendering
$linkClass = ' class="' . implode(' ', $linkClass) . '" ';

// Get the menu link
$link      = $current->get('link');

// Get the menu icon
$icon      = $this->getIconClass($current);
$iconClass = ($icon != '' && $current->level == 1) ? '<span class="' . $icon . '" aria-hidden="true"></span>' : '';
$ajax      = $current->ajaxbadge ? '<span class="menu-badge"><span class="fa fa-spin fa-spinner mt-1 system-counter" data-url="' . $current->ajaxbadge . '"></span></span>' : '';

if ($link != '' && $current->target != '')
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\" target=\"" . $current->target . "\">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->title) . '</span>' . $ajax . '</a>';
}
elseif ($link != '')
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->title) . '</span>' . $ajax . '</a>';
}
elseif ($current->title != '' && $current->get('class') !== 'separator')
{
	echo "<a" . $linkClass . $dataToggle . ">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->title) . '</span>' . $ajax . '</a>';
}
else
{
	echo '<span>' . Text::_($current->title) . '</span>' . $ajax;
}

if ($current->getParams()->get('menu-quicktask', false))
{
	$params = $current->getParams();
	$user = $this->application->getIdentity();
	$link = $params->get('menu-quicktask-link');
	$icon = $params->get('menu-quicktask-icon', 'plus');
	$title = $params->get('menu-quicktask-title', 'MOD_MENU_QUICKTASK_NEW');
	$permission = $params->get('menu-quicktask-permission');
	$scope = $current->scope !== 'default' ? $current->scope : null;

	if (!$permission || $user->authorise($permission, $scope))
	{
		echo '<span class="menu-quicktask"><a href="' . $link . '">';
		echo '<span class="fa fa-' . $icon . '" title="' . htmlentities(Text::_($title)) . '" aria-hidden="true"></span>';
		echo '<span class="sr-only">' . Text::_($title) . '</span>';
		echo '</a></span>';
	}
}

if ($current->dashboard)
{
	$titleDashboard = Text::_('MOD_MENU_DASHBOARD_LINK') . ': ' . Text::_($current->title);
	echo '<span class="menu-dashboard"><a href="'
		. JRoute::_('index.php?option=com_cpanel&view=cpanel&dashboard=' . $current->dashboard) . '">'
		. '<span class="fa fa-th-large" title="' . $titleDashboard . '" aria-hidden="true"></span>'
		. '<span class="sr-only">' . $titleDashboard . '</span>'
		. '</a></span>';
}

// Recurse through children if they exist
if ($this->enabled && $current->hasChildren())
{
	if ($current->level > 1)
	{
		$id = $current->get('id') ? ' id="menu-' . strtolower($current->get('id')) . '"' : '';

		echo '<ul' . $id . ' class="collapse collapse-level-' . $current->level . '">' . "\n";
	}
	else
	{
		echo '<ul id="collapse' . $this->getCounter() . '" class="collapse-level-1 collapse" role="menu" aria-haspopup="true">' . "\n";
	}

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$this->renderSubmenu(__FILE__, $current);

	echo "</ul>\n";
}

echo "</li>\n";
