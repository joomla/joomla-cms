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
			<?php $item->route = new JHelperRoute; ?>
			<a href="<?php echo JRoute::_($item->route->getRoute($item->content_item_id, $item->type_alias, $item->link, $item->core_language, $item->core_catid)); ?>">
				<?php if (!empty($item->core_title)) :
					echo htmlspecialchars($item->core_title);
				endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<span><?php echo JText::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS'); ?></span>
<?php endif; ?>
</div>
