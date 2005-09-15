<?php
/**
* @version $Id: admin.syndicate.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Syndicate
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!($acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' )
 | $acl->acl_check( 'com_syndicate', 'manage', 'users', $my->usertype ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@admin_html' );

/**
 * @package Syndicate
 * @subpackage Syndicate
 */
class syndicateTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function syndicateTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'edit' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	/**
	* List the records
	*/
	function edit() {
		global $database, $mainframe;

		$mainframe->set('disableMenu', true);

		$query = "SELECT a.id"
		. "\n FROM #__components AS a"
		. "\n WHERE a.option = 'com_syndicate'"
		;
		$database->setQuery( $query );
		$id = $database->loadResult();

		// load the row from the db table
		$row = new mosComponent( $database );
		$row->load( $id );

		// get params definitions
		$params = new mosParameters( $row->params, $mainframe->getPath( 'com_xml', $row->option ), 'component' );

		HTML_syndicate::settings( 'com_syndicate', $params, $id );
	}

	/**
	* Saves the record from an edit form submit
	*/
	function save() {
		global $database;
		global $_LANG;

		$id 	= intval( mosGetParam( $_POST, 'id', '17' ) );
		$params = mosGetParam( $_POST, 'params', '' );

		if (is_array( $params )) {
			$txt = array();
			foreach ($params as $k=>$v) {
				$txt[] = "$k=$v";
			}
			$_POST['params'] = mosParameters::textareaHandling( $txt );
		}

		$row = new mosComponent( $database );
		$row->load( $id );

		if (!$row->bind( $_POST )) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}

		$msg = $_LANG->_( 'Settings successfully Saved' );
		mosRedirect( 'index2.php?option=com_syndicate', $msg );
	}

	/**
	* Cancels editing returns to Admin Home page
	*/
	function cancel() {
		$this->setRedirect( 'index2.php' );
	}
}

$tasker = new syndicateTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>