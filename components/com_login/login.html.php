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
class loginHTML {

	function loginpage ( &$params, $image ) {
		global $mosConfig_lang;

		$return = $params->get('login');
		?>
		<form action="<?php echo sefRelToAbs( 'index.php?option=login' ); ?>" method="post" name="login" id="login">
		<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td colspan="2">
			<?php
			if ( $params->get( 'page_title' ) ) {
				?>
				<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $params->get( 'header_login' ); ?>
				</div>
				<?php
			}
			?>
			<div>
			<?php echo $image; ?>
			<?php
			if ( $params->get( 'description_login' ) ) {
				 ?>
				<?php echo $params->get( 'description_login_text' ); ?>
				<br/><br/>
				<?php
			}
			?>
			</div>
			</td>
		</tr>
		<tr>
			<td align="center" width="50%">
				<br />
				<table>
				<tr>
					<td align="center">
					<?php echo _USERNAME; ?>
					<br />
					</td>
					<td align="center">
					<?php echo _PASSWORD; ?>
					<br />
					</td>
				</tr>
				<tr>
					<td align="center">
					<input name="username" type="text" class="inputbox" size="20" />
					</td>
					<td align="center">
					<input name="passwd" type="password" class="inputbox" size="20" />
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2">
					<br/>
					<?php echo _REMEMBER_ME; ?>
					<input type="checkbox" name="remember" class="inputbox" value="yes" />
					<br/>
					<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=lostPassword' ); ?>">
					<?php echo _LOST_PASSWORD; ?>
					</a>
					<?php
					if ( $params->get( 'registration' ) ) {
						?>
						<br/>
						<?php echo _NO_ACCOUNT; ?>
						<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>">
						<?php echo _CREATE_ACCOUNT;?>
						</a>
						<?php
					}
					?>
					<br/><br/><br/>
					</td>
				</tr>
				</table>
			</td>
			<td>
			<div align="center">
			<input type="submit" name="submit" class="button" value="<?php echo _BUTTON_LOGIN; ?>" />
			</div>

			</td>
		</tr>
		<tr>
			<td colspan="2">
			<noscript>
			<?php echo _CMN_JAVASCRIPT; ?>
			</noscript>
			</td>
		</tr>
		</table>
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
		?>

		<input type="hidden" name="op2" value="login" />
		<input type="hidden" name="return" value="<?php echo sefRelToAbs( $return ); ?>" />
		<input type="hidden" name="lang" value="<?php echo $mosConfig_lang; ?>" />
		<input type="hidden" name="message" value="<?php echo $params->get( 'login_message' ); ?>" />
		</form>
		<?php
  	}

	function logoutpage( &$params, $image ) {
		global $mosConfig_lang;

		$return = $params->get('logout');
		?>
		<form action="<?php echo sefRelToAbs( 'index.php?option=logout' ); ?>" method="post" name="login" id="login">
			<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td valign="top">
			<?php
			if ( $params->get( 'page_title' ) ) {
				?>
				<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $params->get( 'header_logout' ); ?>
				</div>
				<?php
			}
			?>
			<div>
			<?php
			echo $image;

			if ( $params->get( 'description_logout' ) ) {
				echo $params->get( 'description_logout_text' );
				?>
				<br/><br/>
				<?php
			}
			?>
			</div>
			</td>
		</tr>
		<tr>
			<td align="center">
			<div align="center">
			<input type="submit" name="Submit" class="button" value="<?php echo _BUTTON_LOGOUT; ?>" />
			</div>
			</td>
		</tr>
		</table>
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
		?>

		<input type="hidden" name="op2" value="logout" />
		<input type="hidden" name="return" value="<?php echo sefRelToAbs( $return ); ?>" />
		<input type="hidden" name="lang" value="<?php echo $mosConfig_lang; ?>" />
		<input type="hidden" name="message" value="<?php echo $params->get( 'logout_message' ); ?>" />
		</form>
	<?php
	}
}
?>