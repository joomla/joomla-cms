<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHtml::_('behavior.tooltip'); ?>
<?php JRequest::setVar( 'hidemainmenu', 1 ); ?>


<?php
	$isCopy		= (JRequest::getCmd( 'task', 'copyselect' ) == 'copyselect');
	$option		= JRequest::getCmd( 'option' );

	// Set toolbar items for the page
	JToolBarHelper::title( JText::_( 'Category' ) .': <small><small>[ '. JText::_( $isCopy ? 'Copy' : 'Move' ).' ]</small></small>', 'categories.png' );
	JToolBarHelper::save( $isCopy ? 'copysave' : 'movesave' );
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
	if (!getSelectedValue( 'adminForm', 'sectionmove' )) {
		alert( "<?php echo JText::_( 'Please select a section from the list', true ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<br />
<table class="adminform">
<tr>
	<td width="3%"></td>
	<td  valign="top" width="30%">
	<strong><?php echo JText::_( $isCopy ? 'Copy to Section' : 'Move to Section' ); ?>:</strong>
	<br />
	<?php echo $this->lists['SectionList'] ?>
	<br /><br />
	</td>
	<td  valign="top" width="20%">
	<strong><?php echo JText::_( $isCopy ? 'Categories being copied' : 'Categories being moved' ); ?>:</strong>
	<br />
	<?php
	echo "<ol>";
	foreach ( $this->items as $item ) {
		echo "<li>". $item->title ."</li>";
	}
	echo "</ol>";
	?>
	</td>
	<td valign="top" width="20%">
	<strong><?php echo JText::_( $isCopy ? 'Articles being copied' : 'Articles being moved' ); ?>:</strong>
	<br />
	<?php
	echo "<ol>";
	foreach ( $this->contents as $content ) {
		echo "<li>". $content->title ."</li>";
		echo "\n <input type=\"hidden\" name=\"item[]\" value=\"$content->id\" />";
	}
	echo "</ol>";
	?>
	</td>
	<td valign="top">
	<?php echo JText::_( 'This will copy the Categories listed' ); ?>
	<br />
	<?php echo JText::_( 'and all the items within the Category (also listed)' ); ?>
	<br />
	<?php echo JText::_( 'to the selected Section' ); ?>
	<br />
	<?php if ($isCopy) echo JText::_( 'NOTE: IF SAME SECTION' ); ?>
	</td>.
</tr>
</table>
<br /><br />

<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="section" value="<?php echo $this->sectionOld;?>" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="redirect" value="<?php echo $this->redirect; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_( 'form.token' ); ?>
<?php
foreach ( $this->cid as $id ) {
	echo "\n <input type=\"hidden\" name=\"cid[]\" value=\"$id\" />";
}
?>
</form>