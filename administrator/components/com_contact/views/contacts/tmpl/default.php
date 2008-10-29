<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JHtml::_('behavior.tooltip');
	$ordering = ($this->filter->order == 'a.ordering');
?>

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
		echo JHtml::_('list.category',  'filter_catid', 'com_contact_details', intval( $this->filter->catid ), 'onchange="document.adminForm.submit();"' );
		echo JHtml::_('grid.state',  $this->filter->state );
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
				<?php echo JHtml::_('grid.sort',  'Name', 'a.name', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  'Published', 'a.published', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  'Order', 'a.ordering', $this->filter->order_Dir, $this->filter->order ); ?>
				<?php echo JHtml::_('grid.order',  $this->items ); ?>
			</th>
			<th width="8%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',   'Access', 'a.access', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="12%"  class="title">
				<?php echo JHtml::_('grid.sort',  'Category', 'category', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th class="title" nowrap="nowrap" width="10%">
				<?php echo JHtml::_('grid.sort',   'Linked to User', 'user', $this->filter->order_Dir, $this->filter->order ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  'ID', 'a.id', $this->filter->order_Dir, $this->filter->order ); ?>
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

		$link 	= JRoute::_( 'index.php?option=com_contact&view=contact&task=edit&cid[]='. $row->id );

		$checked 	= JHtml::_('grid.checkedout',   $row, $i );
		$access 	= JHtml::_('grid.access',   $row, $i );
		$published 	= JHtml::_('grid.published', $row, $i );

		$row->cat_link 	= JRoute::_( 'index.php?option=com_categories&section=com_contact&task=edit&type=other&cid[]='. $row->catid );
		$row->user_link	= JRoute::_( 'index.php?option=com_users&task=edit&cid[]='. $row->user_id );
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
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Contact' );?>::<?php echo $row->title; ?>">
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
				<span><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid),'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>
				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center">
				<?php echo $access;?>
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit Category' );?>::<?php echo $row->category; ?>">
				<a href="<?php echo $row->cat_link; ?>" >
				<?php echo $row->category; ?></a><span>
			</td>
			<td>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit User' );?>::<?php echo $row->user; ?>">
				<a href="<?php echo $row->user_link; ?>">
				<?php echo $row->user; ?></a></span>
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

<input type="hidden" name="option" value="com_contact" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>