<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$context   = $this->escape($this->state->get('filter.context'));
$component = $this->state->get('filter.component');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

// The category object of the component
$category = JCategories::getInstance(str_replace('com_', '', $component));

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_fields&task=fields.saveOrderAjax&tmpl=component';
	JHtml::_('draggablelist.draggable');
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_fields&view=fields'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<?php } ?>
		<div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'context'))); ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table table-striped" id="fieldList">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th style="width:1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_FIELDS_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_FIELDS_FIELD_GROUP_LABEL', 'g.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<?php if (JLanguageMultilang::isEnabled()) : ?>
									<th style="width:10%" class="nowrap hidden-sm-down text-center">
										<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th style="width:5%" class="nowrap hidden-sm-down text-center">
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
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php $ordering   = ($listOrder == 'a.ordering'); ?>
								<?php $canEdit    = $user->authorise('core.edit', $component . '.field.' . $item->id); ?>
								<?php $canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0; ?>
								<?php $canEditOwn = $user->authorise('core.edit.own', $component . '.field.' . $item->id) && $item->created_user_id == $userId; ?>
								<?php $canChange  = $user->authorise('core.edit.state', $component . '.field.' . $item->id) && $canCheckin; ?>
								<tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
									<td class="order nowrap text-center hidden-sm-down">
										<?php $iconClass = ''; ?>
										<?php if (!$canChange) : ?>
											<?php $iconClass = ' inactive'; ?>
										<?php elseif (!$saveOrder) : ?>
											<?php $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED'); ?>
										<?php endif; ?>
										<span class="sortable-handler<?php echo $iconClass; ?>">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>">
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo JHtml::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo JHtml::_('jgrid.published', $item->state, $i, 'fields.', $canChange, 'cb'); ?>
										</div>
									</td>
									<td>
										<div class="break-word">
											<?php if ($item->checked_out) : ?>
												<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'fields.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit || $canEditOwn) : ?>
												<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
												<a href="<?php echo JRoute::_('index.php?option=com_fields&task=field.edit&id=' . $item->id . '&context=' . $context); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
													<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->title); ?>
											<?php endif; ?>
											<span class="small break-word">
												<?php if (empty($item->note)) : ?>
													<?php echo JText::sprintf('JGLOBAL_LIST_NAME', $this->escape($item->name)); ?>
												<?php else : ?>
													<?php echo JText::sprintf('JGLOBAL_LIST_NAME_NOTE', $this->escape($item->name), $this->escape($item->note)); ?>
												<?php endif; ?>
											</span>
                                            <?php if ($category) : ?>
                                                <div class="small">
                                                    <?php echo JText::_('JCATEGORY') . ': '; ?>
                                                    <?php $categories = FieldsHelper::getAssignedCategoriesTitles($item->id); ?>
                                                    <?php if ($categories) : ?>
                                                        <?php echo implode(', ', $categories); ?>
                                                    <?php else: ?>
                                                        <?php echo JText::_('JALL'); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
										</div>
									</td>
									<td class="small text-center">
										<?php echo $this->escape($item->type); ?>
									</td>
									<td>
										<?php echo $this->escape($item->group_title); ?>
									</td>
									<td class="small hidden-sm-down text-center">
										<?php echo $this->escape($item->access_level); ?>
									</td>
									<?php if (JLanguageMultilang::isEnabled()) : ?>
										<td class="small nowrap hidden-sm-down text-center">
											<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
										</td>
									<?php endif; ?>
									<td class="hidden-sm-down text-center">
										<span><?php echo (int) $item->id; ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php //Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', $component)
						&& $user->authorise('core.edit', $component)
						&& $user->authorise('core.edit.state', $component)) : ?>
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
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
