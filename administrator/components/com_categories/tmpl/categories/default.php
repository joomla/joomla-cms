<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\Inflector;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');


$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$extension = $this->escape($this->state->get('filter.extension'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$parts     = explode('.', $extension, 2);
$component = $parts[0];
$section   = null;
$columns   = 7;

if (count($parts) > 1)
{
	$section = $parts[1];

	$inflector = Inflector::getInstance();

	if (!$inflector->isPlural($section))
	{
		$section = $inflector->toPlural($section);
	}
}

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_categories&task=categories.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	JHtml::_('draggablelist.draggable');
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) { ?>
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<?php } ?>
		<div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table table-striped" id="categoryList">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th style="width:1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) :
									$columns++; ?>
									<th style="width:3%" class="nowrap text-center d-none d-md-table-cell">
                                        <span class="icon-publish hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_CATEGORY_COUNT_PUBLISHED_ITEMS'); ?>">
                                            <span class="sr-only"><?php echo JText::_('COM_CATEGORY_COUNT_PUBLISHED_ITEMS'); ?></span>
                                        </span>
									</th>
								<?php endif; ?>
								<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) :
									$columns++; ?>
									<th style="width:3%" class="nowrap text-center d-none d-md-table-cell">
                                        <span class="icon-unpublish hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_CATEGORY_COUNT_UNPUBLISHED_ITEMS'); ?>">
                                            <span class="sr-only"><?php echo JText::_('COM_CATEGORY_COUNT_UNPUBLISHED_ITEMS'); ?></span>
                                        </span>
									</th>
								<?php endif; ?>
								<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) :
									$columns++; ?>
									<th style="width:3%" class="nowrap text-center d-none d-md-table-cell">
                                        <span class="icon-archive hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_CATEGORY_COUNT_ARCHIVED_ITEMS'); ?>">
                                            <span class="sr-only"><?php echo JText::_('COM_CATEGORY_COUNT_ARCHIVED_ITEMS'); ?></span>
                                        </span>
									</th>
								<?php endif; ?>
								<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) :
									$columns++; ?>
									<th style="width:3%" class="nowrap text-center d-none d-md-table-cell">
                                        <span class="icon-trash hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_CATEGORY_COUNT_TRASHED_ITEMS'); ?>">
                                            <span class="sr-only"><?php echo JText::_('COM_CATEGORY_COUNT_TRASHED_ITEMS'); ?></span>
                                        </span>
									</th>
								<?php endif; ?>
								<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
								</th>
								<?php if ($this->assoc) :
									$columns++; ?>
									<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo JHtml::_('searchtools.sort', 'COM_CATEGORY_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<?php if (JLanguageMultilang::isEnabled()) : ?>
									<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
										<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>	
								<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="<?php echo $columns; ?>">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php
								$canEdit    = $user->authorise('core.edit',       $extension . '.category.' . $item->id);
								$canCheckin = $user->authorise('core.admin',      'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canEditOwn = $user->authorise('core.edit.own',   $extension . '.category.' . $item->id) && $item->created_user_id == $userId;
								$canChange  = $user->authorise('core.edit.state', $extension . '.category.' . $item->id) && $canCheckin;

								// Get the parents of item for sorting
								if ($item->level > 1)
								{
									$parentsStr = '';
									$_currentParentId = $item->parent_id;
									$parentsStr = ' ' . $_currentParentId;
									for ($i2 = 0; $i2 < $item->level; $i2++)
									{
										foreach ($this->ordering as $k => $v)
										{
											$v = implode('-', $v);
											$v = '-' . $v . '-';
											if (strpos($v, '-' . $_currentParentId . '-') !== false)
											{
												$parentsStr .= ' ' . $k;
												$_currentParentId = $k;
												break;
											}
										}
									}
								}
								else
								{
									$parentsStr = '';
								}
								?>
								<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">
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
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-menu"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->lft; ?>">
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo JHtml::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
										</div>
									</td>
									<td>
										<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
										<?php if ($item->checked_out) : ?>
											<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit || $canEditOwn) : ?>
											<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
											<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->id . '&extension=' . $extension); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
												<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->title); ?>
										<?php endif; ?>
										<span class="small" title="<?php echo $this->escape($item->path); ?>">
											<?php if (empty($item->note)) : ?>
												<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
											<?php else : ?>
												<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
											<?php endif; ?>
										</span>
									</td>
									<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
										<td class="text-center btns d-none d-md-table-cell">
											<a class="badge <?php echo ($item->count_published > 0) ? 'badge-success' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_CATEGORY_COUNT_PUBLISHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=1' . '&filter[level]=1'); ?>">
												<?php echo $item->count_published; ?></a>
										</td>
									<?php endif; ?>
									<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
										<td class="text-center btns d-none d-md-table-cell">
											<a class="badge <?php echo ($item->count_unpublished > 0) ? 'badge-danger' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_CATEGORY_COUNT_UNPUBLISHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=0' . '&filter[level]=1'); ?>">
												<?php echo $item->count_unpublished; ?></a>
										</td>
									<?php endif; ?>
									<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
										<td class="text-center btns d-none d-md-table-cell">
											<a class="badge <?php echo ($item->count_archived > 0) ? 'badge-info' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_CATEGORY_COUNT_ARCHIVED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=2' . '&filter[level]=1'); ?>">
												<?php echo $item->count_archived; ?></a>
										</td>
									<?php endif; ?>
									<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
										<td class="text-center btns d-none d-md-table-cell">
											<a class="badge <?php echo ($item->count_trashed > 0) ? 'badge-inverse' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_CATEGORY_COUNT_TRASHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=-2' . '&filter[level]=1'); ?>">
												<?php echo $item->count_trashed; ?></a>
										</td>
									<?php endif; ?>

									<td class="small d-none d-md-table-cell text-center">
										<?php echo $this->escape($item->access_level); ?>
									</td>
									<?php if ($this->assoc) : ?>
										<td class="d-none d-md-table-cell text-center">
											<?php if ($item->association) : ?>
												<?php echo JHtml::_('CategoriesAdministrator.association', $item->id, $extension); ?>
											<?php endif; ?>
										</td>
									<?php endif; ?>
									<?php if (JLanguageMultilang::isEnabled()) : ?>
										<td class="small nowrap d-none d-md-table-cell text-center">
											<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
										</td>
									<?php endif; ?>
									<td class="d-none d-md-table-cell text-center">
										<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
											<?php echo (int) $item->id; ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', $extension)
						&& $user->authorise('core.edit', $extension)
						&& $user->authorise('core.edit.state', $extension)) : ?>
						<?php echo JHtml::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            array(
                                'title'  => JText::_('COM_CATEGORIES_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer'),
                            ),
                            $this->loadTemplate('batch_body')
                        ); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="extension" value="<?php echo $extension; ?>">
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
