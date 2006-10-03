<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doCopyMenu') {
			if ( document.adminForm.menu_name.value == '' ) {
				alert( "<?php echo JText::_( 'Please enter a name for the copy of the Menu', true ); ?>" );
				return;
			} else if ( document.adminForm.module_name.value == '' ) {
				alert( "<?php echo JText::_( 'Please enter a name for the new Module', true ); ?>" );
				return;
			} else {
				submitform( 'doCopyMenu' );
			}
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<form action="index.php" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<td width="3%"></td>
		<td valign="top" width="30%">
			<strong><?php echo JText::_( 'New Menu Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="menu_name" size="30" value="" />
			<br /><br /><br />
			<strong><?php echo JText::_( 'New Module Name' ); ?>:</strong>
			<br />
			<input class="inputbox" type="text" name="module_name" size="30" value="" />
			<br /><br />
		</td>
		<td valign="top" width="25%">
			<strong><?php echo JText::_( 'Menu being copied' ); ?>:</strong>
			<br />
			<font color="#000066"><strong><?php echo $this->table->type; ?></strong></font>
			<br /><br />
			<strong><?php echo JText::_( 'Menu Items being copied' ); ?>:</strong>
			<br />
			<ol>
				<?php foreach ($this->items as $item) : ?>
				<li>
					<font color="#000066"><?php echo $item->name; ?></font>
				</li>
				<input type="hidden" name="mids[]" value="<?php echo $item->id; ?>" />
				<?php endforeach; ?>
			</ol>
		</td>
		<td valign="top"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>

<input type="hidden" name="option" value="com_menus" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="type" value="<?php echo $this->table->type; ?>" />
</form>
