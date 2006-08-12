<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Get user object for current logged in user
$user	= & JFactory::getUser();

// Editor usertype check
$access = new stdClass();
$access->canEdit = $user->authorize( 'action', 'edit', 'content', 'all' );
$access->canEditOwn = $user->authorize( 'action', 'edit', 'content', 'own' );

// Load the html output class
require_once ( JApplicationHelper::getPath( 'front_html' ) );

// Set the component name in the pathway
$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'User');

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch( JRequest::getVar( 'task' ) )
{
	case 'saveUpload':
		$dbprefix = $mainframe->getCfg( 'dbprefix' );
		UserController::upload( $dbprefix, $uid, $option, $userfile, $userfile_name, $type, $existingImage );
		break;

	case 'UserDetails':
		UserController::edit( $option, JText::_( 'Update' ) );
		break;

	case 'saveUserEdit':
		UserController::save( $option, $user->get('id') );
		break;

	case 'CheckIn':
		UserController::checkin( $user->get('id'), $access, $option );
		break;

	case 'cancel':
		$mainframe->redirect( 'index.php' );
		break;

	default:
		HTML_user::frontpage();
		break;
}

/**
 * Static class to hold controller functions for the User component
 *
 * @static
 * @author		Louis Landry <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	User
 * @since		1.5
 */
class UserController
{
	function upload( $_dbprefix, $uid, $option, $userfile, $userfile_name, $type, $existingImage )
	{
		// Protect against simple spoofing attacks
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}

		// Initialize some variables
		$db = & JFactory::getDBO();

		if ($uid == 0) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		$base_Dir 	= 'images/stories/';
		$checksize	= filesize($userfile);

