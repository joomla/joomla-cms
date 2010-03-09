<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
?>

<ul class="menu<?php echo $params->get('moduleclass_sfx'); ?>"><?php
foreach ($list as $i => &$item) :
	
	$id = '';
	if($item->id == $active->id)
	{
		$id = ' id="current"';
	}
	$class = null;
	if(in_array($item->id, $path))
	{
		if ($item->params->get('class')) {
			$class = $item->params->get('class').' active ';
		} else {
			$class = 'active ';
		}
	}
	$class = ' class="'.$class.'item'.$item->id.'"';
	
	echo '<li'.$id.$class.'>';

	// Render the menu item.
	switch ($item->type) :
		case 'separator':
		case 'url':
		case 'component':
			require JModuleHelper::getLayoutPath('mod_menu', 'default_'.$item->type);
			break;

		default:
			require JModuleHelper::getLayoutPath('mod_menu', 'default_url');
			break;
	endswitch;

	// The next item is deeper.
	if ($item->deeper) {
		echo '<ul>';
	}
	// The next item is shallower.
	elseif ($item->shallower) {
		echo '</li>';
		echo str_repeat('</ul></li>', $item->level_diff);
	}
	// The next item is on the same level.
	else {
		echo '</li>';
	}
endforeach;
?></ul>