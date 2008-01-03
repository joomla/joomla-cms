<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancelItem') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'menu' )) {
				alert( "<?php echo JText::_( 'Please select a menu from the list', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

<form action="index.php" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Menu' ); ?>:</strong>
			<br />
			<?php echo $this->MenuList ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Menu Items being moved' ); ?>:
			</strong>
			<br />
			<ol>
				<?php foreach ( $this->items as $item ) : ?>
				<li><?php echo $item->name; ?></li>
				<?php endforeach; ?>
			</ol>
			</td>
		</tr>
	</table>

	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="menutype" value="<?php echo $this->menutype; ?>" />
<?php foreach ( $this->items as $item ) : ?>
	<input type="hidden" name="cid[]" value="<?php echo $item->id; ?>" />
<?php endforeach; ?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
