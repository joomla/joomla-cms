<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_mainmenu
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if (! defined('modMainMenuXMLCallbackDefined'))
{
	function modMainMenuXMLCallback(&$node, $args)
	{
		$user	= &JFactory::getUser();
		$groups	= $user->authorisedLevels();
		$menu	= &JSite::getMenu();
		$active	= $menu->getActive();
		$path	= isset($active) ? array_reverse($active->tree) : null;

		if (($args['end']) && ($node->attributes('level') >= $args['end']))
		{
			$children = $node->children();
			foreach ($node->children() as $child)
			{
				if ($child->name() == 'ul') {
					$node->removeChild($child);
				}
			}
		}

		if ($node->name() == 'ul') {
			foreach ($node->children() as $child)
			{
				if (!in_array($child->attributes('access'), $groups)) {
					$node->removeChild($child);
				}
			}
		}

		if (($node->name() == 'li') && isset($node->ul)) {
			$node->addAttribute('class', 'parent');
		}

		if (isset($path) && (in_array($node->attributes('id'), $path) || in_array($node->attributes('rel'), $path)))
		{
			if ($node->attributes('class')) {
				$node->addAttribute('class', $node->attributes('class').' active');
			} else {
				$node->addAttribute('class', 'active');
			}
		}
		else
		{
			if (isset($args['children']) && !$args['children'])
			{
				$children = $node->children();
				foreach ($node->children() as $child)
				{
					if ($child->name() == 'ul') {
						$node->removeChild($child);
					}
				}
			}
		}

		if (($node->name() == 'li') && ($id = $node->attributes('id'))) {
			if ($node->attributes('class')) {
				$node->addAttribute('class', $node->attributes('class').' item'.$id);
			} else {
				$node->addAttribute('class', 'item'.$id);
			}
		}

		if (isset($path) && $node->attributes('id') == $path[0]) {
			$node->addAttribute('id', 'current');
		} else {
			$node->removeAttribute('id');
		}
		$node->removeAttribute('rel');
		$node->removeAttribute('level');
		$node->removeAttribute('access');
	}
	define('modMainMenuXMLCallbackDefined', true);
}

modMainMenuHelper::render($params, 'modMainMenuXMLCallback');
