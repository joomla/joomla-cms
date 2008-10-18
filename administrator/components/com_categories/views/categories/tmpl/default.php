<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	//Ordering allowed ?
	$ordering = ($this->filter->order == 'c.ordering');

	JHTML::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_categories&amp;section=' . $this->filter->section); ?>" method="post" name="adminForm">

<table>
	<tr>
		<td align="left" width="100%">
			<?php echo JText::_( 'Filter' ); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.getElementById('sectionid').value='-1';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
		</td>
		<td nowrap="nowrap">
			<?php
			if ( $this->filter->section == 'com_content') {
				echo JHTML::_('list.section',  'sectionid', $this->filter->sectionid, 'onchange="document.adminForm.submit();"' );
			}
			?>
			<?php
			echo JHTML::_('grid.state', $this->filter->state );
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
			<?php echo JHTML::_('grid.sort',   'Title', 'c.title', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="5%">
			<?php echo JHTML::_('grid.sort',   'Published', 'c.published', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo JHTML::_('grid.sort',   'Order by', 'c.ordering', @$this->filter->order_Dir, @$this->filter->order ); ?>
			<?php echo JHTML::_('grid.order',  $this->rows ); ?>
		</th>
		<th width="7%">
			<?php echo JHTML::_('grid.sort',   'Access', 'groupname', @$this->filter->order_Dir, @$this->filter->order ); ?>
		</th>
		<?php
		if ( $this->filter->section == 'com_content') {
			?>
			<th width="20%"  class="title">
				<?php echo JHTML::_('grid.sort',   'Section', 'section_name', @$this->filter->order_Dir, @$this->filter->order ); ?>
			</th>
			<?php
		}
		?>
		<?php
		if ( $this->type == 'content') {
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
			<?php echo JHTML::_('grid.sort',   'ID', 'c.id', @$this->filter->order_Dir, @$this->filter->order ); ?>
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

	$row->sect_link = JRoute::_( 'index.php?option=com_sections&task=edit&cid[]='. $row->section );

	$link = 'index.php?option=com_categories&section='. $this->filter->section .'&task=edit&cid[]='. $row->id .'&type='.$this->type;

	$access 	= JHTML::_('grid.access',   $row, $i );
	$checked 	= JHTML::_('grid.checkedout',   $row, $i );
	$published 	= JHTML::_('grid.published', $row, $i );
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
			<span><?php echo $this->pagination->orderUpIcon( $i, ($row->section == @$this->rows[$i-1]->section), 'orderup', 'Move Up', $ordering ); ?></span>
			<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->section == @$this->rows[$i+1]->section), 'orderdown', 'Move Down', $ordering ); ?></span>
			<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
			<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
		</td>
		<td align="center">
			<?php echo $access;?>
		</td>
		<?php
		if ( $this->filter->section == 'com_content' ) {
			?>
			<td>
				<a href="<?php echo $row->sect_link; ?>" title="<?php echo JText::_( 'Edit Section' ); ?>">
					<?php echo $row->section_name; ?></a>
			</td>
			<?php
		}
		?>
		<?php
		if ( $this->type == 'content') {
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
	if( $this->type == 'content') {
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
<input type="hidden" name="section" value="<?php echo $this->filter->section;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="chosen" value="" />
<input type="hidden" name="act" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->filter->order; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->order_Dir; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>