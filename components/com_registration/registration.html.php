<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Users
*/
class HTML_registration {
	
	/**
	 * Shows the lost passowrd form
	 * @return void
	 */
	function lostPassForm() {
		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );
		?>
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
		</form>
		<?php
	}

	/**
	 * Shows the registration form
	 * @param JUser		reference to the actual user information
	 * @return void
	 */
	function registerForm( $user ) {
		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );
		?>
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
			mosToolBar::save('saveRegistration');
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
		  		<input type="text" name="name" id="name" size="40" value="<?php echo $user->get( 'name' );?>" class="inputbox" maxlength="50" /> *
		  	</td>
		</tr>
		<tr>
			<td height="40">
				<label for="username">
					<?php echo JText::_( 'Username' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" id="username" name="username" size="40" value="<?php echo $user->get( 'username' );?>" class="inputbox" maxlength="25" /> *
			</td>
		<tr>
			<td height="40">
				<label for="email">
					<?php echo JText::_( 'Email' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" id="email" name="email" size="40" value="<?php echo $user->get( 'email' );?>" class="inputbox" maxlength="100" /> *
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
		</form>
		<?php
	}
	
	/**
	 * Shows the component message
	 *
	 * @param string $title
	 * @param string $text
	 * @return void
	 */
	function message( $title, $text ) {
		?>
		<div class="componentheading">
			<?php echo JText::_( $title ); ?>
		</div>
		
		<div>
			<?php echo JText::_( $text ); ?>
		</div>
		<?php
	}
	
	/**
	 * Shows the component message
	 *
	 * @param string $title
	 * @param string $text
	 * @return void
	 */
	function errorMessage( $title, $text ) {
		?>
		<div class="componentheading">
			<?php echo JText::_( $title ); ?>
		</div>
		
		<div class="message">
			<?php echo JText::_( $text ); ?>
		</div>
		<?php
	}}
?>