<form action="index.php?option=com_menus&amp;menutype=<?php echo $this->menutype; ?>" method="post" name="adminForm">

<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
	<td nowrap="nowrap">
		<?php
		echo JText::_( 'Max Levels' );
		echo $this->lists['levellist'];
		echo $this->lists['state'];
		?>
	</td>
</tr>
</table>

<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
			</th>
			<th class="title" width="30%">
				<?php JCommonHTML::tableOrdering( 'Menu Item', 'm.name', $this->lists, 'view' ); ?>
			</th>
			<th width="5%">
				<?php echo JText::_( 'Default' ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php JCommonHTML::tableOrdering( 'Published', 'm.published', $this->lists, 'view' ); ?>
			</th>
			<th width="80" nowrap="nowrap">
				<a href="javascript:tableOrdering('m.ordering','ASC', 'view');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>"><?php echo JText::_( 'Order' ); ?></a>
			</th>
			<th width="1%">
				<?php JCommonHTML::saveorderButton( $this->items ); ?>
			</th>
			<th width="10%">
				<?php JCommonHTML::tableOrdering( 'Access', 'groupname', $this->lists, 'view' ); ?>
			</th>
			<th width="10%" class="title">
				<?php JCommonHTML::tableOrdering( 'Type', 'm.type', $this->lists, 'view' ); ?>
			</th>
			<th nowrap="nowrap">
				<?php JCommonHTML::tableOrdering( 'Itemid', 'm.id', $this->lists, 'view' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<td colspan="12">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	$i = 0;
	$n = count( $this->items );
	$rows = &$this->items;
	foreach ($rows as $row) :
		$access 	= JCommonHTML::AccessProcessing( $row, $i );
		$checked 	= JCommonHTML::CheckedOutProcessing( $row, $i );
		$published 	= JCommonHTML::PublishedProcessing( $row, $i );
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $i + 1 + $this->pagination->limitstart;?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td nowrap="nowrap">
				<?php if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) : ?>
				<?php echo $row->treename; ?>
				<?php else : ?>
				<a href="<?php echo JRoute::_( 'index.php?option=com_menus&menutype='.$row->menutype.'&task=edit&cid[]='.$row->id ); ?>"><?php echo $row->treename; ?></a>
				<?php endif; ?>
			</td>
			<td align="center">
				<?php if ( $row->home == 1 ) : ?>
				<img src="templates/khepri/images/menu/icon-16-default.png" alt="<?php echo JText::_( 'Default' ); ?>" />
				<?php else : ?>
				&nbsp;
				<?php endif; ?>
			</td>
			<td width="10%" align="center">
				<?php echo $published;?>
			</td>
			<td class="order" colspan="2" nowrap="nowrap">
				<span><?php echo $this->pagination->orderUpIcon( $i, $row->parent == 0 || $row->parent == @$rows[$i-1]->parent, 'orderup', 'Move Up', $this->ordering); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, $row->parent == 0 || $row->parent == @$rows[$i+1]->parent, 'orderdown', 'Move Down', $this->ordering ); ?></span>
				<?php $disabled = $this->ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo $access;?>
			</td>
			<td>
				<span class="editlinktip" style="text-transform:capitalize"><?php echo ($row->type == 'component') ? $row->com_name : $row->type; ?></span>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
		$i++;
		?>
	<?php endforeach; ?>
	</tbody>
	</table>

<input type="hidden" name="option" value="com_menus" />
<input type="hidden" name="menutype" value="<?php echo $this->menutype; ?>" />
<input type="hidden" name="task" value="view" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
