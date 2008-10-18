<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	//Ordering allowed ?
	$ordering = ($this->filter->order == 'a.ordering');

	JHTML::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_newsfeeds'); ?>" method="post" name="adminForm">

<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
	<td nowrap="nowrap">
		<?php
		echo JHTML::_('list.category',  'filter_catid', 'com_newsfeeds', $this->filter->catid, 'onchange="document.adminForm.submit();"' );
		echo JHTML::_('grid.state',  $this->filter->state );
		?>
	</td>
</tr>
</table>

	<table class="adminlist">
	<thead>
		<tr>
			<th width="10">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',   'News Feed', 'a.name', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort',   'Published', 'a.published', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'Order', 'a.ordering', $this->filter->order_Dir, $this->filter->order ); ?>
				<?php echo JHTML::_('grid.order',  $this->items ); ?>
			</th>
			<th class="title" width="10%">
				<?php echo JHTML::_('grid.sort',   'Category', 'catname', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'Num Articles', 'a.numarticles', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort',   'Cache time', 'a.cache_time', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'ID', 'a.id', $this->filter->order_Dir, $this->filter->order ); ?>
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
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];

		$link 		= JRoute::_( 'index.php?option=com_newsfeeds&task=edit&cid[]='. $row->id );

		$checked 	= JHTML::_('grid.checkedout',   $row, $i );
		$published 	= JHTML::_('grid.published', $row, $i );

		$row->cat_link 	= JRoute::_( 'index.php?option=com_categories&section=com_newsfeeds&task=edit&cid[]='. $row->catid );
		?>
		<tr class="<?php echo 'row'. $k; ?>">
			<td align="center">
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
					echo $row->name;
				} else {
					?>
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Newsfeed' );?>::<?php echo $row->name; ?>">
					<a href="<?php echo $link; ?>">
						<?php echo $row->name; ?></a></span>
					<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $published;?>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon($i, ($row->catid == @$this->items[$i-1]->catid), 'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon($i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td>
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
					<?php echo $row->catname;?></a>
			</td>
			<td align="center">
				<?php echo $row->numarticles;?>
			</td>
			<td align="center">
				<?php echo $row->cache_time;?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>

	<table class="adminform">
	<tr>
		<td>
			<div align="center">
			<strong><?php echo JText::_('Cache Directory'); ?></strong>
			<?php echo $this->cache_folder; ?>
			<b><span style="color:<?php echo $this->cache_writable ? 'green' : 'red'; ?>;">
				<?php echo JText::_( $this->cache_writable ? 'Writable' : 'Unwritable' ); ?>
			</span></b>
			</div>
		</td>
	</tr>
	</table>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
</form>