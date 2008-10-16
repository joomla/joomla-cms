<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>
<?php JRequest::setVar( 'hidemainmenu', 1 ); ?>


<?php
	$editor =& JFactory::getEditor();

	// Set toolbar items for the page
	$edit	= JRequest::getVar('edit',true);
	$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

	JToolBarHelper::title( JText::_( 'Section' ).': <small><small>[ '. $text.' ]</small></small>', 'sections.png' );
	JToolBarHelper::save();
	JToolBarHelper::apply();
	if ( $edit ) {
		// for existing items the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );
	} else {
		JToolBarHelper::cancel();
	}
	JToolBarHelper::help( 'screen.sections.edit' );
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	if ( form.title.value == '' ){
		alert("<?php echo JText::_( 'Section must have a title', true ); ?>");
	} else {
		<?php
		echo $editor->save( 'description' ) ; ?>
		submitform(pressbutton);
	}
}
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div class="col width-60">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" class="key">
				<?php echo JText::_( 'Scope' ); ?>:
			</td>
			<td colspan="2">
				<strong>
				<?php echo $this->row->scope; ?>
				</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="title">
					<?php echo JText::_( 'Title' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<input class="text_area" type="text" name="title" id="title" value="<?php echo $this->row->title; ?>" size="50" maxlength="50" title="<?php echo JText::_( 'TIPTITLEFIELD' ); ?>" />
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="key">
				<label for="alias">
					<?php echo JText::_( 'Alias' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<input class="text_area" type="text" name="alias" id="alias" value="<?php echo $this->row->alias; ?>" size="50" maxlength="255" title="<?php echo JText::_( 'TIPNAMEFIELD' ); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td colspan="2">
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="ordering">
					<?php echo JText::_( 'Ordering' ); ?>:
				</label>
			</td>
			<td colspan="2">
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" valign="top" class="key">
				<label for="access">
					<?php echo JText::_( 'Access Level' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['access']; ?>
			</td>
			<td rowspan="4" width="50%">
				<?php
					$path = JURI::root() . 'images/';
					if ($this->row->image != 'blank.png') {
						$path.= 'stories/';
					}
				?>
				<img src="<?php echo $path;?><?php echo $this->row->image;?>" name="imagelib" width="80" height="80" border="2" alt="<?php echo JText::_( 'Preview' ); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="image">
					<?php echo JText::_( 'Image' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['image']; ?>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="key">
				<label for="image_position">
					<?php echo JText::_( 'Image Position' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->lists['image_position']; ?>
			</td>
		</tr>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Description' ); ?></legend>

		<table class="admintable">
		<tr>
			<td valign="top" colspan="3">
				<?php
				// parameters : areaname, content, width, height, cols, rows
				echo $editor->display( 'description',  $this->row->description, '550', '300', '60', '20', array('pagebreak', 'readmore') ) ;
				?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="scope" value="<?php echo $this->row->scope; ?>" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="oldtitle" value="<?php echo $this->row->title ; ?>" />
</form>