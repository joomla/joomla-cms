<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<?php
	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	JHtml::_('user.fx.slider');

	// Find the correct parent group for the filter list
	$acl = &JFactory::getACL();
	$parentId = $acl->get_group_id('USERS');
?>
@todo Decide on ACL - if a user has access to the user manager, should they have a limit on the people they can create vs the overhead of adding rules (examine the most common use cases)<br/>
@todo Add toolbar action to resend activation notice or reset password<br />
@todo Add toolbar action for configuration popup (take out of Global Configuration)

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->state->get('search');?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_type').value='0';this.form.getElementById('filter_logged').value='0';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
			</td>
			<td nowrap="nowrap">
				<select name="filter_logged_in" class="inputbox" size="1" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $this->get('f_logged_in'), 'value', 'text', $this->state->get('logged_in')); ?>
				</select>
				<select name="filter_enabled" class="inputbox" size="1" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $this->get('f_enabled'), 'value', 'text', $this->state->get('enabled')); ?>
				</select>
				<select name="filter_activated" class="inputbox" size="1" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $this->get('f_activated'), 'value', 'text', $this->state->get('activated')); ?>
				</select>
				<select name="filter_group_id" class="inputbox" size="1" onchange="document.adminForm.submit();">
					<option value="0"><?php echo JText::_('Select Group'); ?></option>
					<?php echo JHtml::_('user.groups', $this->state->get('group_id'), $parentId);  ?>
				</select>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th width="2%" class="title">
					<?php echo JText::_('NUM'); ?>
				</th>
				<th width="3%" class="title">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',   'Name', 'a.name', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
					(<?php echo JHtml::_('grid.sort',   'Username', 'a.username', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>)
				</th>
				<th width="5%" class="title" nowrap="nowrap">
					<?php echo JText::_('Logged In'); ?>
				</th>
				<th width="5%" class="title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',   'Enabled', 'a.block', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
				<th width="5%" class="title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',   'Activated', 'a.activation', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
				<th width="15%" class="title">
					<?php echo JHtml::_('grid.sort',   'Group(s)', 'gm.group_id', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
				<th width="15%" class="title">
					<?php echo JHtml::_('grid.sort',   'E-Mail', 'a.email', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
				<th width="10%" class="title">
					<?php echo JHtml::_('grid.sort',   'Last Visit', 'a.lastvisitDate', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
				<th width="1%" class="title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',   'ID', 'a.id', $this->state->get('orderDirn'), $this->state->get('orderCol')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			for ($i=0, $n=count($this->items); $i < $n; $i++) :
				$row 	=& $this->items[$i];

				$img 	= $row->block ? 'publish_x.png' : 'tick.png';
				$task 	= $row->block ? 'user.unblock' : 'user.block';
				$alt 	= $row->block ? JText::_('Enabled') : JText::_('Blocked');
			?>
			<tr class="row<?php echo $i%2; ?>">
				<td>
					<?php echo $i+1+$this->pagination->limitstart;?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $row->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.$row->id); ?>">
						<?php echo $row->name; ?></a>
					(<?php echo $row->username; ?>)
					<span class="hasTip" title="Add preview of related contact information here">@todo</span>
				</td>
				<td align="center">
					<?php echo $row->loggedin ? '<img src="images/tick.png" width="16" height="16" border="0" alt="" />': ''; ?>
				</td>
				<td align="center">
					<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
						<img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a>
				</td>
				<td align="center">
					<?php echo $row->activation ? '' : '<img src="images/tick.png" width="16" height="16" border="0" alt="" />'; ?>
				</td>
				<td>
					<?php
					foreach (explode("\n", $row->groups) AS $group) :
						echo JText::_($group).'<br />';
					endforeach;
					?>
				</td>
				<td align="center">
					<a href="mailto:<?php echo $row->email; ?>">
						<?php echo $row->email; ?></a>
				</td>
				<td nowrap="nowrap" align="center">
				<?php
					if ($row->lastvisitDate == "0000-00-00 00:00:00") :
						echo JText::_('Never');
					else :
						echo $row->lastvisitDate; //= JHtml::_('date',  $row->lastvisitDate, JText::_('DATE_FORMAT_LC4'));
					endif;
				 ?>
				</td>
				<td align="center">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php endfor; ?>
		</tbody>
	</table>

	<fieldset>
		<legend id="v_toggle"><?php echo JText::_('Click Here for Batch Processing Options'); ?></legend>
		<div id="v_slider">
			@todo Batch move/append/remove users amongst groups<br />
		</div>
	</fieldset>


	<input type="hidden" name="task" value="" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->orderCol; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->orderDirn; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
</form>