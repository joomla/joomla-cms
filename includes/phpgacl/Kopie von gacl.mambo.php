<?php
/**
* @version $Id: Kopie von gacl.mambo.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage phpGACL
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// NOTE, this is a temporary solution until phpGACL libraries are fully implemented

/* -- Code to manually add a group to the ARO Groups
SET @parent_name = 'Registered';
SET @new_name = 'Support';

-- Select the parent node to insert after
SELECT @ins_id := group_id, @ins_lft := lft, @ins_rgt := rgt
FROM mos_core_acl_aro_groups
WHERE name = @parent_name;

SELECT @new_id := MAX(group_id) + 1 FROM mos_core_acl_aro_groups;

-- Make room for the new node
UPDATE mos_core_acl_aro_groups SET rgt=rgt+2 WHERE rgt>=@ins_rgt;
UPDATE mos_core_acl_aro_groups SET lft=lft+2 WHERE lft>@ins_rgt;

-- Insert the new node
INSERT INTO mos_core_acl_aro_groups (group_id,parent_id,name,lft,rgt)
VALUES (@new_id,@ins_id,@new_name,@ins_rgt,@ins_rgt+1);
*/

/*
 * Path to ADODB.
 */
if ( !defined('ADODB_DIR') ) {
	define('ADODB_DIR', dirname(__FILE__).'/adodb');
}

/**
* @package Joomla
* @subpackage phpGACL
*/
class mambo_acl_api extends gacl_api {
	var $acl=null;
	var $acl_count=0;

