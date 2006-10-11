<span class="small">
<?php
	jimport('joomla.filesystem.path');
	if (JPath::canCHMOD( JPATH_CONFIGURATION . DS . 'configuration.php' )) {
	if (is_writable( JPATH_CONFIGURATION . DS . 'configuration.php')) {
?>
	<label for="disable_write">
		<?php echo JText::_( 'Make unwriteable after saving' ); ?>
	</label>
	<input type="checkbox" id="disable_write" name="disable_write" value="1" />
<?php
} else {
?>
	<label for="enable_write">
		<?php echo JText::_( 'Override write protection while saving' ); ?>
	</label>
	<input type="checkbox" id="enable_write" name="enable_write" value="1" />
<?php
}
}
?>
</span>
<span class="small">
<?php echo JText::_( 'configuration.php' ); ?> :
<?php echo is_writable( JPATH_CONFIGURATION . DS . 'configuration.php' ) ? '<b><font color="green"> '. JText::_( 'Writeable' ) .'</font></b>' : '<b><font color="red"> '. JText::_( 'Unwriteable' ) .'</font></b>' ?>
</span>
