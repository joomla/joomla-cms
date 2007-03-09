<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$user 	=& JFactory::getUser();

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'a.ordering');

jimport('joomla.html.tooltips');
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
	<td nowrap="nowrap">
		<?php
			echo $this->lists['catid'];
			echo $this->lists['state'];
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
				<?php JCommonHTML::tableOrdering( 'Title', 'a.title', $this->lists ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php JCommonHTML::tableOrdering( 'Published', 'a.published', $this->lists ); ?>
			</th>
			<th width="80" nowrap="nowrap">
				<a href="javascript:tableOrdering('a.ordering','ASC');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( 'Order' ); ?>">
					<?php echo JText::_( 'Order' );?>
				</a>
		 	</th>
			<th width="1%">
				<?php JCommonHTML::saveorderButton( $this->items ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php JCommonHTML::tableOrdering( 'ID', 'a.id', $this->lists ); ?>
			</th>
			<th width="25%"  class="title">
				<?php JCommonHTML::tableOrdering( 'Category', 'category', $this->lists ); ?>
			</th>
			<th width="5%">
				<?php JCommonHTML::tableOrdering( 'Hits', 'a.hits', $this->lists ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];

		$link 	= JRoute::_( 'index.php?option=com_weblinks&controller=weblink&task=edit&cid[]='. $row->id );

		$checked 	= JCommonHTML::CheckedOutProcessing( $row, $i );
		$published 	= JCommonHTML::PublishedProcessing( $row, $i );

		$row->cat_link 	= JRoute::_( 'index.php?option=com_categories&section=com_weblinks&task=edit&id='. $row->catid );
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
					<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Weblinks' ); ?>">
						<?php echo $row->title; ?></a>
				<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $published;?>
			</td>
			<td class="order" colspan="2">
				<span><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid),'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
			<td>
				<a href="<?php echo $row->cat_link; ?>" title="<?php echo JText::_( 'Edit Category' ); ?>">
				<?php echo $row->category; ?>
				</a>
			</td>
			<td align="center">
				<?php echo $row->hits; ?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	<tfoot>
		<td colspan="9">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>
</div>

<input type="hidden" name="controller" value="weblink" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>