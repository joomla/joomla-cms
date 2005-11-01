<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Users
*/
class HTML_registration {
	function lostPassForm($option) {
		?>
		<form action="index.php" method="post">

		<div class="componentheading">
			<?php echo _PROMPT_PASSWORD; ?>
		</div>

		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2">
				<?php echo _NEW_PASS_DESC; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _PROMPT_UNAME; ?>
			</td>
			<td>
				<input type="text" name="checkusername" class="inputbox" size="40" maxlength="25" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _PROMPT_EMAIL; ?>
			</td>
			<td>
				<input type="text" name="confirmEmail" class="inputbox" size="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="option" value="<?php echo $option;?>" />
				<input type="hidden" name="task" value="sendNewPass" /> <input type="submit" class="button" value="<?php echo _BUTTON_SEND_PASS; ?>" />
			</td>
		</tr>
		</table>
		</form>
		<?php
	}

	function registerForm($option, $useractivation) {
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton() {
			var form = document.mosForm;
			var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_NAME);?>" );
			} else if (form.username.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_UNAME);?>" );
			} else if (r.exec(form.username.value) || form.username.value.length < 3) {
				alert( "<?php printf( html_entity_decode( $_LANG->_( 'VALID_AZ09' ) ), html_entity_decode(_PROMPT_UNAME), 2 );?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_MAIL);?>" );
			} else if (form.password.value.length < 6) {
				alert( "<?php echo html_entity_decode(_REGWARN_PASS);?>" );
			} else if (form.password2.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_VPASS1);?>" );
			} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
				alert( "<?php echo html_entity_decode(_REGWARN_VPASS2);?>" );
			} else if (r.exec(form.password.value)) {
				alert( "<?php printf( html_entity_decode(_VALID_AZ09), html_entity_decode(_REGISTER_PASS), 6 );?>" );
			} else {
				form.submit();
			}
		}
		</script>
		<form action="index.php" method="post" name="mosForm">

		<div class="componentheading">
			<?php echo _REGISTER_TITLE; ?>
		</div>

		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2"><?php echo _REGISTER_REQUIRED; ?></td>
		</tr>
		<tr>
			<td width="30%">
				<?php echo _REGISTER_NAME; ?> *
			</td>
		  	<td>
		  		<input type="text" name="name" size="40" value="" class="inputbox" />
		  	</td>
		</tr>
		<tr>
			<td>
				<?php echo _REGISTER_UNAME; ?> *
			</td>
			<td>
				<input type="text" name="username" size="40" value="" class="inputbox" />
			</td>
		<tr>
			<td>
				<?php echo _REGISTER_EMAIL; ?> *
			</td>
			<td>
				<input type="text" name="email" size="40" value="" class="inputbox" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _REGISTER_PASS; ?> *
			</td>
		  	<td>
		  		<input class="inputbox" type="password" name="password" size="40" value="" />
		  	</td>
		</tr>
		<tr>
			<td>
				<?php echo _REGISTER_VPASS; ?> *
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" size="40" value="" />
			</td>
		</tr>
		<tr>
			  <td colspan="2">
			  </td>
		</tr>
		<tr>
			<td colspan=2>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="0" />
		<input type="hidden" name="gid" value="0" />
		<input type="hidden" name="useractivation" value="<?php echo $useractivation;?>" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="saveRegistration" />
		<input type="button" value="<?php echo _BUTTON_SEND_REG; ?>" class="button" onclick="submitbutton()" />
		</form>
		<?php
	}
}
?>
