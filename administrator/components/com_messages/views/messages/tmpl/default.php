<?php 
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$user	= &JFactory::getUser();
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm">

<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_('Search'); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->state->get('filter.search');?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
	</td>
</tr>
</table>

<div id="tablecell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_('NUM'); ?>
			</th>
			<th width="20" class="title">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
			</th>
			<th width="50%" class="title">
				<?php echo JHtml::_('grid.sort',   'Subject', 'a.subject', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th width="5%" class="title" align="center">
				<?php echo JHtml::_('grid.sort',   'Read', 'a.state', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th width="25%" class="title">
				<?php echo JHtml::_('grid.sort',   'From', 'a.user_id_from', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th width="15%" class="title" nowrap="nowrap" align="center">
				<?php echo JHtml::_('grid.sort',   'Date', 'a.date_time', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count($this->items); $i < $n; $i++) {
		$row = &$this->items[$i];
		$img = $row->state ? 'tick.png' : 'publish_x.png';
		$alt = $row->state ? JText::_('Read') : JText::_('Read');

		if ($user->authorize('core.users.manage')) {
			$linkA 	= 'index.php?option=com_users&view=user&task=edit&cid[]='. $row->user_id_from;
			$author = '<a href="'. JRoute::_($linkA) .'" title="'. JText::_('Edit User') .'">'. $row->user_from .'</a>';
		} else {
			$author = $row->user_from;
		}

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $i+1+$this->pagination->limitstart;?>
			</td>
			<td>
				<?php echo JHtml::_('grid.id', $i, $row->message_id); ?>
			</td>
			<td>
				<a href="index.php?option=com_messages&amp;view=message&amp;message_id=<?php echo $row->message_id; ?>">
					<?php echo $row->subject; ?></a>
			</td>
			<td align="center">
				<a href="javascript: void(0);">
					<img src="images/<?php echo $img;?>" width="16" height="16" border="0" alt="<?php echo $alt; ?>" /></a>
			</td>
			<td>
				<?php echo $author; ?>
			</td>
			<td>
				<?php echo JHtml::_('date', $row->date_time, JText::_('DATE_FORMAT_LC2')); ?>
			</td>
		</tr>
		<?php $k = 1 - $k;
		}
	?>
	</tbody>
	</table>
</div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>