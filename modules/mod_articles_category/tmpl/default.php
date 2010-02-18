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
		<?php echo $group_name; ?>
		<ul>
			<?php foreach ($group as $item) : ?>
			<li>
				<a class="<?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
					<?php echo $item->title; ?> <?php echo $item->displayHits; ?></a>
				<?php echo $item->displayAuthorName; ?>
				<?php echo $item->displayCategoryTitle; ?>
				<?php echo $item->displayIntrotext; ?>
				<?php echo $item->displayDate; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</li>
	<?php endforeach; ?>
<?php else : ?>
	<?php foreach ($list as $item) : ?>
	<li>
		<a class="<?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
			<?php echo $item->title; ?> <?php echo $item->displayHits; ?></a>
		<?php echo $item->displayAuthorName; ?>
		<?php echo $item->displayCategoryTitle; ?>
		<?php echo $item->displayIntrotext; ?>
		<?php echo $item->displayDate; ?>
	</li>
	<?php endforeach; ?>
<?php endif; ?>
</ul>
