<?php
/**
* @version $Id: user.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package User
 * @subpackage User
 */
class userTasks_Front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function userTasks_Front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'frontpage' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );

		// additional mappings
		$this->registerTask( 'saveUserEdit', 'UserSave' );
	}

	function frontpage() {
		mosFS::load( '@front_html' );

		userScreens_front::welcome();
	}

	function UserDetails() {
		global $mosConfig_name_change, $mosConfig_username_change, $mosConfig_password_length, $mosConfig_username_length, $mosConfig_user_params;
		global $mosConfig_live_site;
		global $database, $mainframe;
		global $option, $my;

		$uid = $my->id;

		if ( $uid == 0 ) {
			mosNotAuth();
			return;
		}

		mosFS::load( '@toolbar_front' );
		mosFS::load( 'administrator/components/com_users/users.class.php' );

		$row = new mosUser( $database );
		$row->load( $uid );

		$file = $mainframe->getPath( 'com_xml', 'com_users' );
		$params = new mosUserParameters( $row->params, $file, 'component' );

		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$list->length_name = $mosConfig_username_length;
		$list->length_pass = $mosConfig_password_length;
		$list->show_params = $mosConfig_user_params;

		//toolbar css file
		$css = $mosConfig_live_site .'/includes/HTML_toolbar.css';
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="'. $css .'" type="text/css" />' );

		mosFS::load( '@front_html' );

		userScreens_front::edit( $row, $params, $list );
	}

	function userSave() {
		global $database, $Itemid, $my;
		global $_LANG, $_MAMBOTS;

		$uid 		= $my->id;
		$user_id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		// do some security checks
		if ($uid == 0 || $user_id == 0 || $user_id <> $uid) {
			mosNotAuth();
			return;
		}

		$row = new mosUser( $database );
		$row->load( $user_id );
		$orig_password = $row->password;

		if ( !$row->bind( $_POST, 'gid usertype' ) ) {
			mosErrorAlert( $row->getError() );
		}

		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		// save params
		$params = mosGetParam( $_POST, 'params', '' );
		if ( is_array( $params ) ) {
			$txt = array();
			foreach ( $params as $k=>$v) {
				$txt[] = "$k=$v";
			}
			$row->params = implode( "\n", $txt );
		}

		// password handling
		if( isset( $_POST['password'] ) && isset( $_POST['verifyPass'] ) && $_POST['password'] != '' ) {
			// verify passwords match
			if(  ( $_POST['verifyPass'] == $_POST['password'] ) ) {
				// convert password via md5 hashing
				$row->password = md5( $_POST['password'] );
			} else {
				mosErrorAlert( $_LANG->_( 'errorPasswordMatch' ) );
			}
		} else {
			// Restore 'original password'
			$row->password = $orig_password;
		}

		if ( !$row->check() ) {
			mosErrorAlert( $row->getError() );
		}

		//trigger the onBeforeStoreUser event
		$results = $_MAMBOTS->trigger( 'onBeforeStoreUser', array(get_object_vars($row), false));

		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}

		//trigger the onAfterStoreUser event
		$results = $_MAMBOTS->trigger( 'onAfterStoreUser', array(get_object_vars($row), false, true, null ));

		mosRedirect( 'index.php?option=com_user&task=UserDetails&Itemid='. $Itemid, $_LANG->_( 'USER_DETAILS_SAVE' ) );
	}

	function CheckIn() {
		global $database, $acl, $my;
		global $mosConfig_db, $mosConfig_zero_date;
		global $_LANG;

		$userid = intval( mosGetParam( $_REQUEST, 'id', 0 ));

		$access = new stdClass();
		$access->canEdit 	= $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'all' );
		$access->canEditOwn = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'own' );

		if (!($access->canEdit || $access->canEditOwn || $userid > 0)) {
			mosNotAuth();
			return;
		}

		$lt = mysql_list_tables( $mosConfig_db );
		$k = 0;
		$i = 0;
		while ( list( $tn ) = mysql_fetch_array( $lt ) ) {
			// only check in the mos_* tables
			if ( strpos( $tn, $database->_table_prefix ) !== 0 ) {
				continue;
			}
			$lf = mysql_list_fields( $mosConfig_db, $tn );
			$nf = mysql_num_fields($lf);

			$checked_out 	= false;
			$editor 		= false;

			for ( $i = 0; $i < $nf; $i++ ) {
				$fname = mysql_field_name($lf, $i);
				if ( $fname == 'checked_out' ) {
					$checked_out = true;
				} else if ( $fname == 'editor' ) {
					$editor = true;
				}
			}

			if ( $checked_out ) {
				if ( $editor ) {
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

				if ( $editor ) {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$mosConfig_zero_date', editor = NULL"
					. "\n WHERE checked_out > 0"
					;
					$database->setQuery( $query );
				} else {
					$query = "UPDATE $tn"
					. "\n SET checked_out = 0, checked_out_time = '$mosConfig_zero_date'"
					. "\n WHERE checked_out > 0"
					;
					$database->setQuery( $query );
				}
				$res = $database->query();

				if ( $res == 1 ) {
					if ( $num > 0 ) {
						$rows[$i]->class 	= $k;
						$rows[$i]->num 		= $num;
						$rows[$i]->table	= $tn;
						$i++;
					}
					$k = 1 - $k;
				}
			}
		}

		mosFS::load( '@front_html' );

		userScreens_front::checkin( $rows );
	}

	function cancel() {
		mosRedirect( 'index.php' );
	}
}

$tasker = new userTasks_Front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>