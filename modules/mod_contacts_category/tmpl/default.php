<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_contacts_category
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<ul class="category-module<?php echo $moduleclass_sfx; ?>">
	<?php if ($grouped) : ?>
		<?php foreach ($list as $group_name => $group) : ?>
		<li>
			<div class="mod-contacts-category-group"><?php echo $group_name;?></div>
			<ul>
				<?php foreach ($group as $item) : ?>
					<li>
						<?php if ($params->get('link_titles') == 1) : ?>
							<a class="mod-contacts-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
								<?php echo $item->title; ?>
							</a>
						<?php else : ?>
							<?php echo $item->title; ?>
						<?php endif; ?>

						<?php if ($item->displayHits) : ?>
							<span class="mod-contacts-category-hits">
								(<?php echo $item->displayHits; ?>)
							</span>
						<?php endif; ?>



						<?php if ($item->displayCategoryTitle) : ?>
							<span class="mod-contacts-category-category">
								(<?php echo $item->displayCategoryTitle; ?>)
							</span>
						<?php endif; ?>


					</li>
				<?php endforeach; ?>
			</ul>
		</li>
		<?php endforeach; ?>
	<?php else : ?>
		<?php foreach ($list as $item) : ?>
			<li>
				<?php if ($params->get('link_titles') == 1) : ?>
					<a class="mod-contacts-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
						<?php echo $item->title; ?>
					</a>
				<?php else : ?>
					<?php echo $item->title; ?>
				<?php endif; ?>

				<?php if ($item->displayHits) : ?>
					<span class="mod-contacts-category-hits">
						(<?php echo $item->displayHits; ?>)
					</span>
				<?php endif; ?>



				<?php if ($item->displayCategoryTitle) : ?>
					<span class="mod-contacts-category-category">
						(<?php echo $item->displayCategoryTitle; ?>)
					</span>
				<?php endif; ?>

			</li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
