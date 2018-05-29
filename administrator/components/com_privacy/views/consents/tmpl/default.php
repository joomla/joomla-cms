<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var PrivacyViewConsent $this */

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$now       = JFactory::getDate();

?>
<form action="<?php echo JRoute::_('index.php?option=com_privacy&view=consents'); ?>" method="post" name="adminForm" id="adminForm">
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
				<?php echo JText::_('COM_PRIVACY_MSG_CONSENT_NO_CONSENTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="consentList">
				<thead>
					<tr>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_USERNAME', 'u.username', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JText::_('COM_PRIVACY_HEADING_CONSENTS_SUBJECT'); ?>
						</th>
						<th class="nowrap">
							<?php echo JText::_('COM_PRIVACY_HEADING_CONSENTS_BODY'); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_PRIVACY_HEADING_CONSENTS_CREATED', 'a.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="5">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<?php echo $item->username; ?>
							</td>
							<td>
								<?php echo $item->subject; ?>
							</td>
							<td>
								<?php echo $item->body; ?>
							</td>
							<td class="break-word">
								<span class="hasTooltip" title="<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC6')); ?>">
									<?php echo JHtml::_('date.relative', new JDate($item->created), null, $now); ?>
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
