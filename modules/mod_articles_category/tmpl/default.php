<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$tagLayout = $params->get('show_tags', 0) ? new JLayoutFile('joomla.content.tags') : null;

?>
<ul class="category-module<?php echo $moduleclass_sfx; ?>">
	<?php if ($grouped) : ?>
		<?php foreach ($list as $group_name => $list) : ?>
			<li>
				<div class="mod-articles-category-group">
					<?php echo $group_name; ?>
				</div>
				<?php require JModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default') . '_items'); ?>
		</li>
		<?php endforeach; ?>
	<?php else : ?>
		<?php require JModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default') . '_items'); ?>
	<?php endif; ?>
</ul>
