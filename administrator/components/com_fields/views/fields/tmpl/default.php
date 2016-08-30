<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$context   = $this->escape($this->state->get('filter.context'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_fields&task=fields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'fieldList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_fields&view=fields'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="filter-bar" class="js-stools-container-bar pull-left">
			<div class="btn-group pull-left">
				<?php $fieldSets = $this->filterForm->getFieldsets('custom'); ?>
				<?php foreach ($fieldSets as $name => $fieldSet) :
					<?php foreach ($this->filterForm->getFieldset($name) as $field) : ?>
						<?php echo $field->input; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>&nbsp;
		</div>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="fieldList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDS_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_FIELDS_FIELD_GROUP_LABEL', 'category_title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="10">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php $ordering   = ($listOrder == 'a.ordering'); ?>
						<?php $canEdit    = $user->authorise('core.edit', $context . '.field.' . $item->id); ?>
						<?php $canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0; ?>
						<?php $canEditOwn = $user->authorise('core.edit.own', $context . '.field.' . $item->id) && $item->created_user_id == $userId; ?>
						<?php $canChange  = $user->authorise('core.edit.state', $context . '.field.' . $item->id) && $canCheckin; ?>
						<tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
							<td class="order nowrap center hidden-phone">
								<?php $iconClass = ''; ?>
								<?php if (!$canChange) : ?>
									<?php $iconClass = ' inactive'; ?>
								<?php elseif (!$saveOrder) : ?>
									<?php $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED'); ?>
								<?php endif; ?>
								<span class="sortable-handler<?php echo $iconClass; ?>">
									<span class="icon-menu"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" />
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'fields.', $canChange); ?>
							</td>
							<td>
								<div class="pull-left break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'fields.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_fields&task=field.edit&id=' . $item->id . '&context=' . $context); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
									<span class="small break-word">
										<?php if (empty($item->note)) : ?>
											<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
										<?php else : ?>
											<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
										<?php endif; ?>
									</span>
									<div class="small">
										<?php $category = JCategories::getInstance(str_replace('com_', '', $this->component)); ?>
										<?php if ($category) : ?>
											<?php $buffer = JText::_('JCATEGORY') . ': '; ?>
											<?php $cats = array_filter(explode(',', $item->assigned_cat_ids)); ?>
											<?php if (empty($cats)) : ?>
												<?php $buffer .= JText::_('JALL'); ?>
											<?php endif; ?>
											<?php foreach ($cats as $cat) : ?>
												<?php if (empty($cat)) : ?>
													<?php continue; ?>
												<?php endif; ?>
												<?php $c = $category->get($cat); ?>
												<?php if (!$c || $c->id == 'root') :  ?>
													<?php continue; ?>
												<?php endif; ?>
												<?php $buffer .= ' ' . $c->title . ','; ?>
											<?php endforeach; ?>
											<?php echo trim($buffer, ','); ?>
										<?php endif; ?>
									</div>
								</div>
							</td>
							<td class="small">
								<?php $label = 'COM_FIELDS_TYPE_' . strtoupper($item->type); ?>
								<?php if (!JFactory::getLanguage()->hasKey($label)) :  ?>
									<?php $label = JString::ucfirst($item->type); ?>
								<?php echo $this->escape(JText::_($label)); ?>
							</td>
							<td>
								<?php echo $this->escape($item->category_title); ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="small nowrap hidden-phone">
								<?php if ($item->language == '*') : ?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else : ?>
									<?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<span><?php echo (int) $item->id; ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php //Load the batch processing form. ?>
			<?php if ($user->authorise('core.create', $context)
				&& $user->authorise('core.edit', $context)
				&& $user->authorise('core.edit.state', $context)) : ?>
				<?php echo JHtml::_(
						'bootstrap.renderModal',
						'collapseModal',
						array(
							'title' => JText::_('COM_FIELDS_VIEW_FIELDS_BATCH_OPTIONS'),
							'footer' => $this->loadTemplate('batch_footer')
						),
						$this->loadTemplate('batch_body')
					); ?>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" id="context" name="context" value="<?php echo $context; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
