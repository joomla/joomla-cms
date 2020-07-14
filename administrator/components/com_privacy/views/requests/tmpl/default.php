<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var PrivacyViewRequests $this */

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$now       = JFactory::getDate();

$urgentRequestDate= clone $now;
$urgentRequestDate->sub(new DateInterval('P' . $this->urgentRequestAge . 'D'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_privacy&view=requests'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_PRIVACY_MSG_REQUESTS_NO_REQUESTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="requestList">
				<thead>
					<tr>
						<th width="5%" class="nowrap center">
							<?php echo JText::_('COM_PRIVACY_HEADING_ACTIONS'); ?>
						</th>
						<th width="5%" class="nowrap center">
							<?php echo JText::_('JSTATUS'); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUEST_TYPE', 'a.request_type', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_PRIVACY_HEADING_REQUESTED_AT', 'a.requested_at', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
						$itemRequestedAt = new JDate($item->requested_at);
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<div class="btn-group">
									<?php if ($item->status == 1 && $item->request_type === 'export') : ?>
										<a class="btn btn-micro hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&task=request.export&format=xml&id=' . (int) $item->id); ?>" title="<?php echo JText::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?>"><span class="icon-download" aria-hidden="true"></span><span class="element-invisible"><?php echo JText::_('COM_PRIVACY_ACTION_EXPORT_DATA'); ?></span></a>
										<?php if ($this->sendMailEnabled) : ?>
											<a class="btn btn-micro hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&task=request.emailexport&id=' . (int) $item->id); ?>" title="<?php echo JText::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?>"><span class="icon-mail" aria-hidden="true"></span><span class="element-invisible"><?php echo JText::_('COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA'); ?></span></a>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ($item->status == 1 && $item->request_type === 'remove') : ?>
										<a class="btn btn-micro hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&task=request.remove&id=' . (int) $item->id . '&' . JFactory::getSession()->getFormToken() . '=1'); ?>" title="<?php echo JText::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?>"><span class="icon-delete" aria-hidden="true"></span><span class="element-invisible"><?php echo JText::_('COM_PRIVACY_ACTION_DELETE_DATA'); ?></span></a>
									<?php endif; ?>
								</div>
							</td>
							<td class="center">
								<?php echo JHtml::_('PrivacyHtml.helper.statusLabel', $item->status); ?>
							</td>
							<td>
								<?php if ($item->status == 1 && $urgentRequestDate >= $itemRequestedAt) : ?>
									<span class="pull-right label label-important"><?php echo JText::_('COM_PRIVACY_BADGE_URGENT_REQUEST'); ?></span>
								<?php endif; ?>
								<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&view=request&id=' . (int) $item->id); ?>" title="<?php echo JText::_('COM_PRIVACY_ACTION_VIEW'); ?>">
									<?php echo JStringPunycode::emailToUTF8($this->escape($item->email)); ?>
								</a>
							</td>
							<td class="break-word">
								<?php echo JText::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $item->request_type); ?>
							</td>
							<td class="break-word">
								<span class="hasTooltip" title="<?php echo JHtml::_('date', $item->requested_at, JText::_('DATE_FORMAT_LC6')); ?>">
									<?php echo JHtml::_('date.relative', $itemRequestedAt, null, $now); ?>
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
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
