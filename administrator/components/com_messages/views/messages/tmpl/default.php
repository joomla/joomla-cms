<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm">

<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Search' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
	<td nowrap="nowrap">
		<?php
		echo JHTML::_('grid.state',  $this->filter->state, 'Read', 'Unread' );
		?>
	</td>
</tr>
</table>

<div id="tablecell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20" class="title">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
			</th>
			<th width="50%" class="title">
				<?php echo JHTML::_('grid.sort',   'Subject', 'a.subject', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%" class="title" align="center">
				<?php echo JHTML::_('grid.sort',   'Read', 'a.state', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="25%" class="title">
				<?php echo JHTML::_('grid.sort',   'From', 'user_from', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="15%" class="title" nowrap="nowrap" align="center">
				<?php echo JHTML::_('grid.sort',   'Date', 'a.date_time', $this->filter->order_Dir, $this->filter->order ); ?>
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
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
		$row =& $this->rows[$i];
		$img = $row->state ? 'tick.png' : 'publish_x.png';
		$alt = $row->state ? JText::_( 'Read' ) : JText::_( 'Read' );

		if ( $this->user->authorize( 'com_users', 'manage' ) ) {
			$linkA 	= 'index.php?option=com_users&view=user&task=edit&cid[]='. $row->user_id_from;
			$author = '<a href="'. JRoute::_( $linkA ) .'" title="'. JText::_( 'Edit User' ) .'">'. $row->user_from .'</a>';
		} else {
			$author = $row->user_from;
		}

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $i+1+$this->pagination->limitstart;?>
			</td>
			<td>
				<?php echo JHTML::_('grid.id', $i, $row->message_id ); ?>
			</td>
			<td>
				<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','view')">
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
				<?php echo JHTML::_('date', $row->date_time, JText::_('DATE_FORMAT_LC2')); ?>
			</td>
		</tr>
		<?php $k = 1 - $k;
		}
	?>
	</tbody>
	</table>
</div>

<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>