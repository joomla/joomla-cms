<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$clientId  = (int) $this->state->get('client_id', 0);
$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.ordering');

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_modules&task=modules.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	JHtml::_('draggablelist.draggable');
}
$colSpan = $clientId === 1 ? 8 : 10;
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->total > 0) : ?>
			<table class="table table-striped" id="moduleList">
				<thead>
					<tr>
						<th style="width:1%" class="nowrap text-center d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th style="width:1%" class="nowrap text-center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th style="width:1%" class="nowrap text-center" style="min-width:85px">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
						</th>
						<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
						</th>
						<?php if ($clientId === 0) : ?>
						<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_PAGES', 'pages', $listDirn, $listOrder); ?>
						</th>
						<?php endif; ?>
						<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'ag.title', $listDirn, $listOrder); ?>
						</th>
						<?php if (($clientId === 0) && (JLanguageMultilang::isEnabled())) : ?>
						<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.title', $listDirn, $listOrder); ?>
						</th>
						<?php elseif ($clientId === 1 && JModuleHelper::isAdminMultilang()) : ?>
						<th width="10%" class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
						</th>
						<?php endif; ?>
						<th style="width:5%" class="nowrap text-center d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo $colSpan; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create',     'com_modules');
					$canEdit    = $user->authorise('core.edit',		  'com_modules.module.' . $item->id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_modules.module.' . $item->id) && $canCheckin;
				?>
					<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->position ?: 'none'; ?>">
						<td class="order nowrap text-center d-none d-md-table-cell">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass; ?>">
								<span class="icon-menu"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
							<?php endif; ?>
						</td>
						<td class="text-center">
							<?php if ($item->enabled > 0) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td class="text-center">
							<div class="btn-group">
							<?php // Check if extension is enabled ?>
							<?php if ($item->enabled > 0) : ?>
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'modules.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							<?php else : ?>
								<?php // Extension is not enabled, show a message that indicates this. ?>
								<button class="btn btn-xs btn-secondary hasTooltip" title="<?php echo JText::_('COM_MODULES_MSG_MANAGE_EXTENSION_DISABLED'); ?>">
									<span class="icon-ban-circle" aria-hidden="true"></span>
								</button>
							<?php endif; ?>
							</div>
						</td>
						<td class="has-context">
							<div>
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'modules.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
									<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id=' . (int) $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
										<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>

								<?php if (!empty($item->note)) : ?>
									<div class="small">
										<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
									</div>
								<?php endif; ?>
							</div>
						</td>
						<td class="d-none d-md-table-cell text-center">
							<?php if ($item->position) : ?>
								<span class="badge badge-info">
									<?php echo $item->position; ?>
								</span>
							<?php else : ?>
								<span class="badge badge-secondary">
									<?php echo JText::_('JNONE'); ?>
								</span>
							<?php endif; ?>
						</td>
						<td class="small d-none d-md-table-cell text-center">
							<?php echo $item->name; ?>
						</td>
						<?php if ($clientId === 0) : ?>
						<td class="small d-none d-md-table-cell text-center">
							<?php echo $item->pages; ?>
						</td>
						<?php endif; ?>
						<td class="small d-none d-md-table-cell text-center">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<?php if (($clientId === 0) && (JLanguageMultilang::isEnabled())) : ?>
						<td class="small d-none d-md-table-cell text-center">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<?php elseif ($clientId === 1 && JModuleHelper::isAdminMultilang()) : ?>
							<td class="small d-none d-md-table-cell">
								<?php if ($item->language == ''):?>
									<?php echo JText::_('JUNDEFINED'); ?>
								<?php elseif ($item->language == '*'):?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $this->escape($item->language); ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="d-none d-md-table-cell text-center">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php // Load the batch processing form. ?>
		<?php if ($user->authorise('core.create', 'com_modules')
			&& $user->authorise('core.edit', 'com_modules')
			&& $user->authorise('core.edit.state', 'com_modules')) : ?>
			<?php echo JHtml::_(
				'bootstrap.renderModal',
				'collapseModal',
				array(
					'title'  => JText::_('COM_MODULES_BATCH_OPTIONS'),
					'footer' => $this->loadTemplate('batch_footer'),
				),
				$this->loadTemplate('batch_body')
			); ?>
		<?php endif; ?>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
