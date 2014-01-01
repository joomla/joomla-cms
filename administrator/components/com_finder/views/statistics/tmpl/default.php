<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_finder');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="<?php echo JFactory::getApplication()->input->get('refresh', 0, 'bool') ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JTOOLBAR_CLOSE');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::_('COM_FINDER_STATISTICS_TITLE') ?>
		</div>
	</fieldset>

	<p class="tab-description"><?php echo JText::sprintf('COM_FINDER_STATISTICS_STATS_DESCRIPTION', number_format($this->data->term_count), number_format($this->data->link_count), number_format($this->data->taxonomy_node_count), number_format($this->data->taxonomy_branch_count)); ?></p>
	<table class="adminlist">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_HEADING');?>
				</th>
				<th>
					<?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_COUNT');?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->data->type_list as $type) :?>
			<tr>
				<td>
					<?php echo $type->type_title;?>
				</td>
				<td align="right">
					<?php echo number_format($type->link_count);?>
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td>
					<strong><?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_TOTAL'); ?></strong>
				</td>
				<td align="right">
					<strong><?php echo number_format($this->data->link_count); ?></strong>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="clr"></div>
	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
