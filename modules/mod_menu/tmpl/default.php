<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<ul class="menu<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
$level = $list[0]->level;
for ($i=0,$n=count($list); $i<$n; $i++)
{
	$item = $list[$i];

	// The next item is deeper.
	if ($item->deeper)
	{
		echo "\n\t<li>";
	}
	// The next item is shallower.
	elseif ($item->shallower)
	{
		echo "\n\t<li>";

	}
	// The next item is on the same level.
	else {
		echo "\n\t<li>";
	}

	// Render the menu item.
	require JModuleHelper::getLayoutPath('mod_menu', 'default_item');

	// The next item is deeper.
	if ($item->deeper)
	{
		echo "\n\t<ul>";
	}
	// The next item is shallower.
	elseif ($item->shallower)
	{
		echo "\n\t</li>";
		echo str_repeat("\n\t</ul>\n\t</li>", $item->level_diff);
	}
	// The next item is on the same level.
	else {
		echo "\n\t</li>";
	}
}
?>
</ul>