<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHtml::_('behavior.tooltip'); ?>

<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (form.subject.value == "") {
		alert( "<?php echo JText::_( 'You must provide a subject.' ); ?>" );
	} else if (form.message.value == "") {
		alert( "<?php echo JText::_( 'You must provide a message.' ); ?>" );
	} else if (getSelectedValue('adminForm','user_id_to') < 1) {
		alert( "<?php echo JText::_( 'You must select a recipient.' ); ?>" );
	} else {
		submitform( pressbutton );
	}
}
</script>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<table class="adminform">
<tr>
	<td width="100">
		<?php echo JText::_( 'To' ); ?>:
	</td>
	<td width="85%">
		<?php
			echo JHtml::_(
				'select.genericlist',
				$this->recipients,
				'user_id_to',
				array(
					'format.depth' => 2,
					'list.attr' => 'class="inputbox" size="1"',
					'list.select' => $this->reply_user
				)
			);
		?>
	</td>
</tr>
<tr>
	<td>
		<?php echo JText::_( 'Subject' ); ?>:
	</td>
	<td>
		<input type="text" name="subject" size="50" maxlength="100" class="inputbox" value="<?php echo $this->subject; ?>"/>
	</td>
</tr>
<tr>
	<td valign="top">
		<?php echo JText::_( 'Message' ); ?>:
	</td>
	<td width="100%">
		<textarea name="message" style="width:95%" rows="30" class="inputbox"></textarea>
	</td>
</tr>
</table>

<input type="hidden" name="user_id_from" value="<?php echo $this->user->get('id'); ?>">
<input type="hidden" name="option" value="<?php echo $option; ?>">
<input type="hidden" name="task" value="">
<?php echo JHtml::_( 'form.token' ); ?>
</form>
