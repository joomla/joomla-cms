<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<ul class="breadcrumb <?php echo $moduleclass_sfx; ?>">
<?php if ($params->get('showHere', 1))
	{
		echo '<li class="active"><span class="divider"><i class="icon-location" rel="tooltip" title="' .JText::_('MOD_BREADCRUMBS_HERE').'"></i></span></li>';
	}
?>
<?php for ($i = 0; $i < $count; $i ++) :
	// Workaround for duplicate Home when using multilanguage
	if ($i == 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link == $list[$i - 1]->link)
	{
		continue;
	}
	// If not the last item in the breadcrumbs add the separator
	echo '<li>';
	if ($i < $count - 1)
	{
		if (!empty($list[$i]->link)) {
			echo '<a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a>';
		} else {
			echo '<span>';
			echo $list[$i]->name;
			echo '</span>';
		}
		if ($i < $count - 2)
		{
			echo '<span class="divider">/</span>';
		}
	}  elseif ($params->get('showLast', 1)) { // when $i == $count -1 and 'showLast' is true
		if($i > 0){
			echo '<span class="divider">/</span>';
		}
		echo '<span>';
		echo $list[$i]->name;
		echo '</span>';
	}
	echo '<li>';
endfor; ?>
</ul>
