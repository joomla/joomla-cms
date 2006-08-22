<script language="javascript" type="text/javascript">
function submitbutton( pressbutton ) {
	var form = document.josForm;
	var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

	if (pressbutton == 'cancel') {
		form.task.value = 'cancel';
		form.submit();
		return;
	}

	// do field validation
	if (form.name.value == "") {
		alert( "<?php echo JText::_( 'Please enter your name.', true );?>" );
	} else if (form.username.value == "") {
		alert( "<?php echo JText::_( 'Please enter a user name.', true );?>" );
	} else if (r.exec(form.username.value) || form.username.value.length < 3) {
		alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Username', true ), 2 );?>" );
	} else if (form.email.value == "") {
		alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
	} else if (form.password.value.length < 6) {
		alert( "<?php echo JText::_( 'REGWARN_PASS', true );?>" );
	} else if (form.password2.value == "") {
		alert( "<?php echo JText::_( 'Please verify the password.', true );?>" );
	} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
		alert( "<?php echo JText::_( 'REGWARN_VPASS2', true );?>" );
	} else if (r.exec(form.password.value)) {
		alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Password', true ), 6 );?>" );
	} else {
		form.submit();
	}
}
</script>
<form action="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>" method="post" name="josForm">

<div class="componentheading">
	<?php echo JText::_( 'Registration' ); ?>
</div>

<div style="float: right;">
	<?php
	mosToolBar::startTable();
	mosToolBar::spacer();
	mosToolBar::save('save');
	mosToolBar::cancel();
	mosToolBar::endtable();
	?>
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
	<td width="30%" height="40">
		<label for="name">
			<?php echo JText::_( 'Name' ); ?>:
		</label>
	</td>
  	<td>
  		<input type="text" name="name" id="name" size="40" value="<?php echo $this->user->get( 'name' );?>" class="inputbox" maxlength="50" /> *
  	</td>
</tr>
<tr>
	<td height="40">
		<label for="username">
			<?php echo JText::_( 'Username' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="username" name="username" size="40" value="<?php echo $this->user->get( 'username' );?>" class="inputbox" maxlength="25" /> *
	</td>
<tr>
	<td height="40">
		<label for="email">
			<?php echo JText::_( 'Email' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="email" name="email" size="40" value="<?php echo $this->user->get( 'email' );?>" class="inputbox" maxlength="100" /> *
	</td>
</tr>
<tr>
	<td height="40">
		<label for="password">
			<?php echo JText::_( 'Password' ); ?>:
		</label>
	</td>
  	<td>
  		<input class="inputbox" type="password" id="password" name="password" size="40" value="" /> *
  	</td>
</tr>
<tr>
	<td height="40">
		<label for="password2">
			<?php echo JText::_( 'Verify Password' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="password" id="password2" name="password2" size="40" value="" /> *
	</td>
</tr>
<tr>
	<td colspan="2" height="40">
		<?php echo JText::_( 'REGISTER_REQUIRED' ); ?>
	</td>
</tr>
</table>

<input type="hidden" name="id" value="0" />
<input type="hidden" name="gid" value="0" />
<input type="hidden" name="task" value="saveRegistration" />
<input type="hidden" name="<?php echo JUtility::spoofKey(); ?>" value="1" />
</form>
