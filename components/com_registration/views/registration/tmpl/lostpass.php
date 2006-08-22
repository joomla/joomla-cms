<script language="javascript" type="text/javascript">
function submitbutton( pressbutton ) {
	var form = document.josForm;

	if (pressbutton == 'cancel') {
		form.task.value = 'cancel';
		form.submit();
	}

	form.submit();
}
</script>
<form action="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=lostPassword' ); ?>" method="post" name="josForm">

<div class="componentheading">
	<?php echo JText::_( 'Lost your Password?' ); ?>
</div>

<div style="float: right;">
	<?php
	mosToolBar::startTable();
	mosToolBar::spacer();
	mosToolBar::save('sendNewPass');
	mosToolBar::cancel();
	mosToolBar::endtable();
	?>
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
	<td colspan="2" height="40">
		<?php echo JText::_( 'NEW_PASS_DESC' ); ?>
	</td>
</tr>
<tr>
	<td colspan="2" height="40">
		<?php echo JText::_( 'USER_UNKNOWN_DESC' ); ?>
	</td>
</tr>
<tr>
	<td height="40">
		<label for="checkusername">
			<?php echo JText::_( 'Username' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="checkusername" name="checkusername" class="inputbox" size="40" maxlength="25" />
	</td>
</tr>
<tr>
	<td height="40">
		<label for="userunknown">
			<?php echo JText::_( 'User unknown' ); ?>:
		</label>
	</td>
	<td>
		<input type="checkbox" id="userunknown" name="userunkown" class="inputbox" value="1" />
	</td>
</tr>
<tr>
	<td height="40">
		<label for="confirmEmail">
			<?php echo JText::_( 'Email Address' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="confirmEmail" name="confirmEmail" class="inputbox" size="40" />
	</td>
</tr>
</table>

<input type="hidden" name="task" value="sendNewPass" />
<input type="hidden" name="<?php echo JUtility::spoofKey(); ?>" value="1" />