<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

JHtml::_('jquery.framework');
JHtml::_('behavior.multiselect');

$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$canManageCheckin = JFactory::getUser()->authorise('core.manage', 'com_checkin');
$colSpan          = 5;

$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

JText::script('COM_ASSOCIATIONS_PURGE_CONFIRM_PROMPT', true);
JHtml::_('script', 'com_associations/admin-associations-default.min.js', false, true);
?>
<form action="<?php echo JRoute::_('index.php?option=com_associations&view=associations'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table table-striped" id="associationsList">
					<thead>
						<tr>
							<?php if (!empty($this->typeSupports['state'])) : ?>
								<th style="width:1%" class="text-center nowrap">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'state', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<th class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
							</th>
							<th style="width:15%" class="nowrap">
								<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
							</th>
							<th style="width:5%" class="nowrap">
								<?php echo JText::_('COM_ASSOCIATIONS_HEADING_ASSOCIATION'); ?>
							</th>
							<th style="width:15%" class="nowrap">
								<?php echo JText::_('COM_ASSOCIATIONS_HEADING_NO_ASSOCIATION'); ?>
							</th>
							<?php if (!empty($this->typeFields['menutype'])) : ?>
								<th style="width:10%" class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<?php if (!empty($this->typeFields['access'])) : ?>
								<th style="width:5%" class="nowrap d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
								</th>
							<?php endif; ?>
							<th style="width:1%" class="nowrap d-none d-md-table-cell text-center">
								<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
							<td class="nowrap has-context">
								<?php if (isset($item->level)) : ?>
									<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
								<?php endif; ?>
								<?php if ($canCheckin && $isCheckout) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'associations.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<?php $editIcon = $isCheckout ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
									<a class="hasTooltip" href="<?php echo JRoute::_($this->editUri . '&id=' . (int) $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
										<?php echo $editIcon; ?><?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
								<?php endif; ?>
								<?php if (!empty($this->typeFields['alias'])) : ?>
									<span class="small">
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
								<?php endif; ?>
								<?php if (!empty($this->typeFields['catid'])) : ?>
									<div class="small">
										<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								<?php endif; ?>
							</td>
							<td class="small">
								<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
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
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
