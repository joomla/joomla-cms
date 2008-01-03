<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doDeleteMenu') {

			submitform( pressbutton );
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<form action="index.php" method="post" name="adminForm">
	<div>
		<div style="width:30%;float:left">
			<?php if ( $this->modules ) : ?>
				<strong><?php echo JText::_( 'Module(s) being Deleted' ); ?>:</strong>
				<ol>
					<?php foreach ( $this->modules as $module ) :	?>
					<li><?php echo $module->title; ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
		<div style="width:30%;float:left">
			<strong><?php echo JText::_( 'Menu Items being Deleted' ); ?>:</strong>
			<ol>
				<?php foreach ($this->menuItems as $item) : ?>
				<li><?php echo $item->name; ?></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<div class="clr"></div>
	</div>

	<input type="hidden" name="id" value="<?php echo $this->table->id; ?>" />
	<input type="hidden" name="option" value="com_menus" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>