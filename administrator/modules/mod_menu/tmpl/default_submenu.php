<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * =========================================================================================================
 * IMPORTANT: The scope of this layout file is the `var  \Joomla\Module\Menu\Administrator\Menu\CssMenu` object
 * and NOT the module context.
 * =========================================================================================================
 */
/** @var  \Joomla\Module\Menu\Administrator\Menu\CssMenu  $this */
$class         = 'item';
$currentParams = $current->getParams();

// Build the CSS class suffix
if (!$this->enabled)
{
	$class .= ' disabled';
}
elseif ($current->type == 'separator')
{
	$class = $current->title ? 'menuitem-group' : 'divider';
}
elseif ($current->hasChildren())
{
	$class .= ' parent';
}

if ($current->level == 1)
{
	$class .= ' item-level-1';
}
elseif ($current->level == 2)
{
	$class .= ' item-level-2';
}
elseif ($current->level == 3)
{
	$class .= ' item-level-3';
}

// Set the correct aria role and print the item
if ($current->type == 'separator')
{
	echo '<li class="' . $class . '" role="presentation">';
}
else
{
	echo '<li class="' . $class . '">';
}

// Print a link if it exists
$linkClass  = [];
$dataToggle = '';
$iconClass  = '';
$itemIconClass = '';
$itemImage  = '';

if ($current->hasChildren())
{
	$linkClass[] = 'has-arrow';

	if ($current->level > 2)
	{
		$dataToggle  = ' data-bs-toggle="dropdown"';
	}
}
else
{
	$linkClass[] = 'no-dropdown';
}

// Implode out $linkClass for rendering
$linkClass = ' class="' . implode(' ', $linkClass) . '" ';

// Get the menu link
$link = $current->link;

// Get the menu image class
$itemIconClass = $currentParams->get('menu_icon');

// Get the menu image
$itemImage = $currentParams->get('menu_image');

// Get the menu icon
$icon      = $this->getIconClass($current);
$iconClass = ($icon != '' && $current->level == 1) ? '<span class="' . $icon . '" aria-hidden="true"></span>' : '';
$ajax      = !empty($current->ajaxbadge) ? '<span class="menu-badge"><span class="icon-spin icon-spinner mt-1 system-counter" data-url="' . $current->ajaxbadge . '"></span></span>' : '';
$iconImage = $current->icon;
$homeImage = '';

if ($iconClass === '' && $itemIconClass)
{
	$iconClass = '<span class="' . $itemIconClass . '" aria-hidden="true"></span>';
}

if ($iconImage)
{
	if (substr($iconImage, 0, 6) == 'class:' && substr($iconImage, 6) == 'icon-home')
	{
		$iconImage = '<span class="home-image icon-home" aria-hidden="true"></span>';
		$iconImage .= '<span class="visually-hidden">' . Text::_('JDEFAULT') . '</span>';
	}
	elseif (substr($iconImage, 0, 6) == 'image:')
	{
		$iconImage = '&nbsp;<span class="badge">' . substr($iconImage, 6) . '</span>';
	}
	else
	{
		$iconImage = '';
	}
}

$itemImage = (empty($itemIconClass) && $itemImage) ? '&nbsp;<img src="' . Uri::root() . $itemImage . '" alt="">&nbsp;' : '';

// If the item image is not set, the item title would not have margin. Here we add it.
if ($icon == '' && $iconClass == '' && $current->level == 1 && $current->target == '')
{
	$iconClass = '<span aria-hidden="true" class="icon-fw"></span>';
}

if ($link != '' && $current->target != '')
{
	echo '<a' . $linkClass . $dataToggle . ' href="' . $link . '" target="' . $current->target . '">'
		. $iconClass
		. '<span class="sidebar-item-title">' . $itemImage . Text::_($current->title) . '</span>' . $ajax . '</a>';
}
elseif ($link != '' && $current->type !== 'separator')
{
	echo '<a' . $linkClass . $dataToggle . ' href="' . $link . '" aria-label="' . Text::_($current->title) . '">'
		. $iconClass
		. '<span class="sidebar-item-title">' . $itemImage . Text::_($current->title) . '</span>' . $iconImage . '</a>';
}
elseif ($current->title != '' && $current->type !== 'separator')
{
	echo '<a' . $linkClass . $dataToggle . ' href="#">'
		. $iconClass
		. '<span class="sidebar-item-title">'. $itemImage . Text::_($current->title) . '</span>' . $ajax . '</a>';
}
elseif ($current->title != '' && $current->type === 'separator')
{
	echo '<span class="sidebar-item-title">' . Text::_($current->title) . '</span>' . $ajax;
}
else
{
	echo '<span>' . Text::_($current->title) . '</span>' . $ajax;
}

if ($currentParams->get('menu-quicktask') && (int) $this->params->get('shownew', 1) === 1)
{
	$params = $current->getParams();
	$user = $this->application->getIdentity();
	$link = $params->get('menu-quicktask');
	$icon = $params->get('menu-quicktask-icon', 'plus');
	$title = $params->get('menu-quicktask-title', 'MOD_MENU_QUICKTASK_NEW');
	$permission = $params->get('menu-quicktask-permission');
	$scope = $current->scope !== 'default' ? $current->scope : null;

	if (!$permission || $user->authorise($permission, $scope))
	{
		echo '<span class="menu-quicktask"><a href="' . $link . '">';
		echo '<span class="icon-' . $icon . '" title="' . htmlentities(Text::_($title)) . '" aria-hidden="true"></span>';
		echo '<span class="visually-hidden">' . Text::_($title) . '</span>';
		echo '</a></span>';
	}
}

if (!empty($current->dashboard))
{
	$titleDashboard = Text::sprintf('MOD_MENU_DASHBOARD_LINK', Text::_($current->title));
	echo '<span class="menu-dashboard"><a href="'
		. Route::_('index.php?option=com_cpanel&view=cpanel&dashboard=' . $current->dashboard) . '">'
		. '<span class="icon-th-large" title="' . $titleDashboard . '" aria-hidden="true"></span>'
		. '<span class="visually-hidden">' . $titleDashboard . '</span>'
		. '</a></span>';
}

// Recurse through children if they exist
if ($this->enabled && $current->hasChildren())
{
	if ($current->level > 1)
	{
		$id = $current->id ? ' id="menu-' . strtolower($current->id) . '"' : '';

		echo '<ul' . $id . ' class="mm-collapse collapse-level-' . $current->level . '">' . "\n";
	}
	else
	{
		echo '<ul id="collapse' . $this->getCounter() . '" class="collapse-level-1 mm-collapse">' . "\n";
	}

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$this->renderSubmenu(__FILE__, $current);

	echo "</ul>\n";
}

echo "</li>\n";
