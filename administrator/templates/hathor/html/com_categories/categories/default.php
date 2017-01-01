<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$extension = $this->escape($this->state->get('filter.extension'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.lft');
$canOrder  = $user->authorise('core.edit.state', $extension);
$saveOrder = ($listOrder == 'a.lft' && $listDirn == 'asc');
$jinput    = JFactory::getApplication()->input;
$component = $jinput->get('extension');
?>

<div class="categories">
	<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories'); ?>" method="post" name="adminForm" id="adminForm">
		<?php if (!empty($this->sidebar)) : ?>
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
		<?php endif; ?>
		<div id="j-main-container"<?php echo !empty($this->sidebar) ? ' class="span10"' : ''; ?>>
			<fieldset id="filter-bar">
				<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
				<div class="filter-search">
					<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />

					<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>

				<div class="filter-select">
					<label class="selectlabel" for="filter_level"><?php echo JText::_('JOPTION_SELECT_MAX_LEVELS'); ?></label>
					<select name="filter_level" id="filter_level">
						<option value=""><?php echo JText::_('JOPTION_SELECT_MAX_LEVELS'); ?></option>
						<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level')); ?>
					</select>

					<label class="selectlabel" for="filter_published"><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></label>
					<select name="filter_published" id="filter_published">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
					</select>

					<label class="selectlabel" for="filter_access"><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></label>
					<select name="filter_access" id="filter_access">
						<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
					</select>

					<label class="selectlabel" for="filter_language"><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></label>
					<select name="filter_language" id="filter_language">
						<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
					</select>

					<label class="selectlabel" for="filter_tag"><?php echo JText::_('JOPTION_SELECT_TAG'); ?></label>
					<select name="filter_tag" id="filter_tag">
						<option value=""><?php echo JText::_('JOPTION_SELECT_TAG'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tag')); ?>
					</select>

					<button type="submit" id="filter-go">
						<?php echo JText::_('JSUBMIT'); ?></button>
				</div>
			</fieldset>
			<div class="clr"></div>

			<table class="adminlist">
				<thead>
					<tr>
						<th class="checkmark-col">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap state-col">
							<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap ordering-col">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>
							<?php if ($canOrder && $saveOrder) : ?>
								<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'categories.saveorder'); ?>
							<?php endif; ?>
						</th>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<i class="icon-publish hasTooltip" title="<?php echo JText::_('COM_CATEGORY_COUNT_PUBLISHED_ITEMS'); ?>"></i>
							</th>
						<?php endif;?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<i class="icon-unpublish hasTooltip" title="<?php echo JText::_('COM_CATEGORY_COUNT_UNPUBLISHED_ITEMS'); ?>"></i>
							</th>
						<?php endif;?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<i class="icon-archive hasTooltip" title="<?php echo JText::_('COM_CATEGORY_COUNT_ARCHIVED_ITEMS'); ?>"></i>
							</th>
						<?php endif;?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<i class="icon-trash hasTooltip" title="<?php echo JText::_('COM_CATEGORY_COUNT_TRASHED_ITEMS'); ?>"></i>
							</th>
						<?php endif;?>
						<th class="access-col">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<?php if ($this->assoc) : ?>
							<th width="5%">
								<?php echo JHtml::_('grid.sort', 'COM_CATEGORY_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						<th class="language-col">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
						</th>
						<th class="nowrap id-col">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php
						$orderkey = array_search($item->id, $this->ordering[$item->parent_id]);
						$canEdit = $user->authorise('core.edit', $extension . '.category.' . $item->id);
						$canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own', $extension . '.category.' . $item->id) && $item->created_user_id == $userId;
						$canChange = $user->authorise('core.edit.state', $extension . '.category.' . $item->id) && $canCheckin;
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1) ?>
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->id . '&extension=' . $extension); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
								<p class="smallsub" title="<?php echo $this->escape($item->path); ?>">
									<?php echo str_repeat('<span class="gtr">|&mdash;</span>', $item->level - 1) ?>
									<?php if (empty($item->note)) : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									<?php else : ?>
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
									<?php endif; ?></p>
							</td>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
							</td>
							<td class="order">
								<?php if ($canChange) : ?>
									<?php if ($saveOrder) : ?>
										<span><?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'categories.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'categories.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php endif; ?>
									<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
									<input type="text" name="order[]" value="<?php echo $orderkey + 1; ?>" <?php echo $disabled ?> class="text-area-order" title="<?php echo $item->title; ?> order" />
								<?php else : ?>
									<?php echo $orderkey + 1; ?>
								<?php endif; ?>
							</td>
							<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
								<td class="center">
									<a title="<?php echo JText::_('COM_CATEGORY_COUNT_PUBLISHED_ITEMS');?>" href="<?php echo JRoute::_('index.php?option=' . $component . '&filter[category_id]=' . (int) $item->id . '&filter[published]=1' . '&filter[level]=' . (int) $item->level);?>">
										<?php echo $item->count_published; ?></a>
								</td>
							<?php endif;?>
							<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
								<td class="center">
									<a title="<?php echo JText::_('COM_CATEGORY_COUNT_UNPUBLISHED_ITEMS');?>" href="<?php echo JRoute::_('index.php?option=' . $component . '&filter[category_id]=' . (int) $item->id . '&filter[published]=0' . '&filter[level]=' . (int) $item->level);?>">
										<?php echo $item->count_unpublished; ?></a>
								</td>
							<?php endif;?>
							<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
								<td class="center">
									<a title="<?php echo JText::_('COM_CATEGORY_COUNT_ARCHIVED_ITEMS');?>" href="<?php echo JRoute::_('index.php?option=' . $component . '&filter[category_id]=' . (int) $item->id . '&filter[published]=2' . '&filter[level]=' . (int) $item->level);?>">
										<?php echo $item->count_archived; ?></a>
								</td>
							<?php endif;?>
							<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
								<td class="center">
									<a title="<?php echo JText::_('COM_CATEGORY_COUNT_TRASHED_ITEMS');?>" href="<?php echo JRoute::_('index.php?option=' . $component . '&filter[category_id]=' . (int) $item->id . '&filter[published]=-2' . '&filter[level]=' . (int) $item->level);?>">
										<?php echo $item->count_trashed; ?></a>
								</td>
							<?php endif;?>
							<td class="center">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<?php if ($this->assoc) : ?>
								<td class="center">
									<?php if ($item->association): ?>
										<?php echo JHtml::_('CategoriesAdministrator.association', $item->id, $extension); ?>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<td class="center nowrap">
								<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
							</td>
							<td class="center">
						<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
							<?php echo (int) $item->id; ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php echo $this->pagination->getListFooter(); ?>
			<div class="clr"></div>

			<?php //Load the batch processing form. ?>
			<?php if ($user->authorise('core.create', $extension)
				&& $user->authorise('core.edit', $extension)
				&& $user->authorise('core.edit.state', $extension)) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => JText::_('COM_CATEGORIES_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer')
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>

			<input type="hidden" name="extension" value="<?php echo $extension; ?>" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
