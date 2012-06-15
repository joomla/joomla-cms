<?php
/**
 * @package		Joomla.Site
 * @subpackage	Templates.atomic
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
?>
<!-- The class on the root UL tag was changed to match the Blueprint nav style -->
<ul class="joomla-nav<?php echo $params->get('class_sfx');?>"<?php
	$tag = '';
	if ($params->get('tag_id')!=NULL) {
		$tag = $params->get('tag_id').'';
		echo ' id="'.$tag.'"';
	}
?>>
<?php
foreach ($list as $i => &$item) :
	$id = '';
	if($item->id == $active_id)
	{
		$id = ' id="current"';
	}
	$class = '';
	if(in_array($item->id, $path))
	{
		// Changed the active style to match the Blueprint nav style
		$class .= 'selected ';
	}
	if($item->deeper) {
		$class .= 'parent ';
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
