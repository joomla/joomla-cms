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
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table" id="requestList">
				<caption id="captionTable" class="sr-only">
					<?php echo Text::_('COM_PRIVACY_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
				</caption>
				<thead>
					<tr>
						<th scope="col" style="width:5%" class="text-center">
							<?php echo Text::_('COM_PRIVACY_HEADING_ACTIONS'); ?>
						</th>
						<th scope="col" style="width:5%" class="text-center">
							<?php echo Text::_('JSTATUS'); ?>
						</th>
						<th scope="col">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUEST_TYPE', 'a.request_type', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:15%">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUESTED_AT', 'a.requested_at', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:1%" >
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php
						$itemRequestedAt = new Date($item->requested_at);
						?>
						<tr>
							<td class="center">
								<div class="btn-group">
									<?php if ($item->status == 1 && $item->request_type === 'export') : ?>
										<a class="btn tbody-icon" href="<?php echo Route::_('index.php?option=com_privacy&task=request.export&format=xml&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?>"><span class="icon-download" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?></span></a>
										<?php if ($this->sendMailEnabled) : ?>
											<a class="btn tbody-icon" href="<?php echo Route::_('index.php?option=com_privacy&task=request.emailexport&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?>"><span class="icon-mail" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?></span></a>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ($item->status == 1 && $item->request_type === 'remove') : ?>
										<a class="btn tbody-icon" href="<?php echo Route::_('index.php?option=com_privacy&task=request.remove&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?>"><span class="icon-delete" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?></span></a>
									<?php endif; ?>
								</div>
							</td>
							<td class="text-center">
								<?php echo HTMLHelper::_('privacy.statusLabel', $item->status); ?>
							</td>
							<td scope="row">
								<?php if ($item->status == 1 && $urgentRequestDate >= $itemRequestedAt) : ?>
									<span class="float-right badge badge-danger"><?php echo Text::_('COM_PRIVACY_BADGE_URGENT_REQUEST'); ?></span>
								<?php endif; ?>
								<a href="<?php echo Route::_('index.php?option=com_privacy&view=request&id=' . (int) $item->id); ?>" title="<?php echo Text::_('COM_PRIVACY_ACTION_VIEW'); ?>">
									<?php echo PunycodeHelper::emailToUTF8($this->escape($item->email)); ?>
								</a>
							</td>
							<td>
								<?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $item->request_type); ?>
							</td>
							<td>
								<?php echo HTMLHelper::_('date.relative', $itemRequestedAt, null, $now); ?>
								<div class="small">
									<?php echo HTMLHelper::_('date', $item->requested_at, Text::_('DATE_FORMAT_LC6')); ?>
								</div>
							</td>
							<td>
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
