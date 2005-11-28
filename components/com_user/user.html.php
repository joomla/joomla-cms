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
class HTML_user {
	function frontpage() {
		?>
		<div class="componentheading">
			<?php echo JText::_( 'Welcome!' ); ?>
		</div>

		<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td>
				<?php echo JText::_( 'WELCOME_DESC' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	function userEdit( $row, $option, $submitvalue, &$params ) {
		global $mosConfig_absolute_path;

		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );

		mosCommonHTML::loadOverlib();
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton( pressbutton ) {
			var form = document.mosUserForm;
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
				alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Username', true ), 4 );?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
			} else if ((form.password.value != "") && (form.password.value != form.verifyPass.value)){
				alert( "<?php echo JText::_( 'REGWARN_VPASS2', true );?>" );
			} else if (r.exec(form.password.value)) {
				alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Password', true ), 4 );?>" );
			} else {
				form.submit();
			}
		}
		</script>
		<form action="index.php" method="post" name="mosUserForm">
		<div class="componentheading">
			<?php echo JText::_( 'Edit Your Details' ); ?>
		</div>

		<div style="float: right;">
			<?php
			mosToolBar::startTable();
			mosToolBar::spacer();
			mosToolBar::save();
			mosToolBar::cancel();
			mosToolBar::endtable();
			?>
		</div>

		<table cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr>
			<td width=85>
				<?php echo JText::_( 'Your Name' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="name" value="<?php echo $row->name;?>" size="40" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'email' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="email" value="<?php echo $row->email;?>" size="40" />
			</td>
		<tr>
			<td>
				<?php echo JText::_( 'User Name' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="text" name="username" value="<?php echo $row->username;?>" size="40" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Password' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="password" name="password" value="" size="40" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Verify Password' ); ?>:
			</td>
			<td>
				<input class="inputbox" type="password" name="verifyPass" size="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php echo $params->render( 'params' ); ?>
			</td>
		</tr>
		<!--
		<tr>
			<td colspan="2">
				<input class="button" type="button" value="<?php echo $submitvalue; ?>" onclick="submitbutton()" />
			</td>
		</tr>
		-->
		</table>

		<input type="hidden" name="id" value="<?php echo $row->id;?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>">
		<input type="hidden" name="task" value="saveUserEdit" />
		</form>
		<?php
	}

	function confirmation() {
		?>
		<div class="componentheading">
			<?php echo JText::_( 'Submission Success!' ); ?>
		</div>

		<table>
		<tr>
			<td>
				<?php echo JText::_( 'SUBMIT_SUCCESS_DESC' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}
}
?>
