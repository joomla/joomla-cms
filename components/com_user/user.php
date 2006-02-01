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

// Editor usertype check
$access = new stdClass();
$access->canEdit = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'all' );
$access->canEditOwn = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'own' );

require_once ( JApplicationHelper::getPath( 'front_html' ) );

$breadcrumbs =& $mainframe->getPathWay();
$breadcrumbs->setItemName(1, 'User');

switch( $task ) {
	case 'saveUpload':
		saveUpload( $mosConfig_dbprefix, $uid, $option, $userfile, $userfile_name, $type, $existingImage );
		break;

	case 'UserDetails':
		userEdit( $option, $my->id, JText::_( 'Update' ) );
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

function userEdit( $option, $uid, $submitvalue) {
	global $mainframe, $Itemid;

	$database 			= & $mainframe->getDBO();
	$breadcrumbs 		= & $mainframe->getPathWay();
	
	require_once( JPATH_ADMINISTRATOR .'/components/com_users/users.class.php' );

	if ($uid == 0) {
		mosNotAuth();
		return;
	}
	$row =& JModel::getInstance('user', $database );
	$row->load( $uid );
	$row->orig_password = $row->password;

	$file 	= JApplicationHelper::getPath( 'com_xml', 'com_users' );
	$params = new JParameter( $row->params, $file, 'component' );
	
	$menu =& JModel::getInstance('menu', $database );
	$menu->load( $Itemid );
	
	// Set page title
	$mainframe->setPageTitle( $menu->name );
	
	// Add breadcrumb
	$breadcrumbs->addItem( $menu->name, '' );

	HTML_user::userEdit( $row, $option, $submitvalue, $params );
}

function userSave( $option, $uid) {
	global $mainframe, $database, $Itemid;

	$user_id = intval( mosGetParam( $_POST, 'id', 0 ));

	// do some security checks
	if ($uid == 0 || $user_id == 0 || $user_id <> $uid) {
		mosNotAuth();
		return;
	}
	$row =& JModel::getInstance('user', $database );
	$row->load( $user_id );
	$row->orig_password = $row->password;

	mosMakeHtmlSafe($row);

	if (!$row->bind( $_POST, "gid usertype" )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if(isset($_POST["password"]) && $_POST["password"] != "") {
		if(isset($_POST["verifyPass"]) && ($_POST["verifyPass"] == $_POST["password"])) {
			$row->password = md5($_POST["password"]);
		} else {
			echo "<script> alert(\"". JText::_( 'Passwords do not match', true ) ."\"); window.history.go(-1); </script>\n";
			exit();
		}
	} else {
		// Restore 'original password'
		$row->password = $row->orig_password;
	}

	// save params
	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$row->params = implode( "\n", $txt );
	}

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	//trigger the onBeforeStoreUser event
	JPluginHelper::importGroup( 'user' );
	$results = $mainframe->triggerEvent( 'onBeforeStoreUser', array(get_object_vars($row), false));

	unset($row->orig_password); // prevent DB error!!

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	//trigger the onAfterStoreUser event
	$results = $mainframe->triggerEvent( 'onAfterStoreUser', array(get_object_vars($row), false, true, null ));

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
