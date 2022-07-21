<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$totalRequests  = 0;
$activeRequests = 0;

?>
<div class="row-striped">
	<?php if (count($list)) : ?>
		<div class="row-fluid">
			<div class="span5"><strong><?php echo JText::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_TYPE'); ?></strong></div>
			<div class="span5"><strong><?php echo JText::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_STATUS'); ?></strong></div>
			<div class="span2"><strong><?php echo JText::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_COUNT'); ?></strong></div>
		</div>
		<?php foreach ($list as $row) : ?>
			<div class="row-fluid">
				<div class="span5">
					<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&view=requests&filter[request_type]=' . $row->request_type . '&filter[status]=' . $row->status); ?>" data-original-title="<?php echo JText::_('COM_PRIVACY_DASHBOARD_VIEW_REQUESTS'); ?>">
						<strong><?php echo JText::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $row->request_type); ?></strong>
					</a>
				</div>
				<div class="span5"><?php echo JHtml::_('PrivacyHtml.helper.statusLabel', $row->status); ?></div>
				<div class="span2"><span class="badge badge-info"><?php echo $row->count; ?></span></div>
			</div>
			<?php if (in_array($row->status, array(0, 1))) : ?>
				<?php $activeRequests += $row->count; ?>
			<?php endif; ?>
			<?php $totalRequests += $row->count; ?>
		<?php endforeach; ?>
		<div class="row-fluid">
			<div class="span5"><?php echo JText::plural('COM_PRIVACY_DASHBOARD_BADGE_TOTAL_REQUESTS', $totalRequests); ?></div>
			<div class="span7"><?php echo JText::plural('COM_PRIVACY_DASHBOARD_BADGE_ACTIVE_REQUESTS', $activeRequests); ?></div>
		</div>
	<?php else : ?>
		<div class="row-fluid">
			<div class="span12">
				<div class="alert"><?php echo JText::_('COM_PRIVACY_DASHBOARD_NO_REQUESTS'); ?></div>
			</div>
		</div>
	<?php endif; ?>
</div>