		if ($checksize > 50000)
		{
			echo "<script> alert(\"". JText::_( 'UP_SIZE' ) ."\"); window.history.go(-1); </script>\n";
		}
		else
		{
			if (file_exists($base_Dir.$userfile_name)) {
				$message= JText::_( 'UP_EXISTS', true );
				eval ("\$message = \"$message\";");
				print "<script> alert('$message'); window.history.go(-1);</script>\n";
			}
			else
			{
				if ((!strcasecmp(substr($userfile_name,-4),".gif")) || (!strcasecmp(substr($userfile_name,-4),".jpg")))
				{
					if (!move_uploaded_file($userfile, $base_Dir.$userfile_name))
					{
						echo JText::_( 'Failed to copy' ) ." $userfile_name";
					}
					else
					{
						echo "<script>window.opener.focus;</script>";
						if ($type=="news") {
							$op="UserNews";
						} elseif ($type=="articles") {
							$op="UserArticle";
						}

						if ($existingImage!="") {
							if (file_exists($base_Dir.$existingImage)) {
								//delete the exisiting file
								unlink($base_Dir.$existingImage);
							}
						}
						echo "<script>window.opener.document.adminForm.ImageName.value='$userfile_name';</script>";
						echo "<script>window.opener.document.adminForm.ImageName2.value='$userfile_name';</script>";
						echo "<script>window.opener.document.adminForm.imagelib.src=null;</script>";
						echo "<script>window.opener.document.adminForm.imagelib.src='images/stories/$userfile_name';</script>";
						echo "<script>window.close(); </script>";
					}
				}
				else
				{
					echo "<script> alert(\"". JText::_( 'You may only upload a gif, or jpg image.', true ) ."\"); window.history.go(-1); </script>\n";
				}
			}
		}
	}

	function edit( $option, $submitvalue)
	{
		global $mainframe, $Itemid;

		$db 		 =& JFactory::getDBO();
		$breadcrumbs =& $mainframe->getPathWay();
		$user		 =& JFactory::getUser();

		// security check to see if link exists in a menu
		$link = 'index.php?option=com_user&task=CheckIn';
		$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE link LIKE '%$link%'"
			. "\n AND published = 1"
			;
		$db->setQuery( $query );
		$exists = $db->loadResult();
		if ( !$exists ) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		$menu =& JTable::getInstance('menu', $db );
		$menu->load( $Itemid );

		// Set page title
		$mainframe->setPageTitle( $menu->name );

		// Add breadcrumb
		$breadcrumbs->addItem( $menu->name, '' );

		HTML_user::userEdit( $user, $option, $submitvalue );
	}

	function save( $option, $uid)
	{
		global $mainframe;

		// Protect against simple spoofing attacks
		if (!JUtility::spoofCheck()) {
			JError::raiseWarning( 403, JText::_( 'E_SESSION_TIMEOUT' ) );
			return;
		}

		$db 	=& JFactory::getDBO();
		$user_id = JRequest::getVar( 'id', 0, 'post', 'int' );

		// do some security checks
		if ($uid == 0 || $user_id == 0 || $user_id <> $uid) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// do a password safety check
		if(isset($_POST["password"]) && $_POST["password"] != "") {
			if(!isset($_POST["verifyPass"]) && ($_POST["verifyPass"] == $_POST["password"])) {
				echo "<script> alert(\"". JText::_( 'Passwords do not match', true ) ."\"); window.history.go(-1); </script>\n";
				exit();
			}
		}

		$user = JUser::getInstance($user_id);
		$orig_username = $user->get('username');

		if (!$user->bind( $_POST )) {
			echo "<script> alert('".$user->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		if (!$user->save()) {
			echo "<script> alert('".$user->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}


		// check if username has been changed
		if ( $orig_username != $user->get('username') )
		{
			// change username value in session table
			$query = "UPDATE #__session"
				. "\n SET username = '$user->get('username')"
				. "\n WHERE username = '$orig_username'"
				. "\n AND userid = $user->get('id')"
				. "\n AND gid = $user->get('gid')"
				. "\n AND guest = 0"
				;
			$db->setQuery( $query );
			$db->query();

			JSession::set('username', $user->get('username'));
		}

		$link = $_SERVER['HTTP_REFERER'];
		$mainframe->redirect( $link, JText::_( 'Your settings have been saved.' ) );
	}

	function checkin( $userid, $access, $option )
	{
		global $mainframe;

		$db 	=& JFactory::getDBO();

		$nullDate = $db->getNullDate();
		if (!($access->canEdit || $access->canEditOwn || $userid > 0)) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		// security check to see if link exists in a menu
		$link = 'index.php?option=com_user&task=CheckIn';
		$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE link LIKE '%$link%'"
			. "\n AND published = 1"
		;
		$db->setQuery( $query );
		$exists = $db->loadResult();
		if ( !$exists ) {
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return;
		}

		$lt = mysql_list_tables($mainframe->getCfg('db'));
		$k = 0;
		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
		while (list($tn) = mysql_fetch_array($lt)) {
			// only check in the jos_* tables
			if (strpos( $tn, $db->_table_prefix ) !== 0) {
				continue;
			}
			$lf = mysql_list_fields($mainframe->getCfg('db'), "$tn");
			$nf = mysql_num_fields($lf);

			$checked_out = false;
			$editor = false;

			for ($i = 0; $i < $nf; $i++) {
				$fname = mysql_field_name($lf, $i);
				if ( $fname == "checked_out") {
					$checked_out = true;
				} else if ( $fname == "editor") {
					$editor = true;
				}
			}

			if ($checked_out)
			{
				if ($editor) {
					$query = "SELECT checked_out, editor"
					. "\n FROM $tn"
					. "\n WHERE checked_out > 0"
					. "\n AND checked_out = $userid"
					;
					$db->setQuery( $query );
				} else {
					$query = "SELECT checked_out"
					. "\n FROM $tn"
					. "\n WHERE checked_out > 0"
					. "\n AND checked_out = $userid"
					;
					$db->setQuery( $query );
				}
				$res = $db->query();
				$num = $db->getNumRows( $res );

				if ($editor) {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$nullDate', editor = NULL"
					. "\n WHERE checked_out > 0"
					;
					$db->setQuery( $query );
				} else {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$nullDate'"
					. "\n WHERE checked_out > 0"
					;
					$db->setQuery( $query );
				}
				$res = $db->query();

				if ($res == 1) {

					if ($num > 0) {
						echo "\n<tr class=\"row$k\">";
						echo "\n	<td width=\"250\">";
						echo JText::_( 'Checking table' );
						echo " - $tn</td>";
						echo "\n	<td>";
						echo JText::_( 'Checked in' ) ." <b>". $num ."</b> ". JText::_( 'items' );
						echo "</td>";
						echo "\n</tr>";
					}
					$k = 1 - $k;
				}
			}
		}
		?>
		<tr>
			<td colspan="2">
				<b><?php echo JText::_( 'CONF_CHECKED_IN' ); ?></b>
			</td>
		</tr>
		</table>
		<?php
	}
}
?>