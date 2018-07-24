<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\Inflector;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$extension = $this->escape($this->state->get('filter.extension'));
$parts     = explode('.', $extension);
$component = $parts[0];
$section   = null;
$mode      = false;
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

if ($section === 'categories')
{
	$mode = true;
	$section = $component;
	$component = 'com_categories';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tags&task=tags.saveOrderAjax';
	JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_tags&view=tags'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="categoryList">
				<thead>
					<tr>
						<th width="1%" class="nowrap hidden-phone center">
							<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>

						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<span class="icon-publish hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?>"><span class="element-invisible"><?php echo JText::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?></span></span>
							</th>
							<?php $columns++; ?>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<span class="icon-unpublish hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?>"><span class="element-invisible"><?php echo JText::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?></span></span>
							</th>
							<?php $columns++; ?>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<span class="icon-archive hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?>"><span class="element-invisible"><?php echo JText::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?></span></span>
							</th>
							<?php $columns++; ?>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
							<th width="1%" class="nowrap center hidden-phone">
								<span class="icon-trash hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?>"><span class="element-invisible"><?php echo JText::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?></span></span>
							</th>
							<?php $columns++; ?>
						<?php endif; ?>

						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
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
				<tbody>
				<?php
				foreach ($this->items as $i => $item) :
					$orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
					$canCreate  = $user->authorise('core.create',     'com_tags');
					$canEdit    = $user->authorise('core.edit',       'com_tags');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_tags') && $canCheckin;

					// Get the parents of item for sorting
					if ($item->level > 1)
					{
						$parentsStr = '';
						$_currentParentId = $item->parent_id;
						$parentsStr = ' ' . $_currentParentId;
						for ($j = 0; $j < $item->level; $j++)
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
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id; ?>" parents="<?php echo $parentsStr; ?>" level="<?php echo $item->level; ?>">
							<td class="order nowrap center hidden-phone">
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
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'tags.', $canChange); ?>
									<?php // Create dropdown items and render the dropdown list.
									if ($canChange)
									{
										JHtml::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'tags');
										JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'tags');
										echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									}
									?>
								</div>
							</td>
							<td>
								<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tags.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_tags&task=tag.edit&id=' . $item->id); ?>">
										<?php echo $this->escape($item->title); ?></a>
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
							<td class="center btns hidden-phone">
								<a class="badge <?php if ($item->count_published > 0) echo 'badge-success'; ?>" title="<?php echo JText::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=1'); ?>">
									<?php echo $item->count_published; ?></a>
							</td>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
							<td class="center btns hidden-phone">
								<a class="badge <?php if ($item->count_unpublished > 0) echo 'badge-important'; ?>" title="<?php echo JText::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=0'); ?>">
									<?php echo $item->count_unpublished; ?></a>
							</td>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
							<td class="center btns hidden-phone">
								<a class="badge <?php if ($item->count_archived > 0) echo 'badge-info'; ?>" title="<?php echo JText::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=2'); ?>">
									<?php echo $item->count_archived; ?></a>
							</td>
						<?php endif; ?>
						<?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
							<td class="center btns hidden-phone">
								<a class="badge <?php if ($item->count_trashed > 0) echo 'badge-inverse'; ?>" title="<?php echo JText::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?>" href="<?php echo JRoute::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=-2'); ?>">
									<?php echo $item->count_trashed; ?></a>
							</td>
						<?php endif; ?>



						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_title); ?>
						</td>
						<td class="small nowrap hidden-phone">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="hidden-phone">
							<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
								<?php echo (int) $item->id; ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php // Load the batch processing form if user is allowed ?>
			<?php if ($user->authorise('core.create', 'com_tags')
				&& $user->authorise('core.edit', 'com_tags')
				&& $user->authorise('core.edit.state', 'com_tags')) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => JText::_('COM_TAGS_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer'),
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
