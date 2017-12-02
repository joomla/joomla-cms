<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Menu\Node\Separator;
use Joomla\CMS\Language\Text;

/**
 * =========================================================================================================
 * IMPORTANT: The scope of this layout file is the `var  \Joomla\Module\Menu\Administrator\Menu\CssMenu` object
 * and NOT the module context.
 * =========================================================================================================
 */
/** @var  \Joomla\Module\Menu\Administrator\Menu\CssMenu  $this */
$current = $this->tree->getCurrent();
$class   = '';

// Build the CSS class suffix
if (!$this->enabled)
{
	$class = ' class="disabled"';
}
elseif ($current instanceOf Separator)
{
	$class = $current->get('title') ? ' class="menuitem-group"' : ' class="divider"';
}
elseif ($current->hasChildren())
{
	$class = ' class="dropdown-submenu"';

	if ($current->getLevel() == 1)
	{
		$class = ' class="parent"';
	}
	elseif ($current->get('class') == 'scrollable-menu')
	{
		$class = ' class="dropdown scrollable-menu"';
	}
}

// Print the item
echo '<li' . $class . ' role="menuitem">';

// Print a link if it exists
$linkClass  = [];
$dataToggle = '';
$iconClass  = '';

if ($current->hasChildren())
{
	$linkClass[] = 'collapse-arrow';

	if ($current->getLevel() > 2)
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
$icon      = $this->tree->getIconClass();
$iconClass = ($icon != '' && $current->getLevel() == 1) ? '<span class="' . $icon . '"></span>' : '';

if ($current->get('link') === '#')
{
	$link = '#collapse' . $this->tree->getCounter();
}

if ($link !== null && $current->get('target') !== null)
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\" target=\"" . $current->get('target') . "\">" 
		. $iconClass
		. '<span class="sidebar-item-title">' . Text::_($current->get('title')) . "</span></a>";
}
elseif ($link !== null && $current->get('target') === null)
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $link . "\">"
		. $iconClass
		. '<span class="sidebar-item-title" >' . Text::_($current->get('title')) . "</span></a>";
}
elseif ($current->get('title') !== null && $current->get('class') !== 'separator')
{
	echo "<a" . $linkClass . $dataToggle . ">"
		. $iconClass
		. '<span class="sidebar-item-title" >' . Text::_($current->get('title')) . "</span></a>";
}
else
{
	echo '<span>' . Text::_($current->get('title')) . '</span>';
}

// Recurse through children if they exist
if ($this->enabled && $current->hasChildren())
{
	if ($current->getLevel() > 1)
	{
		$id = $current->get('id') ? ' id="menu-' . strtolower($current->get('id')) . '"' : '';

		echo '<ul' . $id . ' class="nav panel-collapse collapse collapse-level-' . $current->getLevel() . '">' . "\n";
	}
	else
	{
		echo '<ul id="collapse' . $this->tree->getCounter() . '" class="nav panel-collapse collapse-level-1 collapse" role="menu" aria-hidden="true">
		   <li>' . Text::_($current->get('title')) . '<a href="#" class="close"><span aria-label="Close Menu">×</span></a></li>' . "\n";
	}

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$this->renderSubmenu(__FILE__);

	echo "</ul>\n";
}

echo "</li>\n";
