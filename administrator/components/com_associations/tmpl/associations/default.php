<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('behavior.multiselect');

$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$canManageCheckin = Factory::getUser()->authorise('core.manage', 'com_checkin');
$colSpan          = 5;

$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

Text::script('COM_ASSOCIATIONS_PURGE_CONFIRM_PROMPT', true);
HTMLHelper::_('script', 'com_associations/admin-associations-default.min.js', false, true);
?>
<form action="<?php echo Route::_('index.php?option=com_associations&view=associations'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="associationsList">
					<thead>
						<tr>
							<?php if (!empty($this->typeSupports['state'])) : ?>
								<th scope="col" style="width:1%" class="text-center nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<th scope="col" class="nowrap">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:15%" class="nowrap">
								<?php echo Text::_('JGRID_HEADING_LANGUAGE'); ?>
							</th>
							<th scope="col" style="width:5%" class="nowrap">
								<?php echo Text::_('COM_ASSOCIATIONS_HEADING_ASSOCIATION'); ?>
							</th>
							<th scope="col" style="width:15%" class="nowrap">
								<?php echo Text::_('COM_ASSOCIATIONS_HEADING_NO_ASSOCIATION'); ?>
							</th>
							<?php if (!empty($this->typeFields['menutype'])) : ?>
								<th scope="col" style="width:10%" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<?php if (!empty($this->typeFields['access'])) : ?>
								<th scope="col" style="width:5%" class="nowrap d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<th scope="col" style="width:1%" class="nowrap d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$canCheckin = true;
						$canEdit    = AssociationsHelper::allowEdit($this->extensionName, $this->typeName, $item->id);
						$canCheckin = $canManageCheckin || AssociationsHelper::canCheckinItem($this->extensionName, $this->typeName, $item->id);
						$isCheckout = AssociationsHelper::isCheckoutItem($this->extensionName, $this->typeName, $item->id);
					?>
						<tr class="row<?php echo $i % 2; ?>">
							<?php if (!empty($this->typeSupports['state'])) : ?>
								<td class="text-center">
									<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>"></span>
								</td>
							<?php endif; ?>
							<th scope="row" class="nowrap has-context">
								<?php if (isset($item->level)) : ?>
									<?php echo LayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<?php endif; ?>
								<?php if ($canCheckin && $isCheckout) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'associations.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<?php $editIcon = $isCheckout ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
									<a class="hasTooltip" href="<?php echo Route::_($this->editUri . '&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
										<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<?php if (!empty($this->typeFields['alias'])) : ?>
									<span class="small">
										<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
								<?php endif; ?>
								<?php if (!empty($this->typeFields['catid'])) : ?>
									<div class="small">
										<?php echo Text::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								<?php endif; ?>
							</th>
							<td class="small">
								<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
							</td>
							<td>
								<?php echo AssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, !$isCheckout, false); ?>
							</td>
							<td>
								<?php echo AssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, !$isCheckout, true); ?>
							</td>
							<?php if (!empty($this->typeFields['menutype'])) : ?>
								<td class="small">
									<?php echo $this->escape($item->menutype_title); ?>
								</td>
							<?php endif; ?>
							<?php if (!empty($this->typeFields['access'])) : ?>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->access_level); ?>
								</td>
							<?php endif; ?>
							<td class="d-none d-md-table-cell text-center">
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
