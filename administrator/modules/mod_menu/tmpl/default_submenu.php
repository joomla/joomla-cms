<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Menu\Node\Separator;

defined('_JEXEC') or die;

/**
 * =========================================================================================================
 * IMPORTANT: The scope of this layout file is the `var  \Joomla\Module\Menu\Administrator\Menu\CssMenu` object
 * and NOT the module context.
 * =========================================================================================================
 */
/** @var  \Joomla\Module\Menu\Administrator\Menu\CssMenu  $this */
$current = $this->tree->getCurrent();

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
	if ($current->getLevel() == 1)
	{
		$class = ' class="parent"';
	}
	elseif ($current->get('class') == 'scrollable-menu')
	{
		$class = ' class="dropdown scrollable-menu"';
	}
	else
	{
		$class = ' class="dropdown-submenu"';
	}
}
else
{
	$class = '';
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
	$dataToggle  = ' data-toggle="dropdown"';
}
else
{
	$linkClass[] = 'no-dropdown';
}

$iconClass = $this->tree->getIconClass();

// Implode out $linkClass for rendering
$linkClass = ' class="' . implode(' ', $linkClass) . '" ';

if ($current->get('link') === '#')
{
	$link = '#collapse' . $this->tree->getCounter();
}

if ($current->get('link') != null && $current->get('target') != null)
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $current->get('link') . "\" target=\"" . $current->get('target') . "\">" 
		. '<span class="' . $iconClass . '"></span>'
		. '<span class="sidebar-item-title">' . JText::_($current->get('title')) . "</span></a>";
}
elseif ($current->get('link') != null && $current->get('target') == null)
{
	echo "<a" . $linkClass . $dataToggle . " href=\"" . $current->get('link') . "\">"
		. '<span class="' . $iconClass . '"></span>'
		. '<span class="sidebar-item-title" >' . JText::_($current->get('title')) . "</span></a>";
}
elseif ($current->get('title') != null && $current->get('class') != 'separator')
{
	echo "<a" . $linkClass . $dataToggle . ">"
		. '<span class="' . $iconClass . '"></span>'
		. '<span class="sidebar-item-title" >' . JText::_($current->get('title')) . "</span></a>";
}
else
{
	echo '<span>' . JText::_($current->get('title')) . '</span>';
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
		echo '<ul class="nav panel-collapse collapse-level-1 collapse">' . "\n";
	}

	// WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
	$this->renderSubmenu(__FILE__);

	echo "</ul>\n";
}

echo "</li>\n";
