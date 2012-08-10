<?php
/**
 * @package     Jokte.Site
 * @subpackage	joktekyju
 * @copyright   Copyright (C) 2012 - 2014 Open Jokte, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */
// Previene el acceso directo.
defined('_JEXEC') or die;

/*
 * Menú responsive
 * 
 */
?>
<ul id="nav" role="navigation">
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
			$class .= 'selected ';
		}
		if($item->deeper) {
			$class .= 'parent ';
		}

		$class = ' class="'.$class.'item'.$item->id.'"';

		echo '<li class="top-level item-with-ul">';

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
		
		if ($item->deeper) {
			echo '<ul class="sub-menu">';
		}
		elseif ($item->shallower) {
			echo '</li>';
			echo str_repeat('</ul></li>', $item->level_diff);
		}
		else {
			echo '</li>';
		}
	endforeach;
?></ul>
