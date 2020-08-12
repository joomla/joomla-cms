<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Utilities\ArrayHelper;

/** @var WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('metismenu', 'mm-horizontal.js', [], [], ['metismenujs']);

$ulAttribs = [
		'id'    => $params->get('tag_id', ''),
		'class' => 'mod-menu mod-menu_metismenu metismenu mod-list ' . $class_sfx
];

// The menu class is deprecated. Use mod-menu instead
?>
<ul <?php echo ArrayHelper::toString($ulAttribs); ?>>
<?php foreach ($list as $i => &$item)
{
	$itemParams = $item->getParams();
	$class[]    = 'metismenu-item item-' . $item->id;

	if ($item->id == $default_id)
	{
		$class[] = 'default';
	}

	if ($item->id == $active_id || ($item->type === 'alias' && $itemParams->get('aliasoptions') == $active_id))
	{
		$class[] = 'current';
	}

	if (in_array($item->id, $path))
	{
		$class[] = 'active';
	}
	else if ($item->type === 'alias')
	{
		$aliasToId = $itemParams->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class[] = 'active';
		}
		else if (in_array($aliasToId, $path))
		{
			$class[] = 'alias-parent-active';
		}
	}

	if ($item->type === 'separator')
	{
		$class[] = 'divider';
	}

	if ($item->deeper)
	{
		$class[] = 'deeper';
	}

	if ($item->parent)
	{
		$class[] = 'parent';
	}

	echo '<li class="' . implode(' ', $class) . '">';

	switch ($item->type) :
		case 'separator':
		case 'component':
		case 'heading':
		case 'url':
			require ModuleHelper::getLayoutPath('mod_menu', 'metismenu_' . $item->type);
			break;

		default:
			require ModuleHelper::getLayoutPath('mod_menu', 'metismenu_url');
			break;
	endswitch;

	switch (true) :
		// The next item is deeper.
		case $item->deeper:
			echo '<ul class="mm-collapse">';
			break;

		// The next item is shallower.
		case $item->shallower:
			echo '</li>';
			echo str_repeat('</ul></li>', $item->level_diff);
			break;

		// The next item is on the same level.
		default:
			echo '</li>';
			break;
	endswitch;
}
?></ul>
