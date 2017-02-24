<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the default behaviours for plural form
JHtml::_('formbehavior.plural');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_redirect&view=links'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-warning alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="text-center nowrap">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="text-center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap title">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_OLD_URL', 'a.old_url', $listDirn, $listOrder); ?>
						</th>
						<th width="30%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_NEW_URL', 'a.new_url', $listDirn, $listOrder); ?>
						</th>
						<th width="30%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_REFERRER', 'a.referer', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_HITS', 'a.hits', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_STATUS_CODE', 'a.header', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
				<?php foreach ($this->items as $i => $item) :
					$canEdit   = $user->authorise('core.edit',       'com_redirect');
					$canChange = $user->authorise('core.edit.state', 'com_redirect');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="text-center">
							<div class="btn-group">
								<?php echo JHtml::_('redirect.published', $item->published, $i); ?>
							</div>
						</td>
						<td class="break-word">
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_redirect&task=link.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->old_url); ?>">
									<?php echo $this->escape(str_replace(JUri::root(), '', rawurldecode($item->old_url))); ?></a>
							<?php else : ?>
									<?php echo $this->escape(str_replace(JUri::root(), '', rawurldecode($item->old_url))); ?>
							<?php endif; ?>
						</td>
						<td class="small break-word">
							<?php echo $this->escape(rawurldecode($item->new_url)); ?>
						</td>
						<td class="small break-word hidden-sm-down">
							<?php echo $this->escape($item->referer); ?>
						</td>
						<td class="small hidden-sm-down">
							<?php echo JHtml::_('date', $item->created_date, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="hidden-sm-down">
							<?php echo (int) $item->hits; ?>
						</td>
						<td class="hidden-sm-down">
							<?php echo (int) $item->header; ?>
						</td>
						<td class="hidden-sm-down">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if (!empty($this->items)) : ?>
			<?php echo $this->loadTemplate('addform'); ?>
		<?php endif; ?>
		<?php // Load the batch processing form if user is allowed ?>
			<?php if ($user->authorise('core.create', 'com_redirect')
				&& $user->authorise('core.edit', 'com_redirect')
				&& $user->authorise('core.edit.state', 'com_redirect')) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title' => JText::_('COM_REDIRECT_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer')
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
