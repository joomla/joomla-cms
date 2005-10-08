<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/*
 * phpGACL - Generic Access Control List
 * Copyright (C) 2002,2003 Mike Benoit
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * phpGACL mailing list. http://sourceforge.net/mail/?group_id=57103
 *
 * You may contact the author of phpGACL by e-mail at:
 * ipso@snappymail.ca
 *
 * The latest version of phpGACL can be obtained from:
 * http://phpgacl.sourceforge.net/
 *
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// NOTE, this is a temporary solution until phpGACL libraries are fully implemented

/* -- Code to manually add a group to the ARO Groups
SET @parent_name = 'Registered';
SET @new_name = 'Support';

-- Select the parent node to insert after
SELECT @ins_id := group_id, @ins_lft := lft, @ins_rgt := rgt
FROM jos_core_acl_aro_groups
WHERE name = @parent_name;

SELECT @new_id := MAX(group_id) + 1 FROM jos_core_acl_aro_groups;

-- Make room for the new node
UPDATE jos_core_acl_aro_groups SET rgt=rgt+2 WHERE rgt>=@ins_rgt;
UPDATE jos_core_acl_aro_groups SET lft=lft+2 WHERE lft>@ins_rgt;

-- Insert the new node
INSERT INTO jos_core_acl_aro_groups (group_id,parent_id,name,lft,rgt)
VALUES (@new_id,@ins_id,@new_name,@ins_rgt,@ins_rgt+1);
*/

class gacl {

	// --- Private properties ---

	/*
	 * Enable Debug output.
	 */
	var $_debug = FALSE;

	/*
	 * Database configuration.
	 */
	var $db=null;
	var $_db_table_prefix = '#__core_acl_';

	/*
	 * NOTE: 	This cache must be manually cleaned each time ACL's are modified.
	 * 		Alternatively you could wait for the cache to expire.
	 */
	var $_caching = FALSE;
	var $_force_cache_expire = TRUE;

	// --- Fudge properties
	var $acl=null;
	var $acl_count=0;

	/*
	 * Constructor
	 */
	function gacl( $db=null ) {
		global $database;

		$this->db = $db ? $db : $database;

		// ARO value is currently the user type,
		// this changes to user id in proper implementation
		// No hierarchial inheritance so have to do that the long way
		$this->acl = array();

		// backend login
		$this->_mos_add_acl( 'administration', 'login', 'users', 'administrator', null, null );
		$this->_mos_add_acl( 'administration', 'login', 'users', 'super administrator', null, null );
		$this->_mos_add_acl( 'administration', 'login', 'users', 'manager', null, null );
		// backend menus
		//$this->_mos_add_acl( 'administration', 'config', 'users', 'administrator', null, null );
		$this->_mos_add_acl( 'administration', 'config', 'users', 'super administrator', null, null );

		// access to db admin
		//$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_dbadmin' );

		// access to templates
		//$this->_mos_add_acl( 'administration', 'manage', 'user', 'administrator', 'components', 'com_templates' )
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_templates' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'super administrator', 'templates', 'all' );

		// access to trash
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'administrator', 'components', 'com_trash' );
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_trash' );

