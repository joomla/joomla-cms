<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_cloud
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$display_count = $params->get('display_count', 0);
$no_results_text= $params->get('no_results_text', false);
?>
<?php JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php'); ?>
<div class="tagcloud<?php echo $moduleclass_sfx; ?>">
<?php if (!count($list)) : ?>
  <?php echo $no_results_text; ?>
<?php else : ?>
<?php foreach ($list as $item) :	?>
  <?php $route = new TagsHelperRoute; ?>
  <span class="tag">
    <a class="tag-name" style="font-size: <?php echo $item->size.'em'; ?>" href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($item->tag_id . ':' . $item->alias)); ?>"><?php echo htmlspecialchars($item->title); ?></a>
    <?php if ($display_count): ?>
    <span class="tag-count badge badge-info"><?php echo $item->count; ?></span>
    <?php endif; ?>
  </span>
<?php endforeach; ?>
<?php endif; ?>
</div>
