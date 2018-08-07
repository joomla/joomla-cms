<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="tagssimilar<?php echo $moduleclass_sfx; ?>">
<?php if ($list) : ?>
	<ul>
	<?php foreach ($list as $i => $item) : ?>
		<li>
			<?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
				<?php if (!empty($item->core_title)) : ?>
					<?php echo htmlspecialchars($item->core_title, ENT_COMPAT, 'UTF-8'); ?>
				<?php endif; ?>
			<?php else : ?>
				<a href="<?php echo JRoute::_($item->link); ?>">
					<?php if (!empty($item->core_title)) : ?>
						<?php echo htmlspecialchars($item->core_title, ENT_COMPAT, 'UTF-8'); ?>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<span><?php echo JText::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS'); ?></span>
<?php endif; ?>
</div>
