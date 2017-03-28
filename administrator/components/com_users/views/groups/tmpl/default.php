<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user        = JFactory::getUser();
$listOrder   = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
$debugGroups = $this->state->get('params')->get('debugGroups', 1);

JText::script('COM_USERS_GROUPS_CONFIRM_DELETE');

JFactory::getDocument()->addScriptDeclaration('
		Joomla.submitbutton = function(task) {
			if (task == "groups.delete") {
				var i, cids = document.getElementsByName("cid[]");
				for (i = 0; i < cids.length; i++) {
					if (cids[i].checked && cids[i].parentNode.getAttribute("data-usercount") != 0) {
						if (confirm(Joomla.JText._("COM_USERS_GROUPS_CONFIRM_DELETE"))) {
							Joomla.submitform(task);
						}
						return false;
					}
				}
			}

			Joomla.submitform(task);
			return false;
		};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=groups'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="groupList">
				<thead>
					<tr>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_GROUP_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<i class="icon-publish hasTooltip" title="<?php echo JText::_('COM_USERS_COUNT_ENABLED_USERS'); ?>"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_USERS_COUNT_ENABLED_USERS'); ?></span>
						</th>
						<th width="1%" class="nowrap center">
							<i class="icon-unpublish hasTooltip" title="<?php echo JText::_('COM_USERS_COUNT_DISABLED_USERS'); ?>"></i>
							<span class="hidden-phone"><?php echo JText::_('COM_USERS_COUNT_DISABLED_USERS'); ?></span>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="5">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canCreate = $user->authorise('core.create', 'com_users');
					$canEdit   = $user->authorise('core.edit', 'com_users');

					// If this group is super admin and this user is not super admin, $canEdit is false
					if (!$user->authorise('core.admin') && (JAccess::checkGroup($item->id, 'core.admin')))
					{
						$canEdit = false;
					}
					$canChange = $user->authorise('core.edit.state', 'com_users');
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center" data-usercount="<?php echo $item->user_count; ?>">
							<?php if ($canEdit) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level + 1)); ?>
							<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_users&task=group.edit&id=' . $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
							<?php if ($debugGroups) : ?>
								<div class="small"><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuggroup&group_id=' . (int) $item->id); ?>">
								<?php echo JText::_('COM_USERS_DEBUG_GROUP'); ?></a></div>
							<?php endif; ?>
						</td>
						<td class="center btns">
							<a class="badge <?php if ($item->count_enabled > 0) echo 'badge-success'; ?>" href="<?php echo JRoute::_('index.php?option=com_users&view=users&filter[group_id]=' . (int) $item->id . '&filter[state]=0'); ?>">
								<?php echo $item->count_enabled; ?></a>
						</td>
						<td class="center btns">
							<a class="badge <?php if ($item->count_disabled > 0) echo 'badge-important'; ?>" href="<?php echo JRoute::_('index.php?option=com_users&view=users&filter[group_id]=' . (int) $item->id . '&filter[state]=1'); ?>">
								<?php echo $item->count_disabled; ?></a>
						</td>
						<td class="hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
