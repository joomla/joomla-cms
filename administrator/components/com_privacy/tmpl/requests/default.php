<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;

/** @var PrivacyViewRequests $this */

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$now       = Factory::getDate();

$urgentRequestDate = clone $now;
$urgentRequestDate->sub(new DateInterval('P' . $this->urgentRequestAge . 'D'));
?>
<form action="<?php echo Route::_('index.php?option=com_privacy&view=requests'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="requestList">
				<thead>
					<tr>
						<th width="5%" class="nowrap center">
							<?php echo Text::_('COM_PRIVACY_HEADING_ACTIONS'); ?>
						</th>
						<th width="5%" class="nowrap center">
							<?php echo Text::_('JSTATUS'); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUEST_TYPE', 'a.request_type', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUESTED_AT', 'a.requested_at', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php
						$itemRequestedAt = new Date($item->requested_at);
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<div class="btn-group">
									<?php if ($item->status == 1 && $item->request_type === 'export') : ?>
										<a class="btn btn-micro hasTooltip" href="<?php echo Route::_('index.php?option=com_privacy&task=request.export&format=xml&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?>"><span class="icon-download" aria-hidden="true"></span><span class="element-invisible"><?php echo Text::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?></span></a>
										<?php if ($this->sendMailEnabled) : ?>
											<a class="btn btn-micro hasTooltip" href="<?php echo Route::_('index.php?option=com_privacy&task=request.emailexport&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?>"><span class="icon-mail" aria-hidden="true"></span><span class="element-invisible"><?php echo Text::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?></span></a>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ($item->status == 1 && $item->request_type === 'remove') : ?>
										<a class="btn btn-micro hasTooltip" href="<?php echo Route::_('index.php?option=com_privacy&task=request.remove&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?>"><span class="icon-delete" aria-hidden="true"></span><span class="element-invisible"><?php echo Text::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?></span></a>
									<?php endif; ?>
								</div>
							</td>
							<td class="center">
								<?php echo HTMLHelper::_('privacy.statusLabel', $item->status); ?>
							</td>
							<td>
								<?php if ($item->status == 1 && $urgentRequestDate >= $itemRequestedAt) : ?>
									<span class="pull-right badge badge-danger"><?php echo Text::_('COM_PRIVACY_BADGE_URGENT_REQUEST'); ?></span>
								<?php endif; ?>
								<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_privacy&view=request&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_VIEW'); ?>">
									<?php echo PunycodeHelper::emailToUTF8($this->escape($item->email)); ?>
								</a>
							</td>
							<td class="break-word">
								<?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $item->request_type); ?>
							</td>
							<td class="break-word">
								<span class="hasTooltip" title="<?php echo HTMLHelper::_('date', $item->requested_at, Text::_('DATE_FORMAT_LC6')); ?>">
									<?php echo HTMLHelper::_('date.relative', $itemRequestedAt, null, $now); ?>
								</span>
							</td>
							<td class="hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
