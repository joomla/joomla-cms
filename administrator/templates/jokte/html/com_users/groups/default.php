<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$user = JFactory::getUser();
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
JText::script('COM_USERS_GROUPS_CONFIRM_DELETE');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'groups.delete')
		{
			var f = document.adminForm;
			var cb='';
<?php foreach ($this->items as $i=>$item):?>
<?php if ($item->user_count > 0):?>
			cb = f['cb'+<?php echo $i;?>];
			if (cb && cb.checked) {
				if (confirm(Joomla.JText._('COM_USERS_GROUPS_CONFIRM_DELETE'))) {
					Joomla.submitform(task);
				}
				return;
			}
<?php endif;?>
<?php endforeach;?>
		}
		Joomla.submitform(task);
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=groups');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_USERS_SEARCH_GROUPS_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_IN_GROUPS'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</fieldset>
	<?php if( $this->pagination->total > 0 ): ?><div id="pagination-top"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JText::_('COM_USERS_HEADING_GROUP_TITLE'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_USERS_HEADING_USERS_IN_GROUP'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
			</tr>
		</thead>
		<?php if( $this->pagination->total >= 10 ): ?>
		<tfoot>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JText::_('COM_USERS_HEADING_GROUP_TITLE'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_USERS_HEADING_USERS_IN_GROUP'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
			</tr>
		</tfoot>
		<?php endif; ?>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCreate	= $user->authorise('core.create',		'com_users');
			$canEdit	= $user->authorise('core.edit',			'com_users');
			// If this group is super admin and this user is not super admin, $canEdit is false
			if (!$user->authorise('core.admin') && (JAccess::checkGroup($item->id, 'core.admin'))) {
				$canEdit = false;
			}
			$canChange	= $user->authorise('core.edit.state',	'com_users');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php if ($canEdit) : ?>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level) ?>
					<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=group.edit&id='.$item->id);?>">
						<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<?php if (JDEBUG) : ?>
						<div class="fltrt"><div class="button2-left smallsub"><div class="blank"><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuggroup&group_id='.(int) $item->id);?>">
						<?php echo JText::_('COM_USERS_DEBUG_GROUP');?></a></div></div></div>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->user_count ? $item->user_count : ''; ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if( $this->pagination->total > 0): ?><div id="pagination-bottom"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
