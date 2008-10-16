<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
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
			echo JHTML::_('list.category',  'filter_catid', 'com_weblinks', intval( $this->filter->catid ), 'onchange="document.adminForm.submit();"' );
			echo JHTML::_('weblink.statefilter',  $this->filter->state );
		?>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort',  'Title', 'a.title', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'State', 'a.state', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'Order', 'a.ordering', $this->filter->order_Dir, $this->filter->order ); ?>
				<?php echo JHTML::_('grid.order',  $this->items ); ?>
			</th>
			<th width="15%"  class="title">
				<?php echo JHTML::_('grid.sort',  'Category', 'category', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort',  'Hits', 'a.hits', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'ID', 'a.id', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="9">
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

		$link 	= JRoute::_( 'index.php?option=com_weblinks&view=weblink&task=edit&cid[]='. $row->id );

		$checked 	= JHTML::_('grid.checkedout',   $row, $i );
		$state 		= JHTML::_('weblink.state', $row, $i );

		$ordering = ($this->filter->order == 'a.ordering');

		$row->cat_link 	= JRoute::_( 'index.php?option=com_categories&section=com_weblinks&task=edit&type=other&cid[]='. $row->catid );
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
					echo $row->title;
				} else {
				?>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Weblinks' );?>::<?php echo $row->title; ?>">
					<a href="<?php echo $link; ?>">
						<?php echo $row->title; ?></a></span>
				<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $state;?>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid),'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Category' );?>::<?php echo $row->category; ?>">
				<a href="<?php echo $row->cat_link; ?>" >
				<?php echo $row->category; ?></a><span>
			</td>
			<td align="center">
				<?php echo $row->hits; ?>
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
</div>

	<input type="hidden" name="option" value="com_weblinks" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>