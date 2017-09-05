<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$id = '';

if ($tagId = $params->get('tag_id', ''))
{
	$id = ' id="' . $tagId . '"';
}

// The menu class is deprecated. Use nav instead
?>
<ul class="nav menu<?php echo $class_sfx; ?>"<?php echo $id; ?>>
	<?php foreach ($list as $i => &$item) : ?>
		<?php $class = 'item-' . $item->id; ?>
		<?php if ($item->id == $default_id) : ?>
			<?php $class .= ' default'; ?>
		<?php endif; ?>
		<?php if ($item->id == $active_id || ($item->type === 'alias' && $item->params->get('aliasoptions') == $active_id)) : ?>
			<?php $class .= ' current'; ?>
		<?php endif; ?>
		<?php if (in_array($item->id, $path)) : ?>
			<?php $class .= ' active'; ?>
		<?php elseif ($item->type === 'alias') : ?>
			<?php $aliasToId = $item->params->get('aliasoptions'); ?>
			<?php if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) : ?>
				<?php $class .= ' active'; ?>
			<?php elseif (in_array($aliasToId, $path)) : ?>
				<?php $class .= ' alias-parent-active'; ?>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($item->type === 'separator') : ?>
			<?php $class .= ' divider'; ?>
		<?php endif; ?>
		<?php if ($item->deeper) : ?>
			<?php $class .= ' deeper'; ?>
		<?php endif; ?>
		<?php if ($item->parent) : ?>
			<?php $class .= ' parent'; ?>
		<?php endif; ?>
		<li class="<?php echo $class; ?>">
			<?php if (in_array($item->type, array('separator', 'component', 'heading', 'url'))) : ?>
				<?php require JModuleHelper::getLayoutPath('mod_menu', 'default_' . $item->type); ?>
			<?php else : ?>
				<?php require JModuleHelper::getLayoutPath('mod_menu', 'default_url'); ?>
			<?php endif; ?>
			<?php // The next item is deeper. ?>
			<?php if ($item->deeper) : ?>
				<ul class="nav-child unstyled small">
			<?php elseif ($item->shallower) : ?>
				<?php // The next item is shallower. ?>
				</li>
				<?php echo str_repeat('</ul></li>', $item->level_diff); ?>
			<?php else : ?>
				<?php // The next item is on the same level. ?>
				</li>
			<?php endif; ?>
	<?php endforeach; ?>
</ul>
