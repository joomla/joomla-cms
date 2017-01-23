<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$app        = JFactory::getApplication();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$ordering   = ($listOrder == 'a.lft');
$saveOrder  = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$menuType   = (string) $app->getUserState('com_menus.items.menutype', '', 'string');

if ($saveOrder && $menuType)
{
	$saveOrderingUrl = 'index.php?option=com_menus&task=items.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	JHtml::_('draggablelist.draggable');
}

$assoc   = JLanguageAssociations::isEnabled() && $this->state->get('filter.client_id') == 0;
$colSpan = $assoc ? 10 : 9;

if ($menuType == '')
{
	$colSpan--;
}
?>
<?php // Set up the filter bar. ?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=items'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'menutype'))); ?>
		<?php if ($this->total > 0) : ?>
			<table class="table table-striped" id="itemList">
				<thead>
					<tr>
						<?php if ($menuType) : ?>
							<th width="1%" class="nowrap text-center hidden-sm-down">
								<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th>
						<?php endif; ?>
						<th width="1%" class="nowrap text-center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap text-center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MENUS_HEADING_MENU', 'menutype_title', $listDirn, $listOrder); ?>
						</th>
						<?php if ($this->state->get('filter.client_id') == 0): ?>
						<th width="10%" class="text-center nowrap hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', 'COM_MENUS_HEADING_HOME', 'a.home', $listDirn, $listOrder); ?>
						</th>
						<?php endif; ?>
						<th width="10%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<?php if ($assoc) : ?>
							<th width="10%" class="nowrap hidden-sm-down text-center">
								<?php echo JHtml::_('searchtools.sort', 'COM_MENUS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						<th width="10%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-sm-down text-center">
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
				<?php

				foreach ($this->items as $i => $item) :
					$orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
					$canCreate  = $user->authorise('core.create',     'com_menus.menu.' . $item->menutype_id);
					$canEdit    = $user->authorise('core.edit',       'com_menus.menu.' . $item->menutype_id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_menus.menu.' . $item->menutype_id) && $canCheckin;

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
					<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id; ?>" parents="<?php echo $parentsStr; ?>" level="<?php echo $item->level; ?>">
						<?php if ($menuType) : ?>
							<td class="order nowrap text-center hidden-sm-down">
								<?php
								$iconClass = '';

								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
									<span class="icon-menu"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="text-center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="text-center">
							<?php echo JHtml::_('MenusHtml.Menus.state', $item->published, $i, $canChange, 'cb'); ?>
						</td>
						<td>
							<?php $prefix = JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
							<?php echo $prefix; ?>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit && !$item->protected) : ?>
								<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
							<span class="small">
							<?php if ($item->type != 'url') : ?>
								<?php if (empty($item->note)) : ?>
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								<?php else : ?>
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
								<?php endif; ?>
							<?php elseif ($item->type == 'url' && $item->note) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
							<?php endif; ?>
							</span>
							<?php echo JHtml::_('MenusHtml.Menus.visibility', $item->params); ?>
							<div title="<?php echo $this->escape($item->path); ?>">
								<?php echo $prefix; ?>
								<span class="small"  title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
									<?php echo $this->escape($item->item_type); ?></span>
							</div>
						</td>
						<td class="small hidden-sm-down text-center">
							<?php echo $this->escape($item->menutype_title ?: ucwords($item->menutype)); ?>
						</td>
						<?php if ($this->state->get('filter.client_id') == 0) : ?>
						<td class="text-center hidden-sm-down">
							<?php if ($item->type == 'component') : ?>
								<?php if ($item->language == '*' || $item->home == '0') : ?>
									<?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'items.', ($item->language != '*' || !$item->home) && $canChange && !$item->protected); ?>
								<?php elseif ($canChange) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_menus&task=items.unsetDefault&cid[]=' . $item->id . '&' . JSession::getFormToken() . '=1'); ?>">
										<?php if ($item->language_image) : ?>
											<?php echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => JText::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title)), true); ?>
										<?php else : ?>
											<span class="label" title="<?php echo JText::sprintf('COM_MENUS_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->language_sef; ?></span>
										<?php endif; ?>
									</a>
								<?php else : ?>
									<?php if ($item->language_image) : ?>
										<?php echo JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
									<?php else : ?>
										<span class="label" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
									<?php endif; ?>
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>
						<td class="small hidden-sm-down text-center">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<?php if ($assoc) : ?>
							<td class="small hidden-sm-down text-center">
								<?php if ($item->association) : ?>
									<?php echo JHtml::_('MenusHtml.Menus.association', $item->id); ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="small hidden-sm-down text-center">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="hidden-sm-down text-center">
							<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
								<?php echo (int) $item->id; ?>
							</span>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php // Load the batch processing form if user is allowed ?>
			<?php if ($user->authorise('core.create', 'com_menus') || $user->authorise('core.edit', 'com_menus')) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title' => JText::_('COM_MENUS_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer')
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
