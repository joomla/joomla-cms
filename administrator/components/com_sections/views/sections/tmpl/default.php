<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	//Ordering allowed ?
	$ordering = ($this->filter->order == 's.ordering');

	JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_sections&amp;scope=' . $this->scope); ?>" method="post" name="adminForm">

<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
	<td nowrap="nowrap">
		<?php
		echo JHtml::_('grid.state', $this->filter->state );
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
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows );?>);" />
		</th>
		<th class="title">
			<?php echo JHtml::_('grid.sort',   'Title', 's.title', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="5%">
			<?php echo JHtml::_('grid.sort',   'Published', 's.published', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo JHtml::_('grid.sort',   'Order', 's.ordering', @$this->filter->order_Dir, @$this->filter->order ); ?>
			<?php echo JHtml::_('grid.order',  $this->rows ); ?>
		</th>
		<th width="10%">
			<?php echo JHtml::_('grid.sort',   'Access', 'groupname', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="5%" nowrap="nowrap">
			<?php echo JText::_( 'Num Categories' ); ?>
		</th>
		<th width="5%" nowrap="nowrap">
			<?php echo JText::_( 'Num Active' ); ?>
		</th>
		<th width="5%" nowrap="nowrap">
			<?php echo JText::_( 'Num Trash' ); ?>
		</th>
		<th width="1%" nowrap="nowrap">
			<?php echo JHtml::_('grid.sort',   'ID', 's.id', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
	</tr>
</thead>
<tfoot>
	<tr>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
</tfoot>
<tbody>
<?php
$k = 0;
for ( $i=0, $n=count( $this->rows ); $i < $n; $i++ ) {
	$row = &$this->rows[$i];

	$link 		= 'index.php?option=com_sections&scope=content&task=edit&cid[]='. $row->id;

	$access 	= JHtml::_('grid.access',   $row, $i );
	$checked 	= JHtml::_('grid.checkedout',   $row, $i );
	$published 	= JHtml::_('grid.published', $row, $i );
	?>
	<tr class="<?php echo "row$k"; ?>">
		<td align="center">
			<?php echo $this->pagination->getRowOffset( $i ); ?>
		</td>
		<td>
			<?php echo $checked; ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'Title' );?>::<?php echo $row->title; ?>">
			<?php
			if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
				echo $row->title;
			} else {
				?>
				<a href="<?php echo JRoute::_( $link ); ?>">
					<?php echo $row->title; ?></a>
				<?php
			}
			?></span>
		</td>
		<td align="center">
			<?php echo $published;?>
		</td>
		<td class="order">
			<span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $ordering ); ?></span>
			<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering ); ?></span>
			<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
			<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
		</td>
		<td align="center">
			<?php echo $access;?>
		</td>
		<td align="center">
			<?php echo $row->categories; ?>
		</td>
		<td align="center">
			<?php echo $row->active; ?>
		</td>
		<td align="center">
			<?php echo $row->trash; ?>
		</td>
		<td align="center">
			<?php echo $row->id; ?>
		</td>
		<?php
		$k = 1 - $k;
		?>
	</tr>
	<?php
}
?>
</tbody>
</table>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="scope" value="<?php echo $this->scope;?>" />
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="chosen" value="" />
<input type="hidden" name="act" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
</form>