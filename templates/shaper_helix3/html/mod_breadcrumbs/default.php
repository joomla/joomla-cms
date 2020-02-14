<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<ol class="breadcrumb<?php echo $moduleclass_sfx; ?>">
	<?php
	if ($params->get('showHere', 1))
	{
		echo '<span>' . JText::_('MOD_BREADCRUMBS_HERE') . '&#160;</span>';
	}
	else
	{
		echo '<li><i class="fa fa-home"></i></li>';
	}

	// Get rid of duplicated entries on trail including home page when using multilanguage
	for ($i = 0; $i < $count; $i++)
	{
		if ($i == 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link == $list[$i - 1]->link)
		{
			unset($list[$i]);
		}
	}

	end($list);
	$last_item_key = key($list);
	prev($list);
	$penult_item_key = key($list);

	$show_last = $params->get('showLast', 1);

	foreach ($list as $key => $item) {
		if ($key != $last_item_key) {
			echo '<li>';
			if (!empty($item->link)) {
				echo '<a href="' . $item->link . '" class="pathway">' . $item->name . '</a>';
			} else {
				echo $item->name;
			}
			echo '</li>';
		} elseif ($show_last) {
			echo '<li class="active">' . $item->name . '</li>';
		}
	}
	?>
</ol>
