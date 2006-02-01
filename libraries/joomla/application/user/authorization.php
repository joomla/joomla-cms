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
 * Path to ADODB.
 */
if ( !defined('ADODB_DIR') ) {
	define('ADODB_DIR', dirname(__FILE__).'/adodb');
}

jimport('phpgacl.gacl');
jimport('phpgacl.gacl_api');

/**
 * Class that handles all access authorization
 * 
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
class JAuthorization extends gacl_api {
	var $acl=null;
	var $acl_count=0;

	/**
	 * Constructor
	 * @param array An arry of options to oeverride the class defaults
	 */
	function JAuthorization($options = NULL) {
		parent::gacl( $options );

		// ARO value is currently the user type,
		// this changes to user id in proper implementation
		// No hierarchial inheritance so have to do that the long way
		$this->acl = array();

		// backend login
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'administrator' );
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'super administrator' );
		$this->_mos_add_acl( 'login', 'administrator', 'users', 'manager' );

		$this->_mos_add_acl( 'login', 'site', 'users', 'administrator' );
		$this->_mos_add_acl( 'login', 'site', 'users', 'super administrator' );
		$this->_mos_add_acl( 'login', 'site', 'users', 'manager' );

		$this->_mos_add_acl( 'login', 'site', 'users', 'registered' );
		$this->_mos_add_acl( 'login', 'site', 'users', 'author' );
		$this->_mos_add_acl( 'login', 'site', 'users', 'editor' );
		$this->_mos_add_acl( 'login', 'site', 'users', 'publisher' );
		// backend menus

		$this->_mos_add_acl( 'com_banners', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_banners', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_banners', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_checkin', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_checkin', 'manage', 'users', 'administrator' );

		$this->_mos_add_acl( 'com_config', 'manage', 'users', 'super administrator' );
		//$this->_mos_add_acl( 'com_config', 'manage', 'users', 'administrator' );

		$this->_mos_add_acl( 'com_contact', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_contact', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_contact', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_components', 'manage', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_frontpage', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_frontpage', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_frontpage', 'manage', 'users', 'manager' );
		$this->_mos_add_acl( 'com_frontpage', 'edit', 'users', 'manager' );

		// access to installers and base installer
		$this->_mos_add_acl( 'com_installer', 'installer', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_installer', 'installer', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_installer', 'component', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_installer', 'component', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_installer', 'language', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_installer', 'language', 'users', 'administrator' );

		$this->_mos_add_acl( 'com_installer', 'module', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_installer', 'module', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_installer', 'plugin', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_installer', 'plugin', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_installer', 'template', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_installer', 'template', 'users', 'administrator' );

		$this->_mos_add_acl( 'com_languages', 'manage', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_plugins', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_plugins', 'manage', 'users', 'administrator' );
		// uncomment following to allow managers to edit modules
		//array( 'administration', 'edit', 'users', 'manager', 'modules', 'all' );

		$this->_mos_add_acl( 'com_massmail', 'manage', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_media', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_media', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_media', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_menumanager', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_menumanager', 'manage', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_modules', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_modules', 'manage', 'users', 'administrator' );

		$this->_mos_add_acl( 'com_newsfeeds', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_newsfeeds', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_newsfeeds', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_poll', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_poll', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_poll', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_syndicate', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_syndicate', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_syndicate', 'manage', 'users', 'manager' );

		$this->_mos_add_acl( 'com_templates', 'manage', 'users', 'super administrator' );
		//$this->_mos_add_acl( 'com_templates', 'manage', 'user', 'administrator' )

		$this->_mos_add_acl( 'com_trash', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_trash', 'manage', 'users', 'super administrator' );

		// email block users property
		$this->_mos_add_acl( 'com_users', 'block user', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_users', 'block user', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_users', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_users', 'manage', 'users', 'super administrator' );

		$this->_mos_add_acl( 'com_weblinks', 'manage', 'users', 'super administrator' );
		$this->_mos_add_acl( 'com_weblinks', 'manage', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_weblinks', 'manage', 'users', 'manager' );

		// email system events
		$this->_mos_add_acl( 'com_users', 'email_events', 'users', 'administrator' );
		$this->_mos_add_acl( 'com_users', 'email_events', 'users', 'super administrator' );
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

		// Legacy ACL's for backward compat
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'super administrator', 'components', 'all' );
		$this->_mos_add_acl( 'administration', 'edit', 'users', 'administrator', 'components', 'all' );

		$this->acl_count = count( $this->acl );
	}

	/**
	 * This is a temporary function to allow 3PD's to add basic ACL checks for their
	 * modules and components.  NOTE: this information will be compiled in the db
	 * in future versions
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
			. ($groupby ? ' GROUP BY ' . $groupby : '')
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
			$groupName = JText::_( $tree[$i]->name );
			//$list[$i] = $tree[$i]->level.$shim.$twist.$tree[$i]->name;
			if ($html) {
				$list[$i] = mosHTML::makeOption( $tree[$i]->id, $shim.$twist.$groupName );
			} else {
				$list[$i] = array( 'value'=>$tree[$i]->id, 'text'=>$shim.$twist.$groupName );
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

	/*======================================================================*\
		Function:	get_group_children()
		Purpose:	Gets a groups child IDs
	\*======================================================================*/
	function get_group_parents($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE') {
		$this->debug_text("get_group_parents(): Group_ID: $group_id Group Type: $group_type Recurse: $recurse");

		switch (strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'aro_groups';
		}

		if (empty($group_id)) {
			$this->debug_text("get_group_parents(): ID ($group_id) is empty, this is required");
			return FALSE;
		}

		$query  = '
				SELECT		g2.group_id
				FROM		'. $table .' g1';

		//FIXME-mikeb: Why is group_id in quotes?
		switch (strtoupper($recurse)) {
			case 'RECURSE':
				$query .= '
				LEFT JOIN 	'. $table .' g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt
				WHERE		g1.id='. $group_id;
				break;
			case 'RECURSE_INCL':
				// inclusive resurse
				$query .= '
				LEFT JOIN 	'. $table .' g2 ON g1.lft >= g2.lft AND g1.lft <= g2.rgt
				WHERE		g1.id='. $group_id;
				break;
			default:
				$query .= '
				WHERE		g1.parent_id='. $group_id;
		}

		$query .= '
				ORDER BY	g2.lft';


		$this->db->setQuery( $query );
		return $this->db->loadResultArray();
	}

}

/**
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
class JModelARO extends JModel {
/** @var int Primary key */
	var $id=null;
	var $section_value=null;
	var $value=null;
	var $order_value=null;
	var $name=null;
	var $hidden=null;

	function __construct( &$db ) {
		parent::__construct( '#__core_acl_aro', 'aro_id', $db );
	}
}

/**
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
 class JModelAroGroup extends JModel {
/** @var int Primary key */
	var $id=null;
	var $parent_id=null;
	var $name=null;
	var $value=null;
	var $lft=null;
	var $rgt=null;

	function __construct( &$db ) {
		parent::__construct( '#__core_acl_aro_groups', 'group_id', $db );
	}
}
?>