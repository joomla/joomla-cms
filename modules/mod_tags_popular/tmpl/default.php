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
<?php JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php'); ?>
<div class="tagspopular<?php echo $moduleclass_sfx; ?>">
<ul >
<?php foreach ($list as $item) :	?>
<li><?php $route = new TagsHelperRoute; ?>
	<a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($item->tag_id . '-' . $item->alias)); ?>">
		<?php echo htmlspecialchars($item->title); ?></a>
</li>
<?php endforeach; ?>
</ul>
</div>
