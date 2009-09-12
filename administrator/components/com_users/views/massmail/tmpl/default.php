<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform(pressbutton);
			return;
		}
		// do field validation
		if (form.jform_subject.value == ""){
			alert("<?php echo JText::_('MassMail_Please_fill_in_the_subject', true); ?>");
		} else if (getSelectedValue('adminForm','jform_group') < 0){
			alert("<?php echo JText::_('MassMail_Please_select_a_group', true); ?>");
		} else if (form.jform_message.value == ""){
			alert("<?php echo JText::_('MassMail_Please_fill_in_the_message', true); ?>");
		} else {
			submitform(pressbutton);
		}
	}
</script>

<form action="index.php" name="adminForm" method="post">

	<div class="col width-30">
		<fieldset class="adminform">
			<legend><?php echo JText::_('MassMail_Details'); ?></legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getLabel('recurse'); ?></td> 
					<td><?php echo $this->form->getInput('recurse'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('mode'); ?></td>
					<td><?php echo $this->form->getInput('mode'); ?></td>
				</tr>
				<tr>
					<td class="key" valign="top"><?php echo $this->form->getLabel('group'); ?></td>
					<td><?php echo $this->form->getInput('group'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('bcc'); ?></td>
					<td><?php echo $this->form->getInput('bcc'); ?></td>
				</tr>
			</table>
		</fieldset>
	</div>

	<div class="col width-70">
		<fieldset class="adminform">
			<legend><?php echo JText::_('MassMail_Message'); ?></legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getLabel('subject'); ?></td>
					<td><?php echo $this->form->getInput('subject'); ?></td>
				</tr>
				<tr>
					<td class="key" valign="top"><?php echo $this->form->getLabel('message'); ?></td>
					<td><?php echo $this->form->getInput('message'); ?></td>
				</tr>
			</table>
		</fieldset>
	</div>
	
	<div class="clr"></div>

	<input type="hidden" name="option" value="com_massmail" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>