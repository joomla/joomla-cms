<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_categories&amp;extension=' . $this->extension->option); ?>" method="post" name="adminForm">

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
		<th width="10" align="left">
			<?php echo JText::_( 'Num' ); ?>
		</th>
		<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows );?>);" />
		</th>
		<th class="title">
			<?php echo JHtml::_('grid.sort',   'Title', 'c.title', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="5%">
			<?php echo JHtml::_('grid.sort',   'Published', 'c.published', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo JHtml::_('grid.sort',   'Order by', 'c.lft', @$this->filter->order_Dir, @$this->filter->order ); ?>
			<?php echo JHtml::_('grid.order',  $this->rows ); ?>
		</th>
		<th width="7%">
			<?php echo JHtml::_('grid.sort',   'Access', 'groupname', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<?php
		if ( $this->extension->option == 'com_content') {
			?>
			<th width="5%">
				<?php echo JText::_( 'Num Active' ); ?>
			</th>
			<th width="5%">
				<?php echo JText::_( 'Num Trash' ); ?>
			</th>
			<?php
		}
		?>
		<th width="1%" nowrap="nowrap">
			<?php echo JHtml::_('grid.sort',   'ID', 'c.id', @$this->filter->order_Dir, @$this->filter->order ); ?>
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
if( count( $this->rows ) ) {
for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
	$row 	= &$this->rows[$i];
	$link = 'index.php?option=com_categories&extension='. $this->extension->option .'&task=edit&cid[]='. $row->id;

	$access 	= JHtml::_('grid.access',   $row, $i );
	$checked 	= JHtml::_('grid.checkedout',   $row, $i );
	$published 	= JHtml::_('grid.published', $row, $i );
	?>
		<tr class="<?php echo "row$k"; ?>">
		<td>
			<?php echo $this->pagination->getRowOffset( $i ); ?>
		</td>
		<td>
			<?php echo $checked; ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'Title' );?>::<?php echo $row->title; ?>">
			<?php
			if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out )  ) {
				echo str_repeat('&nbsp;&nbsp;', $row->depth).$row->title;
			} else {
				?>
				<a href="<?php echo JRoute::_( $link ); ?>">
					<?php echo str_repeat('&nbsp;&nbsp;', $row->depth).$row->title; ?></a>
				<?php
			}
			?></span>
		</td>
		<td align="center">
			<?php echo $published;?>
		</td>
		<td class="order">
			<?php ++$ordering[$row->depth];
			if($row->depth < $this->rows[$i-1]->depth)
			{
				for($e = 0; $e < ($this->rows[$i-1]->depth - $row->depth); $e++)
				{
					$ordering[$row->depth + $e + 1] = 0;
				}
			} ?>
			<span><?php echo $this->pagination->orderUpIcon( $i, ($row->depth <= @$this->rows[$i-1]->depth), 'orderup', 'Move Up', true ); ?></span>
			<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->rgt != 2 * count($this->rows) + 1 && ($row->level > @$this->rows[$i-1]->depth || $row->depth <= @$this->rows[$i+1]->depth)), 'orderdown', 'Move Down', true ); ?></span>
			<?php $disabled = true ?  '' : 'disabled="disabled"'; ?>
			<input type="text" name="order[]" size="5" value="<?php echo $ordering[$row->depth]; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
		</td>
		<td align="center">
			<?php echo $access;?>
		</td>
		<?php
		if ( $this->extension->option == 'com_content') {
			?>
			<td align="center">
				<?php echo $row->active; ?>
			</td>
			<td align="center">
				<?php echo $row->trash; ?>
			</td>
			<?php
		}
		$k = 1 - $k;
		?>
		<td align="center">
			<?php echo $row->id; ?>
		</td>
	</tr>
	<?php
}
} else {
	if( $this->extension->option == 'com_content') {
		?>
		<tr><td colspan="10"><?php echo JText::_('There are no Categories'); ?></td></tr>
		<?php
	} else {
		?>
		<tr><td colspan="8"><?php echo JText::_('There are no Categories'); ?></td></tr>
		<?php
	}
}
?>
</tbody>
</table>

<input type="hidden" name="option" value="com_categories" />
<input type="hidden" name="extension" value="<?php echo $this->extension->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="chosen" value="" />
<input type="hidden" name="act" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHtml::_( 'form.token' ); ?>
</form>