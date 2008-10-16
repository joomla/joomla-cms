<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<?php
	// Set toolbar items for the page
	JToolBarHelper::title( JText::_( 'CONTACT_MANAGER' ), 'generic.png' );

	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
	JToolBarHelper::deleteList();
	JToolBarHelper::editListX();
	JToolBarHelper::addNewX();
	JToolBarHelper::custom('importView', 'save.png', 'save_f2.png', JText::_('IMPORT'), false, true);

	//JToolBarHelper::preferences('com_contactdirectory', '500');
	//JToolBarHelper::help( 'screen.contactmanager' );
?>

<form action="index.php" method="post" name="adminForm">
<table>
	<tr>
		<td align="left" width="100%">
			<?php echo JText::_( 'FILTER' ); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
		</td>
		<td nowrap="nowrap">
			<?php
				echo $this->lists['category'];
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
				<?php echo JHTML::_('grid.sort', JText::_( 'NAME' ), 'c.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="15%">
				<?php echo JHTML::_('grid.sort', JText::_( 'E-MAIL' ), 'email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'PUBLISHED' ), 'c.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'ACCESS' ), 'groupname', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th class="title" nowrap="nowrap" width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'LINKED_USER' ), 'user', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'c.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
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

		$link 		= JRoute::_( 'index.php?option=com_contactdirectory&controller=contact&view=contact&task=edit&cid[]='. $row->id );
		$access 	= JHTML::_('grid.access',   $row, $i );
		$checked 	= JHTML::_('grid.checkedout',   $row, $i );
		$published 	= JHTML::_('grid.published', $row, $i );

		$user_link	= JRoute::_( 'index.php?option=com_users&task=editA&cid[]='. $row->user_id );

		$ordering = ($this->lists['order'] == 'c.ordering');

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
			<td><?php echo $checked; ?></td>
			<td>
				<?php
				if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
					echo $row->name;
				} else {
				?>
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'EDIT_CONTACTS' );?>::<?php echo $row->name; ?>">
					<a href="<?php echo $link; ?>">
						<?php echo $row->name; ?></a></span>
				<?php
				}
				?>
			</td>
			<td><?php echo $row->email; ?></td>
			<td align="center"><?php echo $published;?></td>
			<td align="center"><?php echo $access;?></td>
			<td align="center">
				<a href="<?php echo $user_link; ?>" title="<?php echo JText::_( 'EDIT_USER' ); ?>">
					<?php echo $row->user; ?>
				</a>
			</td>
			<td align="center"><?php echo $row->id; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>
</div>

	<input type="hidden" name="controller" value="contact" />
	<input type="hidden" name="option" value="com_contactdirectory" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>