<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>
<h3>
	<?php echo JText::_('COM_FINDER_STATISTICS_TITLE'); ?>
</h3>

<div class="row-fluid">
	<div class="span12">
		<p class="tab-description"><?php echo JText::sprintf('COM_FINDER_STATISTICS_STATS_DESCRIPTION', number_format($this->data->term_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->link_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_node_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_branch_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'))); ?></p>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th>
						<?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_HEADING'); ?>
					</th>
					<th>
						<?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_COUNT'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->data->type_list as $type) : ?>
				<tr>
					<td>
						<?php
						$lang_key    = 'PLG_FINDER_STATISTICS_' . str_replace(' ', '_', $type->type_title);
						$lang_string = JText::_($lang_key);
						echo ($lang_string == $lang_key) ? $type->type_title : $lang_string;
						?>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($type->link_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')); ?></span>
					</td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td>
						<strong><?php echo JText::_('COM_FINDER_STATISTICS_LINK_TYPE_TOTAL'); ?></strong>
					</td>
					<td>
						<span class="badge badge-info"><?php echo number_format($this->data->link_count, 0, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR')); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
