<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	$task		= JRequest::getCmd( 'task' );
	if ($task == 'copy') {
		$error = 'VALIDSELECTSECTCATCOPYITEMS';
		$labelsect = 'Copy to Section/Category';
		$labelarticle = 'Articles being copied';
		JToolBarHelper::title( JText::_( 'Copy Articles' ), 'copy_f2.png' );
		JToolBarHelper::custom( 'copysave', 'save.png', 'save_f2.png', 'Save', false );
	} else {
		$error = 'Please select something';
		$labelsect = 'Move to Section/Category';
		$labelarticle = 'Articles being Moved';
		JToolBarHelper::title( JText::_( 'Move Articles' ), 'move_f2.png' );
		JToolBarHelper::custom( 'movesectsave', 'save.png', 'save_f2.png', 'Save', false );
	}

	JToolBarHelper::cancel();
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (!getSelectedValue( 'adminForm', 'sectcat' )) {
		alert( "<?php echo JText::_( $error, true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<table class="adminform">
<tr>
	<td  valign="top" width="40%">
	<strong><?php echo JText::_( $labelsect ); ?>:</strong>
	<br />
	<?php echo $this->sectCatList; ?>
	<br /><br />
	</td>
	<td  valign="top">
	<strong><?php echo JText::_( $labelarticle ); ?>:</strong>
	<br />
	<?php
	echo "<ol>";
	foreach ( $this->items as $item ) {
		echo "<li>". $item->title ."</li>";
	}
	echo "</ol>";
	?>
	</td>
</tr>
</table>
<br /><br />

<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="sectionid" value="<?php echo $this->sectionid; ?>" />
<input type="hidden" name="task" value="" />
<?php
foreach ($this->cid as $id) {
	echo "\n<input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
}
?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>