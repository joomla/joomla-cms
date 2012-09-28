<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>
<h3>
	<?php echo JText::_('COM_FINDER_STATISTICS_TITLE') ?>
</h3>

<div class="row-fluid">
	<div class="span6">
		<p class="tab-description"><?php echo JText::sprintf('COM_FINDER_STATISTICS_STATS_DESCRIPTION', number_format($this->data->term_count), number_format($this->data->link_count), number_format($this->data->taxonomy_node_count), number_format($this->data->taxonomy_branch_count)); ?></p>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="center">
						<?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_HEADING');?>
					</th>
					<th class="center">
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
					<td>
						<span class="badge badge-info"><?php echo number_format($type->link_count);?></span>
					</td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->link_count); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
