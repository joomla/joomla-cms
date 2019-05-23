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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var PrivacyViewConsent $this */

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user       = JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$now        = JFactory::getDate();
$stateIcons = array(-1 => 'trash', 0 => 'archive', 1 => 'publish');
$stateMsgs  = array(
	-1 => Text::_('COM_PRIVACY_CONSENTS_STATE_INVALIDATED'),
	0 => Text::_('COM_PRIVACY_CONSENTS_STATE_OBSOLETE'),
	1 => Text::_('COM_PRIVACY_CONSENTS_STATE_VALID')
);

?>
<form action="<?php echo Route::_('index.php?option=com_privacy&view=consents'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-warning">
				<?php echo Text::_('COM_PRIVACY_MSG_CONSENTS_NO_CONSENTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="consentList">
				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_USERNAME', 'u.username', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_USERID', 'a.user_id', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_CONSENTS_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo Text::_('COM_PRIVACY_HEADING_CONSENTS_BODY'); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PRIVACY_HEADING_CONSENTS_CREATED', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="9">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<span class="icon icon-<?php echo $stateIcons[$item->state]; ?>" title="<?php echo $stateMsgs[$item->state]; ?>"></span>
							</td>
							<td>
								<?php echo $item->username; ?>
							</td>
							<td>
								<?php echo $item->user_id; ?>
							</td>
							<td>
								<?php echo Text::_($item->subject); ?>
							</td>
							<td>
								<?php echo $item->body; ?>
							</td>
							<td class="break-word">
								<span class="hasTooltip" title="<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC6')); ?>">
									<?php echo HTMLHelper::_('date.relative', new Date($item->created), null, $now); ?>
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
