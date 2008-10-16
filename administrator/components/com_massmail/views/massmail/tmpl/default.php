<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}
		// do field validation
		if (form.mm_subject.value == ""){
			alert( "<?php echo JText::_( 'Please fill in the subject', true ); ?>" );
		} else if (getSelectedValue('adminForm','mm_group') < 0){
			alert( "<?php echo JText::_( 'Please select a group', true ); ?>" );
		} else if (form.mm_message.value == ""){
			alert( "<?php echo JText::_( 'Please fillin the message', true ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" name="adminForm" method="post">

<div class="col width-30">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td class="key">
				<label for="mm_recurse">
					<?php echo JText::_( 'Mail to Child Groups' ); ?>:
				</label>
			</td>
			<td>
				<input type="checkbox" name="mm_recurse" id="mm_recurse" value="RECURSE" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="mm_mode">
					<?php echo JText::_( 'Send in HTML mode' ); ?>:
				</label>
			</td>
			<td>
				<input type="checkbox" name="mm_mode" id="mm_mode" value="1" />
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="mm_group">
					<?php echo JText::_( 'Group' ); ?>:
				</label>
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td colspan="2" valign="top">
				<?php echo JHTML::_('select.genericlist', $this->gtree, 'mm_group', 'size="10"', 'value', 'text', 0 ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="mm_bcc" title="<?php echo JText::_( 'Send as Blind Carbon Copy' ); ?>">
						<?php echo JText::_( 'Recipients as BCC' ); ?>:
				</label>
			</td>
			<td>
				<input type="checkbox" name="mm_bcc" id="mm_bcc" value="1" checked="checked" />
			</td>
		</tr>
		</table>
	</fieldset>
</div>

<div class="col width-70">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Message' ); ?></legend>

		<table class="admintable">
		<tr>
			<td class="key">
				<label for="mm_subject">
					<?php echo JText::_( 'Subject' ); ?>:
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="mm_subject" id="mm_subject" value="" size="150" />
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="mm_message">
					<?php echo JText::_( 'Message' ); ?>:
				</label>
			</td>
			<td id="mm_pane" >
				<textarea rows="20" cols="150" name="mm_message" id="mm_message" class="inputbox"></textarea>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="" />
</form>
