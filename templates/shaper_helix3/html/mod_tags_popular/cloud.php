<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');
?>
<div class="tagspopular<?php echo $moduleclass_sfx; ?> tagscloud<?php echo $moduleclass_sfx; ?>">
<?php
if (!count($list)) : ?>
	<div class="alert alert-warning"><?php echo JText::_('MOD_TAGS_POPULAR_NO_ITEMS_FOUND'); ?></div>
<?php else :
	foreach ($list as $item) :?>
		<a class="tag-name" href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($item->tag_id . ':' . $item->alias)); ?>">
			<?php echo htmlspecialchars($item->title); ?>
			<?php if ($display_count) : ?>
				<span><?php echo $item->count; ?></span>
			<?php endif; ?>
		</a>	
	<?php endforeach; ?>
<?php endif; ?>
</div>
