<form action="index.php" method="post" name="adminForm">
<table class="adminform">
<tr>
	<td width="3%"></td>
	<td  valign="top" width="30%">
	<strong><?php echo JText::_( 'Copy to Menu' ); ?>:</strong>
	<br />
	<?php echo $this->MenuList; ?>
	<br /><br />
	</td>
	<td  valign="top">
	<strong><?php echo JText::_( 'Menu Items being copied' ); ?>:</strong>
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
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="menutype" value="<?php echo $this->menutype; ?>" />
<input type="hidden" name="task" value="" />
<?php foreach ( $this->items as $item ) : ?>
<input type="hidden" name="cid[]" value="<?php echo $item->id; ?>" />
<?php endforeach; ?>
</form>
