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

$app = JFactory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('jquery.framework');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$function         = $app->input->getCmd('function', 'jSelectAssociation');
$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$canManageCheckin = JFactory::getUser()->authorise('core.manage', 'com_checkin');
$colSpan          = 4;

$iconStates = array(
	-2 => 'icon-trash',
	0  => 'icon-unpublish',
	1  => 'icon-publish',
	2  => 'icon-archive',
);

JFactory::getDocument()->addScriptOptions('assosiations-modal', ['func' => $function]);
JHtml::_('script', 'com_associations/admin-associations-modal.min.js', false, true);
?>
<form action="<?php echo JRoute::_('index.php?option=com_associations&view=associations&layout=modal&tmpl=component&function='
. $function . '&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="col-md-2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="col-md-10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<?php if (empty($this->items)) : ?>
		<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
	<?php else : ?>
		<table class="table" id="associationsList">
			<thead>
				<tr>
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<th style="width:1%" class="center nowrap">
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
						<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
					</th>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<th style="width:10%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_ASSOCIATIONS_HEADING_MENUTYPE', 'menutype_title', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<?php if (!empty($this->typeSupports['acl'])) : ?>
						<th style="width:5%" class="nowrap d-none d-sm-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); $colSpan++; ?>
						</th>
					<?php endif; ?>
					<th style="width:1%" class="nowrap d-none d-sm-table-cell">
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
				$canEdit    = AssociationsHelper::allowEdit($this->extensionName, $this->typeName, $item->id);
				$canCheckin = $canManageCheckin || AssociationsHelper::canCheckinItem($this->extensionName, $this->typeName, $item->id);
				$isCheckout = AssociationsHelper::isCheckoutItem($this->extensionName, $this->typeName, $item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<?php if (!empty($this->typeSupports['state'])) : ?>
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
						</td>
					<?php endif; ?>
					<td class="nowrap has-context">
						<?php if (isset($item->level)) : ?>
							<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
						<?php endif; ?>
						<?php if (($canEdit && !$isCheckout) || ($canEdit && $canCheckin && $isCheckout)) : ?>
							<a class="select-link" href="javascript:void(0);" data-id="<?php echo $item->id; ?>">
							<?php echo $this->escape($item->title); ?></a>
						<?php elseif ($canEdit && $isCheckout) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'associations.'); ?>
							<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
							<?php echo $this->escape($item->title); ?></span>
						<?php else : ?>
							<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
							<?php echo $this->escape($item->title); ?></span>
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
						<?php if (true || $item->association) : ?>
							<?php echo AssociationsHelper::getAssociationHtmlList($this->extensionName, $this->typeName, (int) $item->id, $item->language, false, false); ?>
						<?php endif; ?>
					</td>
					<?php if (!empty($this->typeFields['menutype'])) : ?>
						<td class="small">
							<?php echo $this->escape($item->menutype_title); ?>
						</td>
					<?php endif; ?>
					<?php if (!empty($this->typeSupports['acl'])) : ?>
						<td class="small d-none d-sm-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
					<?php endif; ?>
					<td class="d-none d-sm-table-cell">
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="forcedItemType" value="<?php echo $app->input->get('forcedItemType', '', 'string'); ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
