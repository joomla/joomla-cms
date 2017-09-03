<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>
<ul itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb<?php echo $moduleclass_sfx; ?>">
	<?php if ($params->get('showHere', 1)) : ?>
		<li>
			<?php echo JText::_('MOD_BREADCRUMBS_HERE'); ?>&#160;
		</li>
	<?php else : ?>
		<li class="active">
			<span class="divider icon-location"></span>
		</li>
	<?php endif; ?>
	<?php // Get rid of duplicated entries on trail including home page when using multilanguage ?>
	<?php for ($i = 0; $i < $count; $i++) : ?>
		<?php if ($i === 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link === $list[$i - 1]->link) : ?>
			<?php unset($list[$i]); ?>
		<?php endif; ?>
	<?php endfor; ?>
	<?php // Find last and penultimate items in breadcrumbs list ?>
	<?php end($list); ?>
	<?php $last_item_key = key($list); ?>
	<?php prev($list); ?>
	<?php $penult_item_key = key($list); ?>
	<?php // Make a link if not the last item in the breadcrumbs ?>
	<?php $show_last = $params->get('showLast', 1); ?>
	<?php // Generate the trail ?>
	<?php foreach ($list as $key => $item) : ?>
		<?php if ($key !== $last_item_key) : ?>
			<?php // Render all but last item - along with separator ?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<?php if (!empty($item->link)) : ?>
					<a itemprop="item" href="<?php echo $item->link; ?>" class="pathway">
						<span itemprop="name">
							<?php echo $item->name; ?>
						</span>
					</a>
				<?php else : ?>
					<span itemprop="name">
						<?php echo $item->name; ?>
					</span>
				<?php endif; ?>
				<?php if (($key !== $penult_item_key) || $show_last) : ?>
					<span class="divider">
						<?php echo $separator; ?>
					</span>
				<?php endif; ?>
				<meta itemprop="position" content="<?php echo $key + 1; ?>" />
			</li>
		<?php elseif ($show_last) : ?>
			<?php // Render last item if reqd. ?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="active">
				<span itemprop="name">
					<?php echo $item->name; ?>
				</span>
				<meta itemprop="position" content="<?php echo $key + 1; ?>" />
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
