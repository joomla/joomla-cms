<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;

/** @var \Joomla\Component\Privacy\Administrator\View\Request\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_privacy&view=request&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row mt-3">
		<div class="col-12 col-md-4 mb-3">
			<div class="card">
				<h3 class="card-header"><?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_INFORMATION'); ?></h3>
				<div class="card-body">
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
			</div>
		</div>
		<div class="col-12 col-md-8 mb-3">
			<div class="card">
				<h3 class="card-header"><?php echo Text::_('COM_PRIVACY_HEADING_ACTION_LOG'); ?></h3>
				<div class="card-body">
					<?php if (empty($this->actionlogs)) : ?>
						<div class="alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
							<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
						</div>
					<?php else : ?>
						<table class="table">
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
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
