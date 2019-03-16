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

if ($link != '' && $current->target != '')
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\" target=\"" . $current->target . "\">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->title) . "</span></a>";
}
elseif ($link != '')
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->get('title')) . "</span></a>";
}
elseif ($current->title != '' && $current->get('class') !== 'separator')
{
	echo "<a" . $linkClass . $dataToggle . ">"
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->get('title')) . "</span></a>";
}
else
{
	echo '<span>' . Text::_($current->get('title')) . '</span>';
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