	/**
	 * Constructor
	 * @param array An arry of options to oeverride the class defaults
	 */
	function mambo_acl_api($options = NULL) {
		parent::gacl( $options );

		// ARO value is currently the user type,
		// this changes to user id in proper implementation
		// No hierarchial inheritance so have to do that the long way
		$this->acl = array();

		// backend login
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'administrator', null, null );
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'super administrator', null, null );
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'manager', null, null );

		$this->_mos_add_acl( 'login', 'site', 'users', 'administrator', null, null );
		$this->_mos_add_acl( 'login', 'site', 'users', 'super administrator', null, null );
		$this->_mos_add_acl( 'login', 'site', 'users', 'manager', null, null );

		$this->_mos_add_acl( 'login', 'site', 'users', 'registered', null, null );
		$this->_mos_add_acl( 'login', 'site', 'users', 'author', null, null );
		$this->_mos_add_acl( 'login', 'site', 'users', 'editor', null, null );
		$this->_mos_add_acl( 'login', 'site', 'users', 'publisher', null, null );
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
		$this->_mos_add_acl( 'com_languages', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_languages', 'install', 'users', 'super administrator' );
		$this->_mos_add_acl( 'administration', 'install', 'users', 'administrator', 'languages', 'all' );
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
		$this->_mos_add_acl( 'com_massmail', 'manage', 'users', 'super administrator' );

		// manage users
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'administrator', 'components', 'com_users' );
		$this->_mos_add_acl( 'administration', 'manage', 'users', 'super administrator', 'components', 'com_users' );

		// manage checkin
		$this->_mos_add_acl( 'com_checkin', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_checkin', 'manage', 'users', 'super administrator' );

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

		return true;
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

	/**
	* Wraps the actual acl_query() function.
	*
	* It is simply here to return TRUE/FALSE accordingly.
	* @param string The ACO section value
	* @param string The ACO value
	* @param string The ARO section value
	* @param string The ARO section
	* @param string The AXO section value (optional)
	* @param string The AXO section value (optional)
	* @param integer The group id of the ARO ??Mike?? (optional)
	* @param integer The group id of the AXO ??Mike?? (optional)
	* @return mixed Generally a zero (0) or (1) or the extended return value of the ACL
	*/
	function acl_check( $aco_section_value, $aco_value,
		$aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL ) {

		$this->debug_text( "\n<br> ACO=$aco_section_value:$aco_value, ARO=$aro_section_value:$aro_value, AXO=$axo_section_value|$axo_value" );

		$acl_result = 0;
		for ($i=0; $i < $this->acl_count; $i++) {
			if (strcasecmp( $aco_section_value, $this->acl[$i][0] ) == 0) {
				if (strcasecmp( $aco_value, $this->acl[$i][1] ) == 0) {
					if (strcasecmp( $aro_section_value, $this->acl[$i][2] ) == 0) {
						if (strcasecmp( $aro_value, $this->acl[$i][3] ) == 0) {
							if ($axo_section_value && $this->acl[$i][4]) {
								if (strcasecmp( $axo_section_value, $this->acl[$i][4] ) == 0) {
									if (strcasecmp( $axo_value, $this->acl[$i][5] ) == 0) {
										$acl_result = 1;
										break;
									}
								}
							} else {
								$acl_result = 1;
								break;
							}
						}
					}
				}
			}
		}
		return $acl_result;
	}

	/**
	 * Gets the 'name' of a group
	 * @param int The group id
	 * @param string The type: [ARO]|AXO
	 * @return string
	 */
	function get_group_name($group_id = null, $group_type = 'ARO') {
		$data = $this->get_group_data( $group_id, 'ARO' );
		return $data[3];
	}

	/**
	 * @param string The value for the group
	 * @return object The row from the group table
	 */
	function getAroGroup( $value ) {
		return $this->_getGroup( 'aro', $value );
	}

	function _getGroup( $type, $value ) {
		global $database;

		$database->setQuery( "SELECT g.*"
			. "\nFROM #__core_acl_{$type}_groups AS g"
			. "\nINNER JOIN #__core_acl_groups_{$type}_map AS gm ON gm.group_id = g.id"
			. "\nINNER JOIN #__core_acl_{$type} AS ao ON ao.id = gm.{$type}_id"
			. "\nWHERE ao.value='$value'"
		);
		$obj = null;
		$database->loadObject( $obj );
		return $obj;
	}

	function _getBelow( $table, $fields, $groupby=null, $root_id=null, $root_name=null, $inclusive=true ) {
		global $database;

		$root = new stdClass();
		$root->lft = 0;
		$root->rgt = 0;

		if ($root_id) {
		} else if ($root_name) {
			$database->setQuery( "SELECT lft, rgt FROM $table WHERE name='$root_name'" );
			$database->loadObject( $root );
		}

		$where = '';
		if ($root->lft+$root->rgt <> 0) {
			if ($inclusive) {
				$where = "WHERE g1.lft BETWEEN $root->lft AND $root->rgt";
			} else {
				$where = "WHERE g1.lft BETWEEN $root->lft+1 AND $root->rgt-1";
			}
		}

		$database->setQuery( "SELECT $fields"
			. "\nFROM $table AS g1"
			. "\nINNER JOIN $table AS g2 ON g1.lft BETWEEN g2.lft AND g2.rgt"
			. "\n$where"
			. ($groupby ? "\nGROUP BY $groupby" : "")
			. "\nORDER BY g1.lft"
		);

		//echo $database->getQuery();
		return $database->loadObjectList();
	}

	/**
	 * @param int
	 * @param string
	 * @param boolean
	 * @param boolean Returns the complete html if true
	 * @return string|array String if html, otherwise an array
	 */
	function get_group_children_tree( $root_id=null, $root_name=null, $inclusive=true, $html=true ) {
		global $database;

		$tree = $this->_getBelow( '#__core_acl_aro_groups',
			'g1.id, g1.name, COUNT(g2.name) AS level',
			'g1.name',
			$root_id, $root_name, $inclusive );

		// first pass get level limits
		$n = count( $tree );
		$min = $tree[0]->level;
		$max = $tree[0]->level;
		for ($i=0; $i < $n; $i++) {
			$min = min( $min, $tree[$i]->level );
			$max = max( $max, $tree[$i]->level );
		}

		$indents = array();
		foreach (range( $min, $max ) as $i) {
			$indents[$i] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		// correction for first indent
		$indents[$min] = '';

		$list = array();
		for ($i=$n-1; $i >= 0; $i--) {
			$shim = '';
			foreach (range( $min, $tree[$i]->level ) as $j) {
				$shim .= $indents[$j];
			}

			if (@$indents[$tree[$i]->level+1] == '.&nbsp;') {
				$twist = '&nbsp;';
			} else {
				$twist = "-&nbsp;";
			}

			//$list[$i] = $tree[$i]->level.$shim.$twist.$tree[$i]->name;
			if ($html) {
				$list[$i] = mosHTML::makeOption( $tree[$i]->id, $shim.$twist.$tree[$i]->name );
			} else {
				$list[$i] = array( 'value'=>$tree[$i]->id, 'text'=>$shim.$twist.$tree[$i]->name );
			}
			if ($tree[$i]->level < @$tree[$i-1]->level) {
				$indents[$tree[$i]->level+1] = '.&nbsp;';
			}
		}

		ksort($list);
		return $list;
	}

	/*======================================================================*\
		Function:	has_group_parent
		Purpose:	Checks whether the 'source' group is a child of the 'target'
	\*======================================================================*/
	function is_group_child_of( $grp_src, $grp_tgt, $group_type='ARO' ) {
		global $database;

		$this->debug_text("has_group_parent(): Source=$grp_src, Target=$grp_tgt, Type=$group_type");

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		if (is_int( $grp_src ) && is_int($grp_tgt)) {
			$database->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.id=$grp_src AND g2.id=$grp_tgt"
			);
		} else if (is_string( $grp_src ) && is_string($grp_tgt)) {
			$database->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.name='$grp_src' AND g2.name='$grp_tgt'"
			);
		} else if (is_int( $grp_src ) && is_string($grp_tgt)) {
			$database->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.id='$grp_src' AND g2.name='$grp_tgt'"
			);
		} else {
			$database->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.name=$grp_src AND g2.id='$grp_tgt'"
			);
		}
		return $database->loadResult();
	}
}

?>
