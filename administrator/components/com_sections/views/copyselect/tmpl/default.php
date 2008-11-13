<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHtml::_('behavior.tooltip'); ?>
<?php JRequest::setVar( 'hidemainmenu', 1 ); ?>


<?php
	$scope 		= JRequest::getCmd( 'scope' );
	$option		= JRequest::getCmd( 'option' );

	// Set toolbar items for the page
	JToolBarHelper::title( JText::_( 'Section' ) .': <small><small>[ '. JText::_( 'Copy' ).' ]</small></small>', 'section.png' );
	JToolBarHelper::save( 'copysave' );
	JToolBarHelper::cancel();
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	submitform(pressbutton);
}
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<table class="adminform">
<tr>
	<td width="3%"></td>
	<td  valign="top" width="20%">
	<strong><?php echo JText::_( 'Sections being copied' ); ?>:</strong>
	<br />
		<ol>
	<?php
	foreach ( $this->sections as $section ) {
		?>
		<li>
			<?php echo $section->title; ?>
			<input type="hidden" name="section[]" value="<?php echo $section->id; ?>" />
		</li>
		<?php
	}
	?>
		</ol>
	</td>
	<td  valign="top" width="20%">
		<strong><?php echo JText::_( 'Categories being copied' ); ?>:</strong>
		<br />
		<ol>
		<?php
		foreach ( $this->categories as $category ) {
			?>
			<li>
				<?php echo $category->title; ?>
				<input type="hidden" name="category[]" value="<?php echo $category->id; ?>" />
			</li>
			<?php
		}
		?>
		</ol>
	</td>
	<td valign="top" width="20%">
		<strong><?php echo JText::_( 'Articles being copied' ); ?>:</strong>
		<br />
		<ol>
		<?php
		foreach ( $this->contents as $content ) {
			?>
			<li>
				<?php echo $content->title; ?>
				<input type="hidden" name="content[]" value="<?php echo $content->id; ?>" />
			</li>
			<?php
		}
		?>
		</ol>
	</td>
	<td valign="top">
		<?php echo JText::_( 'This will copy the Categories listed' ); ?>
		<br />
		<?php echo JText::_( 'DESCALLITEMSWITHINCAT' ); ?>
		<br />
		<?php echo JText::_( 'to the new Section(s) created.' ); ?>
	</td>
</tr>
</table>
<br /><br />

<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="scope" value="content" />
<?php
foreach ( $this->cid as $id ) {
	?>
	<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
	<?php
}
?>
<?php echo JHtml::_( 'form.token' ); ?>
</form>