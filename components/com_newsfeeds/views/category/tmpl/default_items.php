<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
	function tableOrdering(order, dir, task) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit(task);
}
</script>

<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()); ?>" method="post" name="adminForm">

		<div class="limit-box">
			<?php echo JText::_('DISPLAY_NUM') .'&nbsp;'; ?>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>

		<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
</form>

	<table class="jlist-table">
		<?php if ($this->params->def('show_headings', 1)) : ?>
		<thead>
			<tr>

				<th class="item-num">
					<?php echo JText::_('Num'); ?>
				</th>

				<th class="item-title">
					<?php echo JHtml::_('grid.sort',  'News Feed', 'title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>

			</tr>
		</thead>
		<?php endif; ?>

		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="<?php echo $i % 2 ? 'odd' : 'even';?>">

					<?php if ($this->params->get('show_name')) : ?>
						<td class="item-title">
							<?php echo JText::_('Feed Name'); ?>
						</td>
					<?php endif; ?>

					<?php  if ($this->params->get('show_articles')) : ?>
						<td class="item-num-art">
							<?php echo JText::_('Num Articles'); ?>
						</td>
					<?php  endif; ?>
				</tr>

				<tr>
					<td class="item-title">
						<a href="<?php echo $item->link; ?>">
							<?php echo $item->name; ?></a>
					</td>

					<?php  if ($this->params->get('show_articles')) : ?>
						<td class="item-num-art">
							<?php echo $item->numarticles; ?>
						</td>
					<?php  endif; ?>

				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="jpagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<div class="jpag-results">
			<?php echo $this->pagination->getPagesCounter(); ?>
		</div>
	</div>
