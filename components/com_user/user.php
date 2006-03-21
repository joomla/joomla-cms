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
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Get user object for current logged in user
 */
$user	= & $mainframe->getUser();

// Editor usertype check
$access = new stdClass();
$access->canEdit = $user->authorize( 'action', 'edit', 'content', 'all' );
$access->canEditOwn = $user->authorize( 'action', 'edit', 'content', 'own' );

require_once ( JApplicationHelper::getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'User');

switch( $task ) {
	case 'saveUpload':
		saveUpload( $mosConfig_dbprefix, $uid, $option, $userfile, $userfile_name, $type, $existingImage );
		break;

	case 'UserDetails':
		userEdit( $option, JText::_( 'Update' ) );
		break;

	case 'saveUserEdit':
		userSave( $option, $my->id );
		break;

	case 'CheckIn':
		CheckIn( $my->id, $access, $option );
		break;

	case 'cancel':
		mosRedirect( 'index.php' );
		break;

	default:
		HTML_user::frontpage();
		break;
}

function saveUpload( $_dbprefix, $uid, $option, $userfile, $userfile_name, $type, $existingImage ) {
	global $database;

	if ($uid == 0) {
		mosNotAuth();
		return;
	}

	$base_Dir 	= 'images/stories/';
	$checksize	= filesize($userfile);

	if ($checksize > 50000) {
		echo "<script> alert(\"". JText::_( 'UP_SIZE' ) ."\"); window.history.go(-1); </script>\n";
	} else {
		if (file_exists($base_Dir.$userfile_name)) {
			$message= JText::_( 'UP_EXISTS', true );
			eval ("\$message = \"$message\";");
			print "<script> alert('$message'); window.history.go(-1);</script>\n";
		} else {
			if ((!strcasecmp(substr($userfile_name,-4),".gif")) || (!strcasecmp(substr($userfile_name,-4),".jpg"))) {
				if (!move_uploaded_file($userfile, $base_Dir.$userfile_name))
				{
					echo JText::_( 'Failed to copy' ) ." $userfile_name";
				} else {
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
			} else {
				echo "<script> alert(\"". JText::_( 'You may only upload a gif, or jpg image.', true ) ."\"); window.history.go(-1); </script>\n";
			}
		}
	}
}

function userEdit( $option, $submitvalue) 
{
	global $mainframe, $Itemid;

	$database 		=& $mainframe->getDBO();
	$breadcrumbs 	=& $mainframe->getPathWay();
	$user			=& $mainframe->getUser();	
	
	$menu =& JModel::getInstance('menu', $database );
	$menu->load( $Itemid );
	
	// Set page title
	$mainframe->setPageTitle( $menu->name );
	
	// Add breadcrumb
	$breadcrumbs->addItem( $menu->name, '' );

	HTML_user::userEdit( $user, $option, $submitvalue );
}

function userSave( $option, $uid) 
{
	global $mainframe, $database, $Itemid;

	$user_id = JRequest::getVar( 'id', 0, 'post', 'int' );

	// do some security checks
	if ($uid == 0 || $user_id == 0 || $user_id <> $uid) {
		mosNotAuth();
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

	if (!$user->bind( $_POST )) {
		echo "<script> alert('".$user->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$user->save()) {
		echo "<script> alert('".$user->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$link = $_SERVER['HTTP_REFERER'];
	mosRedirect( $link, JText::_( 'Your settings have been saved.' ) );
}

function CheckIn( $userid, $access, $option ){
	global $database;
	global $mosConfig_db;

	$nullDate = $database->getNullDate();
	if (!($access->canEdit || $access->canEditOwn || $userid > 0)) {
		mosNotAuth();
		return;
	}

	$lt = mysql_list_tables($mosConfig_db);
	$k = 0;
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
	while (list($tn) = mysql_fetch_array($lt)) {
		// only check in the jos_* tables
		if (strpos( $tn, $database->_table_prefix ) !== 0) {
			continue;
		}
		$lf = mysql_list_fields($mosConfig_db, "$tn");
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

		if ($checked_out) {
			if ($editor) {
				$query = "SELECT checked_out, editor"
				. "\n FROM $tn"
				. "\n WHERE checked_out > 0"
				. "\n AND checked_out = $userid"
				;
				$database->setQuery( $query );
			} else {
				$query = "SELECT checked_out"
				. "\n FROM $tn"
				. "\n WHERE checked_out > 0"
				. "\n AND checked_out = $userid"
				;
				$database->setQuery( $query );
			}
			$res = $database->query();
			$num = $database->getNumRows( $res );

			if ($editor) {
				$query = "UPDATE $tn"
				. "\n SET checked_out = 0, checked_out_time = '$nullDate', editor = NULL"
				. "\n WHERE checked_out > 0"
				;
				$database->setQuery( $query );
			} else {
				$query = "UPDATE $tn"
				. "\n SET checked_out = 0, checked_out_time = '$nullDate'"
				. "\n WHERE checked_out > 0"
				;
				$database->setQuery( $query );
			}
			$res = $database->query();

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
?>
