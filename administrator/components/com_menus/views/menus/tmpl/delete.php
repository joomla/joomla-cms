<form action="index.php" method="post" name="adminForm">
	<fieldset>
		<div style="float: right">
			<button type="button" onclick="if (confirm('<?php echo str_replace( "\n", '\n', JText::_( 'WARNWANTDELTHISMENU' ) ); ?>')){ submitbutton('delete');};window.top.document.popup.hide();">
				Delete</button>
			<button type="button" onclick="window.top.document.popup.hide();">
				<?php echo JText::_( 'Cancel' );?></button>
	    </div>
		<?php echo JText::_( '* This will Delete this Menu,' ); ?> <?php echo JText::_( 'DESCALLMENUITEMS' ); ?>
	</fieldset>

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
</form>
