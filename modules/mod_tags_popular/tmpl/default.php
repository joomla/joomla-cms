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
<div class="tagspopular<?php echo $moduleclass_sfx; ?>">
<ul >
<?php foreach ($list as $item) :	?>
<li>
	<a href="<?php echo JRoute::_('index.php?option=com_tags&view=tag&id=' . $item->tag_id . ':' . $item->alias); ?>">
		<?php echo htmlspecialchars($item->title); ?></a>
</li>
<?php endforeach; ?>
</ul>
</div>
