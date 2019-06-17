<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var PrivacyViewRequest $this */

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$js = <<< JS
Joomla.submitbutton = function(task) {
	if (task === 'request.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
		Joomla.submitform(task, document.getElementById('item-form'));
	}
};
JS;

Factory::getDocument()->addScriptDeclaration($js);
?>

<form action="<?php echo Route::_('index.php?option=com_privacy&view=request&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_INFORMATION'); ?></h3>
			<dl class="dl-horizontal">
				<dt><?php echo Text::_('JGLOBAL_EMAIL'); ?>:</dt>
				<dd><?php echo $this->item->email; ?></dd>

				<dt><?php echo Text::_('JSTATUS'); ?>:</dt>
				<dd><?php echo HTMLHelper::_('privacy.statusLabel', $this->item->status); ?></dd>

				<dt><?php echo Text::_('COM_PRIVACY_FIELD_REQUEST_TYPE_LABEL'); ?>:</dt>
				<dd><?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $this->item->request_type); ?></dd>

				<dt><?php echo Text::_('COM_PRIVACY_FIELD_REQUESTED_AT_LABEL'); ?>:</dt>
				<dd><?php echo HTMLHelper::_('date', $this->item->requested_at, Text::_('DATE_FORMAT_LC6')); ?></dd>
			</dl>
		</div>
		<div class="span6">
			<h3><?php echo Text::_('COM_PRIVACY_HEADING_ACTION_LOG'); ?></h3>
			<?php if (empty($this->actionlogs)) : ?>
				<div class="alert alert-info">
					<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped table-hover">
					<thead>
						<th>
							<?php echo Text::_('COM_ACTIONLOGS_ACTION'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_ACTIONLOGS_DATE'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_ACTIONLOGS_NAME'); ?>
						</th>
					</thead>
					<tbody>
						<?php foreach ($this->actionlogs as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td>
									<?php echo ActionlogsHelper::getHumanReadableLogMessage($item); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC6')); ?>
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
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
