<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal', 'a.modal');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

	Joomla.submitbutton = function(pressbutton) 
	{ 
		if(pressbutton == 'users.sync')
		{
			jQuery('#sync-progress').show();
			doSyncUsers(0, 0, 0);
		}
		else
		{
			Joomla.submitform(pressbutton);
		}
	}

	function doSyncUsers(startId, endId, lastId)
	{
		try
		{
			startId = parseInt(startId);
			endId = parseInt(endId);
			lastId = parseInt(lastId);
		}
		catch(e){}
		
		jQuery.ajax({
			url: '<?php echo JRoute::_('index.php?option=com_cjforum&task=users.execute&format=json', false);?>',
			dataType: 'json',
			data: {
				'startId': startId,
				'endId': endId,
				'lastId': lastId
			},
			beforeSend: function( xhr ) {
				jQuery('#sync-progress').show();
			}
		}).done(function(r){
			if(r.success)
			{
				if(r.data == -1)
				{
					doSyncUsers(endId + 1, endId + 250, lastId);
				}
				else
				{
					doSyncUsers(r.data.min_id, 250, r.data.max_id);
				}
			}
			else
			{
				if(endId < lastId)
				{
					// there is something wrong or data not found. continue with next
					doSyncUsers(endId + 1, endId + 250, lastId);
				}
				else
				{
					jQuery('#sync-progress').hide();
					alert(r.message);
				}
			}
			
			percent = lastId > 0 ? Math.round((endId - 250) * 100 / lastId) : 0;
			jQuery('#sync-progress').find('.bar').attr('style', 'width: '+percent+'%').find('.pct').text(percent+'%');
		})
		.fail(function(data){
			alert(data.message);
		});
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_cjforum&view=users');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
		<div class="progress progress-striped" id="sync-progress" style="display: none;">
			<div class="bar" style="width: 0%;"><span class="pct"></span></div>
		</div>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th class="left">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_RANK', 'cju.rank', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_TOPICS', 'cju.topics', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_REPLIES', 'cju.replies', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_POINTS', 'cju.points', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JText::_('COM_CJFORUM_HEADING_GROUPS'); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'COM_CJFORUM_HEADING_REGISTRATION_DATE', 'a.registerDate', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canEdit   = $this->canDo->get('core.edit');
			$canChange = $loggeduser->authorise('core.edit.state',	'com_cjforum');

			// If this group is super admin and this user is not super admin, $canEdit is false
			if ((!$loggeduser->authorise('core.admin')) && JAccess::check($item->id, 'core.admin'))
			{
				$canEdit   = false;
				$canChange = false;
			}
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php if ($canEdit) : ?>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=profile.edit&id='.(int) $item->id); ?>" title="<?php echo JText::sprintf('COM_CJFORUM_EDIT_USER', $this->escape($item->name)); ?>">
							<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
					<div>
						<?php echo JHtml::_('users.filterNotes', $item->note_count, $item->id); ?>
						<?php echo JHtml::_('users.notes', $item->note_count, $item->id); ?>
						<?php if ($item->requireReset == '1') : ?>
						<span class="label label-warning"><?php echo JText::_('COM_CJFORUM_PASSWORD_RESET_REQUIRED'); ?></span>
						<?php endif; ?>
					</div>
				</td>
				<td class="center">
					<?php echo $this->escape($item->username); ?>
				</td>
				<td class="center">
					<?php echo CjForumApi::getUserRankImage($item->id, 'default'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->topics;?>
				</td>
				<td class="center">
					<?php echo (int) $item->replies;?>
				</td>
				<td class="center">
					<?php echo (int) $item->points;?>
				</td>
				<td class="center">
					<?php if ($canChange) : ?>
						<?php
						$self = $loggeduser->id == $item->id;
						echo JHtml::_('jgrid.state', JHtmlUsers::blockStates($self), $item->block, $i, 'users.', !$self);
						?>
					<?php else : ?>
						<?php echo JText::_($item->block ? 'JNO' : 'JYES'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php
					$activated = empty( $item->activation) ? 0 : 1;
					echo JHtml::_('jgrid.state', JHtmlUsers::activateStates(), $activated, $i, 'users.', (boolean) $activated);
					?>
				</td>
				<td class="center">
					<?php if (substr_count($item->group_names, "\n") > 1) : ?>
						<span class="hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_CJFORUM_HEADING_GROUPS'), nl2br($item->group_names), 0); ?>"><?php echo JText::_('COM_CJFORUM_USERS_MULTIPLE_GROUPS'); ?></span>
					<?php else : ?>
						<?php echo nl2br($item->group_names); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php if ($item->lastvisitDate != '0000-00-00 00:00:00'):?>
						<?php echo JHtml::_('date', $item->lastvisitDate, 'Y-m-d H:i:s'); ?>
					<?php else:?>
						<?php echo JText::_('JNEVER'); ?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo JHtml::_('date', $item->registerDate, 'Y-m-d H:i:s'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php //Load the batch processing form. ?>
	<?php echo $this->loadTemplate('batch'); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
