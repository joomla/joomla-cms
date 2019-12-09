<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
/** @var string $default_id */
/** @var string $module */
/** @var string $active_id */

$id = '';

if ($tagId = $params->get('tag_id', ''))
{
	$id = ' id="' . $tagId . '"';
}

// Getting params from template
$templateparams = $app->getTemplate(true)->params;

// Lets remove all other trim but a single preceding space.
$class_sfx = $class_sfx ? " " . trim($class_sfx) : '';

if ($module->position == 'menu')
{

// If "nav-" ( like nav-pills, nav-tabs, nav-stacked ) is found then navbar is not added.
	$class_sfx = stristr($class_sfx, "nav-") ? $class_sfx : " navbar-nav" . $class_sfx;
}

// Finally lets set the isNavbarNav flag
$isNavbarNav = stristr($class_sfx, 'navbar-nav');
?>

<ul class="nav navmenu-nav flex-row <?php echo $class_sfx . '"' . $id; ?>>
	<?php foreach ($list as $i => &$item)
{
	$item->isNavbarNav = $isNavbarNav;

	$class = 'nav-item item-' . $item->id;

	if ($item->id == $default_id)
	{
		$class .= ' default';
	}

	if ($item->id == $active_id || ($item->type === 'alias' && $item->params->get('aliasoptions') == $active_id))
	{
		$class .= ' current';
	}

	if (in_array($item->id, $path))
	{
		$class .= ' active';
	}
	elseif ($item->type === 'alias')
	{
		$aliasToId = $item->params->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class .= ' active';
		}
		elseif (in_array($aliasToId, $path))
		{
			$class .= ' alias-parent-active';
		}
	}

	if ($item->type === 'separator')
	{
		$class .= ' separator';
	}

	if ($item->deeper)
	{
		$class .= ' deeper';
	}

	$item->isParentAnchor = false;

	if ($item->parent)
	{
		$class                .= ' parent';
		$item->isParentAnchor = true;

		if ($isNavbarNav)
		{
			$class .= ' dropdown';

			if ($item->level > 1)
			{
				$class .= ' dropdown-submenu';
			}
		}
	}

	echo '<li class="' . trim($class) . '">';

	switch ($item->type) :
		case 'separator':
		case 'component':
		case 'heading':
		case 'url':
			require JModuleHelper::getLayoutPath('mod_menu', 'default_' . $item->type);
			break;

		default:
			require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
			break;
	endswitch;

	// The next item is deeper.
	if ($item->deeper)
	{
		echo $isNavbarNav ? '<ul class="nav-child unstyled small dropdown-menu">' : '<ul class="nav-child unstyled small">';
	}
	// The next item is shallower.
	elseif ($item->shallower)
	{
		echo '</li>';
		echo str_repeat('</ul></li>', $item->level_diff);
	}
	// The next item is on the same level.
	else
	{
		echo '</li>';
	}
}
?></ul>
