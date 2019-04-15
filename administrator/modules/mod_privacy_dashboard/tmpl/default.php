<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.framework');

$totalRequests  = 0;
$activeRequests = 0;

?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
	<caption class="sr-only"><?php echo $module->title; ?></caption>
	<thead>
		<tr>
			<th scope="col" style="width:40%"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_TYPE'); ?></th>
			<th scope="col" style="width:30%"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_STATUS'); ?></th>
			<th scope="col" style="width:30%"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_COUNT'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if (count($list)) : ?>
		<?php foreach ($list as $row) : ?>
		<tr>
			<td>
				<a href="<?php echo Route::_('index.php?option=com_privacy&view=requests&filter[request_type]=' . $row->request_type . '&filter[status]=' . $row->status); ?>" data-original-title="<?php echo Text::_('COM_PRIVACY_DASHBOARD_VIEW_REQUESTS'); ?>">
					<strong><?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $row->request_type); ?></strong>
				</a>
			</td>
			<td><?php echo HTMLHelper::_('privacy.statusLabel', $row->status); ?></td>
			<td><span class="badge badge-info"><?php echo $row->count; ?></span></td>
			<?php if (in_array($row->status, array(0, 1))) : ?>
				<?php $activeRequests += $row->count; ?>
			<?php endif; ?>
			<?php $totalRequests += $row->count; ?>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_TOTAL_REQUESTS', $totalRequests); ?></td>
			<td><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_ACTIVE_REQUESTS', $activeRequests); ?></td>
			<td></td>
		</tr>
		<?php else : ?>
		<tr>
			<td colspan="3">
				<?php echo Text::_('COM_PRIVACY_DASHBOARD_NO_REQUESTS'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
