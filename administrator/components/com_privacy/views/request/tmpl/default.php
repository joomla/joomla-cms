<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var PrivacyViewRequest $this */

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$js = <<< JS
Joomla.submitbutton = function(task) {
	if (task === 'request.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
		Joomla.submitform(task, document.getElementById('item-form'));
	}
};
JS;

JFactory::getDocument()->addScriptDeclaration($js);
?>

<form action="<?php echo JRoute::_('index.php?option=com_privacy&view=request&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('COM_PRIVACY_HEADING_REQUEST_INFORMATION'); ?></h3>
			<dl class="dl-horizontal">
				<dt><?php echo JText::_('JGLOBAL_EMAIL'); ?>:</dt>
				<dd><?php echo $this->item->email; ?></dd>

				<dt><?php echo JText::_('JSTATUS'); ?>:</dt>
				<dd><?php echo JHtml::_('PrivacyHtml.helper.statusLabel', $this->item->status); ?></dd>

				<dt><?php echo JText::_('COM_PRIVACY_FIELD_REQUEST_TYPE_LABEL'); ?>:</dt>
				<dd><?php echo JText::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $this->item->request_type); ?></dd>

				<dt><?php echo JText::_('COM_PRIVACY_FIELD_REQUESTED_AT_LABEL'); ?>:</dt>
				<dd><?php echo JHtml::_('date', $this->item->requested_at, JText::_('DATE_FORMAT_LC6')); ?></dd>
			</dl>
		</div>
		<div class="span6">
			<h3><?php echo JText::_('COM_PRIVACY_HEADING_ACTION_LOG'); ?></h3>
			<?php if (empty($this->actionlogs)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped table-hover">
					<thead>
						<th>
							<?php echo JText::_('COM_ACTIONLOGS_ACTION'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_ACTIONLOGS_DATE'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_ACTIONLOGS_NAME'); ?>
						</th>
					</thead>
					<tbody>
						<?php foreach ($this->actionlogs as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td>
									<?php echo ActionlogsHelper::getHumanReadableLogMessage($item); ?>
								</td>
								<td>
									<?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC6')); ?>
								</td>
								<td>
									<?php echo $item->name; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif;?>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
