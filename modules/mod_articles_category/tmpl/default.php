<?php


/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_articles_category
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul class="category-module<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($grouped) : ?>
	<?php foreach ($list as $group_name => $group) : ?>
	<li>
		<h<?php echo $params->get('item_heading'); ?>><?php echo $group_name; ?></h<?php echo $params->get('item_heading'); ?>>
		<ul>
			<?php foreach ($group as $item) : ?>
				<li>
					<h<?php echo $params->get('item_heading')+1; ?>>
					<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
					<?php echo $item->title; ?>
						<?php if ($item->displayHits) :?>
							<span class="mod-articles-category-hits">
            				(<?php echo $item->displayHits; ?>)</span>
                 		<?php endif; ?></a>
                 	</h<?php echo $params->get('item_heading')+1; ?>>

				<?php if ($params->get('show_author')) :?>
            		<span class="mod-articles-category-writtenby">
					<?php echo $item->displayAuthorName; ?>
					</span>
				<?php endif;?>

				<?php if ($item->displayCategoryTitle) :?>
					<span class="mod-articles-category-category">
					(<?php echo $item->displayCategoryTitle; ?>)
					</span>
				<?php endif; ?>
				<?php if ($item->displayDate) : ?>
					<span class="mod-articles-category-date"><?php echo $item->displayDate; ?></span>
				<?php endif; ?>
				<?php if ($params->get('show_introtext')) :?>
					<span class="mod-articles-category-introtext">
					<?php echo $item->displayIntrotext; ?>
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
	   	<h<?php echo $params->get('item_heading'); ?>>
		<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
		<?php echo $item->title; ?>
		<?php if ($item->displayHits) :?>
			<span class="mod-articles-category-hits">
            (<?php echo $item->displayHits; ?>)  </span>
        <?php endif; ?></a>
        </h<?php echo $params->get('item_heading'); ?>>

       	<?php if ($params->get('show_author')) :?>
       		<span class="mod-articles-category-writtenby">
			<?php echo $item->displayAuthorName; ?>
			</span>
		<?php endif;?>
		<?php if ($item->displayCategoryTitle) :?>
			<span class="mod-articles-category-category">
			(<?php echo $item->displayCategoryTitle; ?>)
			</span>
		<?php endif; ?>
        <?php if ($item->displayDate) : ?>
			<span class="mod-articles-category-date"><?php echo $item->displayDate; ?></span>
		<?php endif; ?>
		<?php if ($params->get('show_introtext')) :?>
			<span class="mod-articles-category-introtext">
			<?php echo $item->displayIntrotext; ?>
			</span>
		<?php endif; ?>

	</li>
	<?php endforeach; ?>
<?php endif; ?>
</ul>
