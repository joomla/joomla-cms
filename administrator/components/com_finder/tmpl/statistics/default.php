<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="container-popup">
	<p class="tab-description"><?php echo Text::sprintf('COM_FINDER_STATISTICS_STATS_DESCRIPTION', number_format($this->data->term_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')), number_format($this->data->link_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_node_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')), number_format($this->data->taxonomy_branch_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR'))); ?></p>
	<table class="table table-sm">
		<thead>
			<tr>
				<th scope="col">
					<?php echo Text::_('COM_FINDER_STATISTICS_LINK_TYPE_HEADING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_FINDER_STATISTICS_LINK_TYPE_COUNT'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->data->type_list as $type) : ?>
			<tr>
				<th scope="row">
					<?php
					$lang_key    = 'PLG_FINDER_STATISTICS_' . str_replace(' ', '_', $type->type_title);
					$lang_string = Text::_($lang_key);
					echo $lang_string === $lang_key ? $type->type_title : $lang_string;
					?>
				</th>
				<td>
					<span class="badge badge-info"><?php echo number_format($type->link_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')); ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td>
					<strong><?php echo Text::_('COM_FINDER_STATISTICS_LINK_TYPE_TOTAL'); ?></strong>
				</td>
				<td>
					<span class="badge badge-info"><?php echo number_format($this->data->link_count, 0, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR')); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