		// access to menu manager
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'administrator', 'components', 'com_menumanager' );
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_menumanager' );

		// access to language
		//$this->_mos_add_acl( 'administration', 'manage', 'users', 'administrator', 'components', 'com_languages' );
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_languages' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'super administrator', 'languages', 'all' );

		// access to modules
		$this->_mos_add_acl( 'administration', 'install', 'users', 'administrator', 'modules', 'all' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'super administrator', 'modules', 'all' );

		$this->_mos_add_acl( 'administration', 'edit', 'users', 'super administrator', 'modules', 'all' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'administrator', 'modules', 'all' );

		// access to modules
		$this->_mos_add_acl( 'administration', 'install', 'users', 'administrator', 'mambots', 'all' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'super administrator', 'mambots', 'all' );

		$this->_mos_add_acl( 'administration', 'edit', 'users', 'super administrator', 'mambots', 'all' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'administrator', 'mambots', 'all' );
		// uncomment following to allow managers to edit modules
		//array( 'administration', 'edit', 'users', 'manager', 'modules', 'all' );

		// access to components
		$this->_mos_add_acl( 'administration', 'install', 'users', 'administrator', 'components', 'all' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'super administrator', 'components', 'all' );

		$this->_mos_add_acl( 'administration', 'edit', 'users', 'super administrator', 'components', 'all' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'administrator', 'components', 'all' );

		$this->_mos_add_acl( 'administration', 'edit', 'users', 'manager', 'components', 'com_newsflash' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'manager', 'components', 'com_frontpage' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'manager', 'components', 'com_media' );
			// ** add additional components for a manager as desired, or give access to all

		// massmail
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_massmail' );

		// manage users
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'administrator', 'components', 'com_users' );
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_users' );

		// email block users property
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'administrator', 'user properties', 'block_user' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'super administrator', 'user properties', 'block_user' );

		// email system events
		$this->_mos_add_acl( 'workflow', 'email_events', 'users', 'administrator', null, null );
		$this->_mos_add_acl( 'workflow', 'email_events', 'users', 'super administrator', null, null );

		// actions
		$this->_mos_add_acl( 'action', 'add', 'users', 'author', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'add', 'users', 'editor', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'add', 'users', 'publisher', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'author', 'content', 'own' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'editor', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'publisher', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'publish', 'users', 'publisher', 'content', 'all' );

		$this->_mos_add_acl( 'action', 'add', 'users', 'manager', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'manager', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'publish', 'users', 'manager', 'content', 'all' );

		$this->_mos_add_acl( 'action', 'add', 'users', 'administrator', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'administrator', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'publish', 'users', 'administrator', 'content', 'all' );

		$this->_mos_add_acl( 'action', 'add', 'users', 'super administrator', 'content', 'all' );
		$this->_mos_add_acl( 'action', 'edit', 'users', 'super administrator', 'content', 'all' );

		$this->_mos_add_acl( 'action', 'publish', 'users', 'super administrator', 'content', 'all' );

		$this->acl_count = count( $this->acl );
	}

	/*
		This is a temporary function to allow 3PD's to add basic ACL checks for their
		modules and components.  NOTE: this information will be compiled in the db
		in future versions
	*/
	function _mos_add_acl( $aco_section_value, $aco_value,
		$aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL ) {

		$this->acl[] = array( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value );
		$this->acl_count = count( $this->acl );
	}

	/*======================================================================*\
		Function:   $gacl_api->debug_text()
		Purpose:	Prints debug text if debug is enabled.
	\*======================================================================*/
	function debug_text($text) {

		if ($this->_debug) {
			echo "$text<br>\n";
		}

		return true;
	}

	/*======================================================================*\
		Function:   $gacl_api->debug_db()
		Purpose:	Prints database debug text if debug is enabled.
	\*======================================================================*/
	function debug_db($function_name = '') {
		if ($function_name != '') {
			$function_name .= ' (): ';
		}

		return $this->debug_text ($function_name .'database error: '. $this->db->getErrorMsg() .' ('. $this->db->getErrorNum() .')');
	}

	/*======================================================================*\
		Function:   acl_check()
		Purpose:	Function that wraps the actual acl_query() function.
						It is simply here to return TRUE/FALSE accordingly.
	\*======================================================================*/
	function acl_check( $aco_section_value, $aco_value,
		$aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL ) {

		$acl_result = 0;
		for ($i=0; $i < $this->acl_count; $i++) {
			if (strcasecmp( $aco_section_value, $this->acl[$i][0] ) == 0) {
				if (strcasecmp( $aco_value, $this->acl[$i][1] ) == 0) {
					if (strcasecmp( $aro_section_value, $this->acl[$i][2] ) == 0) {
						if (strcasecmp( $aro_value, $this->acl[$i][3] ) == 0) {
							if (strcasecmp( $axo_section_value, $this->acl[$i][4] ) == 0) {
								if (strcasecmp( $axo_value, $this->acl[$i][5] ) == 0) {
									$acl_result = 1;
									break;
								}
							}
						}
					}
				}
			}
		}
		return $acl_result;
	}

}

?>