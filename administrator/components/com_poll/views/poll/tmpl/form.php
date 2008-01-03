<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
	$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
	$edit=JRequest::getVar( 'edit', true );
	JArrayHelper::toInteger($cid, array(0));

	$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

	JToolBarHelper::title(  JText::_( 'Poll' ).': <small><small>[ ' . $text.' ]</small></small>' );
	JToolBarHelper::Preview('index.php?option=com_poll&controller=poll&cid[]='.$cid[0]);
	JToolBarHelper::save();
	JToolBarHelper::apply();
	if ($edit) {
		// for existing items the button is renamed `close`
		JToolBarHelper::cancel( 'cancel', 'Close' );
	} else {
		JToolBarHelper::cancel();
	}
	JToolBarHelper::help( 'screen.polls.edit' );
?>

<?php
JFilterOutput::objectHTMLSafe( $this->poll, ENT_QUOTES );
?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		// do field validation
		if (form.title.value == "") {
			alert( "<?php echo JText::_( 'Poll must have a title', true ); ?>" );
		} else if( isNaN( parseInt( form.lag.value ) ) ) {
			alert( "<?php echo JText::_( 'Poll must have a non-zero lag time', true ); ?>" );
		//} else if (form.menu.options.value == ""){
		//	alert( "Poll must have pages." );
		//} else if (form.adminForm.textfieldcheck.value == 0){
		//	alert( "Poll must have options." );
		} else {
			submitform( pressbutton );
		}
	}
</script>
<form action="index.php" method="post" name="adminForm">
<div class="col width-45">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Details' ); ?></legend>
	<table class="admintable">
		<tr>
			<td width="110" class="key">
				<label for="title">
					<?php echo JText::_( 'Title' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="title" id="title" size="60" value="<?php echo $this->poll->title; ?>" />
			</td>
		</tr>
		<tr>
			<td width="110" class="key">
				<label for="alias">
					<?php echo JText::_( 'Alias' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="alias" id="alias" size="60" value="<?php echo $this->poll->alias; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="lag">
					<?php echo JText::_( 'Lag' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="lag" id="lag" size="10" value="<?php echo $this->poll->lag; ?>" />
				<?php echo JText::_( '(seconds between votes)' ); ?>
			</td>
		</tr>
		<tr>
			<td width="120" class="key">
				<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
				<?php echo JHTML::_( 'select.booleanlist',  'published', 'class="inputbox"', $this->poll->published ); ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="col width-45">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'Options' ); ?></legend>
	<table class="admintable">
	<?php for ($i=0, $n=count( $this->options ); $i < $n; $i++ ) { ?>
		<tr>
			<td class="key">
				<label for="polloption<?php echo $this->options[$i]->id; ?>">
					<?php echo JText::_( 'Option' ); ?> <?php echo ($i+1); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="polloption[<?php echo $this->options[$i]->id; ?>]" id="polloption<?php echo $this->options[$i]->id; ?>" value="<?php echo $this->options[$i]->text; ?>" size="60" />
			</td>
		</tr>
		<?php } for (; $i < 12; $i++) { ?>
		<tr>
			<td class="key">
				<label for="polloption<?php echo $i + 1; ?>">
					<?php echo JText::_( 'Option' ); ?> <?php echo $i + 1; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="polloption[]" id="polloption<?php echo $i + 1; ?>" value="" size="60" />
			</td>
		</tr>
		<?php } ?>
	</table>
	</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_poll" />
	<input type="hidden" name="id" value="<?php echo $this->poll->id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->poll->id; ?>" />
	<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>