<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$minsize = $params->get('minsize', 1);
$maxsize = $params->get('maxsize', 2);

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');
?>
<div class="tagspopular<?php echo $moduleclass_sfx; ?> tagscloud<?php echo $moduleclass_sfx; ?>">
<?php
if (!count($list)) : ?>
	<div class="alert alert-no-items"><?php echo JText::_('MOD_TAGS_POPULAR_NO_ITEMS_FOUND'); ?></div>
<?php else :
	// Find maximum and minimum count
	$mincount = null;
	$maxcount = null;
	foreach ($list as $item)
	{
		if ($mincount === null || $mincount > $item->count)
		{
			$mincount = $item->count;
		}
		if ($maxcount === null || $maxcount < $item->count)
		{
			$maxcount = $item->count;
		}
	}
	$countdiff = $maxcount - $mincount;

	foreach ($list as $item) :
		if ($countdiff === 0) :
			$fontsize = $minsize;
		else :
			$fontsize = $minsize + (($maxsize - $minsize) / $countdiff) * ($item->count - $mincount);
		endif;
?>
		<span class="tag">
			<a class="tag-name" style="font-size: <?php echo $fontsize . 'em'; ?>" href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($item->tag_id . '-' . $item->alias)); ?>">
				<?php echo htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8'); ?></a>
			<?php if ($display_count) : ?>
				<span class="tag-count badge badge-info"><?php echo $item->count; ?></span>
			<?php endif; ?>
		</span>
	<?php endforeach; ?>
<?php endif; ?>
</div>
