<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

HTMLHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$context   = $this->escape($this->state->get('filter.context'));
$component = $this->state->get('filter.component');
$section   = $this->state->get('filter.section');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

// The category object of the component
$category = Categories::getInstance(str_replace('com_', '', $component) . '.' . $section);

// If there is no category for the component and section, then check the component only
if (!$category)
{
	$category = Categories::getInstance(str_replace('com_', '', $component));
}

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_fields&task=fields.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>

<form action="<?php echo Route::_('index.php?option=com_fields&view=fields&context=' . $this->state->get('filter.context')); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'context'))); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="fieldList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_FIELDS_FIELDS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_TYPE_LABEL', 'a.type', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_FIELDS_FIELD_GROUP_LABEL', 'g.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<?php if (Multilanguage::isEnabled()) : ?>
									<th scope="col" class="w-10 d-none d-md-table-cell">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:5%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php $ordering   = ($listOrder == 'a.ordering'); ?>
								<?php $canEdit    = $user->authorise('core.edit', $component . '.field.' . $item->id); ?>
								<?php $canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0; ?>
								<?php $canEditOwn = $user->authorise('core.edit.own', $component . '.field.' . $item->id) && $item->created_user_id == $userId; ?>
								<?php $canChange  = $user->authorise('core.edit.state', $component . '.field.' . $item->id) && $canCheckin; ?>
								<tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<?php $iconClass = ''; ?>
										<?php if (!$canChange) : ?>
											<?php $iconClass = ' inactive'; ?>
										<?php elseif (!$saveOrder) : ?>
											<?php $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED'); ?>
										<?php endif; ?>
										<span class="sortable-handler<?php echo $iconClass; ?>">
											<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" class="hidden" name="order[]" size="5" value="<?php echo $item->ordering; ?>">
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'fields.', $canChange, 'cb'); ?>
									</td>
									<th scope="row">
										<div class="break-word">
											<?php if ($item->checked_out) : ?>
												<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'fields.', $canCheckin); ?>
											<?php endif; ?>
											<?php if ($canEdit || $canEditOwn) : ?>
												<a href="<?php echo Route::_('index.php?option=com_fields&task=field.edit&id=' . $item->id . '&context=' . $context); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
													<?php echo $this->escape($item->title); ?></a>
											<?php else : ?>
												<?php echo $this->escape($item->title); ?>
											<?php endif; ?>
											<span class="small break-word">
												<?php if (empty($item->note)) : ?>
													<?php echo Text::sprintf('JGLOBAL_LIST_NAME', $this->escape($item->name)); ?>
												<?php else : ?>
													<?php echo Text::sprintf('JGLOBAL_LIST_NAME_NOTE', $this->escape($item->name), $this->escape($item->note)); ?>
												<?php endif; ?>
											</span>
											<?php if ($category) : ?>
												<div class="small">
													<?php echo Text::_('JCATEGORY') . ': '; ?>
													<?php $categories = FieldsHelper::getAssignedCategoriesTitles($item->id); ?>
													<?php if ($categories) : ?>
														<?php echo implode(', ', $categories); ?>
													<?php else: ?>
														<?php $category_ids = FieldsHelper::getAssignedCategoriesIds($item->id); ?>
														<?php echo (in_array('-1', $category_ids)) ? Text::_('JNONE') : Text::_('JALL'); ?>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									</th>
									<td class="small">
										<?php echo $this->escape($item->type); ?>
									</td>
									<td>
										<?php echo $this->escape($item->group_title); ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $this->escape($item->access_level); ?>
									</td>
									<?php if (Multilanguage::isEnabled()) : ?>
										<td class="small d-none d-md-table-cell">
											<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
										</td>
									<?php endif; ?>
									<td class="d-none d-md-table-cell">
										<span><?php echo (int) $item->id; ?></span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php //Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', $component)
						&& $user->authorise('core.edit', $component)
						&& $user->authorise('core.edit.state', $component)) : ?>
						<?php echo HTMLHelper::_(
								'bootstrap.renderModal',
								'collapseModal',
								array(
									'title' => Text::_('COM_FIELDS_VIEW_FIELDS_BATCH_OPTIONS'),
									'footer' => $this->loadTemplate('batch_footer')
								),
								$this->loadTemplate('batch_body')
							); ?>
					<?php endif; ?>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
