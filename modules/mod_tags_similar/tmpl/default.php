<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="tagssimilar<?php echo $moduleclass_sfx; ?>">
<?php if ($list) : ?>
	<ul>
	<?php foreach ($list as $i => $item) : ?>
		<li>
			<?php $explodedAlias = explode('.', $item->type_alias); ?>
			<?php $explodedRouter = explode('::', $item->router);?>
			<?php JLoader::register($explodedRouter[0],JPATH_BASE . '/components/' . $explodedAlias[0] . '/helpers/route.php')?>
			<?php $routerClass = $explodedRouter[0]; ?>
			<?php $routerMethod = $explodedRouter[1]; ?>
				<?php  $link = $routerClass::$routerMethod($item->content_item_id . ':' . $item->core_alias, $item->core_catid); ?>
				<?php echo  '<a href="' . JRoute::_($link) . '">'; ?>
				<?php echo $item->core_title; ?>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<span><?php echo JText::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS'); ?></span>
<?php endif; ?>
</div>
