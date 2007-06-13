<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	User
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('phpgacl.gacl');
jimport('phpgacl.gacl_api');

/**
 * Class that handles all access authorization
 *
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JAuthorization extends gacl_api
{
	/**
	 * Access control list
	 * @var	array
	 */
	var $acl       = null;

	/**
	 * Internal counter
	 * @var	int
	 */
	var $acl_count = 0;

	/**
	 * The check mode.  0 = Joomla!, 1 = phpGACL
	 * @var	int
	 */
	var $_checkMode = 0;

	/**
	 * Constructor
	 * @param array An arry of options to oeverride the class defaults
	 */
	function JAuthorization($options = NULL)
	{
		parent::gacl( $options );

		// ARO value is currently the user type,
		// this changes to user id in proper implementation
		// No hierarchial inheritance so have to do that the long way
		$this->acl = array();

		// special ACl with return value to edit user
		$this->addACL( 'com_user', 'edit', 'users', 'super administrator', null, null, '' );
		$this->addACL( 'com_user', 'edit', 'users', 'administrator', null, null, '' );
		$this->addACL( 'com_user', 'edit', 'users', 'manager', null, null, '' );
		// return value defines xml setup file variant
		$this->addACL( 'com_user', 'edit', 'users', 'author', null, null, 'author' );
		$this->addACL( 'com_user', 'edit', 'users', 'editor', null, null, 'author' );
		$this->addACL( 'com_user', 'edit', 'users', 'publisher', null, null, 'author' );
		$this->addACL( 'com_user', 'edit', 'users', 'registered', null, null, 'registered' );

		// backend login
		$this->addACL( 'login', 'administrator', 'users', 'administrator' );
		$this->addACL( 'login', 'administrator', 'users', 'super administrator' );
		$this->addACL( 'login', 'administrator', 'users', 'manager' );

		$this->addACL( 'login', 'site', 'users', 'administrator' );
		$this->addACL( 'login', 'site', 'users', 'super administrator' );
		$this->addACL( 'login', 'site', 'users', 'manager' );

		$this->addACL( 'login', 'site', 'users', 'registered' );
		$this->addACL( 'login', 'site', 'users', 'author' );
		$this->addACL( 'login', 'site', 'users', 'editor' );
		$this->addACL( 'login', 'site', 'users', 'publisher' );
		// backend menus

		$this->addACL( 'com_banners', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_banners', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_banners', 'manage', 'users', 'manager' );

		$this->addACL( 'com_checkin', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_checkin', 'manage', 'users', 'administrator' );

		$this->addACL( 'com_cache', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_cache', 'manage', 'users', 'administrator' );

		$this->addACL( 'com_config', 'manage', 'users', 'super administrator' );
		//$this->addACL( 'com_config', 'manage', 'users', 'administrator' );

		$this->addACL( 'com_contact', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_contact', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_contact', 'manage', 'users', 'manager' );

		$this->addACL( 'com_components', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_components', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_components', 'manage', 'users', 'manager' );

		$this->addACL( 'com_frontpage', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_frontpage', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_frontpage', 'manage', 'users', 'manager' );
		$this->addACL( 'com_frontpage', 'edit', 'users', 'manager' );

		// access to installers and base installer
		$this->addACL( 'com_installer', 'installer', 'users', 'administrator' );
		$this->addACL( 'com_installer', 'installer', 'users', 'super administrator' );

		$this->addACL( 'com_installer', 'component', 'users', 'administrator' );
		$this->addACL( 'com_installer', 'component', 'users', 'super administrator' );

		$this->addACL( 'com_installer', 'language', 'users', 'super administrator' );
		$this->addACL( 'com_installer', 'language', 'users', 'administrator' );

		$this->addACL( 'com_installer', 'module', 'users', 'administrator' );
		$this->addACL( 'com_installer', 'module', 'users', 'super administrator' );

		$this->addACL( 'com_installer', 'plugin', 'users', 'administrator' );
		$this->addACL( 'com_installer', 'plugin', 'users', 'super administrator' );

		$this->addACL( 'com_installer', 'template', 'users', 'super administrator' );
		$this->addACL( 'com_installer', 'template', 'users', 'administrator' );

		$this->addACL( 'com_languages', 'manage', 'users', 'super administrator' );

		$this->addACL( 'com_plugins', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_plugins', 'manage', 'users', 'administrator' );
		// uncomment following to allow managers to edit modules
		//array( 'administration', 'edit', 'users', 'manager', 'modules', 'all' );

		$this->addACL( 'com_massmail', 'manage', 'users', 'super administrator' );

		$this->addACL( 'com_media', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_media', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_media', 'manage', 'users', 'manager' );
		$this->addACL( 'com_media', 'popup', 'users', 'super administrator' );
		$this->addACL( 'com_media', 'popup', 'users', 'administrator' );
		$this->addACL( 'com_media', 'popup', 'users', 'manager' );
		$this->addACL( 'com_media', 'popup', 'users', 'registered' );
		$this->addACL( 'com_media', 'popup', 'users', 'author' );
		$this->addACL( 'com_media', 'popup', 'users', 'editor' );
		$this->addACL( 'com_media', 'popup', 'users', 'publisher' );

		$this->addACL( 'com_menumanager', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_menumanager', 'manage', 'users', 'super administrator' );

		$this->addACL( 'com_modules', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_modules', 'manage', 'users', 'administrator' );

		$this->addACL( 'com_newsfeeds', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_newsfeeds', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_newsfeeds', 'manage', 'users', 'manager' );

		$this->addACL( 'com_poll', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_poll', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_poll', 'manage', 'users', 'manager' );

		$this->addACL( 'com_templates', 'manage', 'users', 'super administrator' );
		//$this->addACL( 'com_templates', 'manage', 'user', 'administrator' )

		$this->addACL( 'com_trash', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_trash', 'manage', 'users', 'super administrator' );

		// email block users property
		$this->addACL( 'com_users', 'block user', 'users', 'administrator' );
		$this->addACL( 'com_users', 'block user', 'users', 'super administrator' );

		$this->addACL( 'com_users', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_users', 'manage', 'users', 'super administrator' );

		$this->addACL( 'com_weblinks', 'manage', 'users', 'super administrator' );
		$this->addACL( 'com_weblinks', 'manage', 'users', 'administrator' );
		$this->addACL( 'com_weblinks', 'manage', 'users', 'manager' );

		// email system events
		$this->addACL( 'com_users', 'email_events', 'users', 'administrator' );
		$this->addACL( 'com_users', 'email_events', 'users', 'super administrator' );
		$this->addACL( 'workflow', 'email_events', 'users', 'administrator', null, null );
		$this->addACL( 'workflow', 'email_events', 'users', 'super administrator', null, null );

		// actions
		$this->addACL( 'com_content', 'add', 'users', 'author', 'content', 'all' );
		$this->addACL( 'com_content', 'add', 'users', 'editor', 'content', 'all' );
		$this->addACL( 'com_content', 'add', 'users', 'publisher', 'content', 'all' );
		$this->addACL( 'com_content', 'edit', 'users', 'author', 'content', 'own' );
		$this->addACL( 'com_content', 'edit', 'users', 'editor', 'content', 'all' );
		$this->addACL( 'com_content', 'edit', 'users', 'publisher', 'content', 'all' );
		$this->addACL( 'com_content', 'publish', 'users', 'publisher', 'content', 'all' );

		$this->addACL( 'com_content', 'add', 'users', 'manager', 'content', 'all' );
		$this->addACL( 'com_content', 'edit', 'users', 'manager', 'content', 'all' );
		$this->addACL( 'com_content', 'publish', 'users', 'manager', 'content', 'all' );

		$this->addACL( 'com_content', 'add', 'users', 'administrator', 'content', 'all' );
		$this->addACL( 'com_content', 'edit', 'users', 'administrator', 'content', 'all' );
		$this->addACL( 'com_content', 'publish', 'users', 'administrator', 'content', 'all' );

		$this->addACL( 'com_content', 'add', 'users', 'super administrator', 'content', 'all' );
		$this->addACL( 'com_content', 'edit', 'users', 'super administrator', 'content', 'all' );
		$this->addACL( 'com_content', 'publish', 'users', 'super administrator', 'content', 'all' );
	}

	/**
	 * This is a temporary function to allow 3PD's to add basic ACL checks for their
	 * modules and components.  NOTE: this information will be compiled in the db
	 * in future versions
	 * 
	 * @param	string	The ACO section value
	 * @param	string	The ACO value
	 * @param	string	The ARO section value
	 * @param	string	The ARO section
	 * @param	string	The AXO section value (optional)
	 * @param	string	The AXO section value (optional)
	 * @param	string	The return value for the ACL (optional)
	 */
	function addACL( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $return_value=NULL )
	{
		$this->acl[] = array( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $return_value );
		$this->acl_count++;
	}

	/**
	 * Gets the chec mode
	 * @return	int
	 */
	function getCheckMode()
	{
		return $this->_checkMode;
	}

	/**
	 * Sets the check mode.
	 * 
	 * Only used if the full implementation of the phpGACL library is installed and configured
	 * 
	 * @param	int		0 = Joomla!, 1 = phpGACL native
	 * @return	int		The previous value
	 */
	function setCheckMode( $value )
	{
		$old				= $this->_checkMode;
		$this->_checkMode	= (int) $value;
		return $old;
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
	function acl_check( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group=NULL, $root_axo_group=NULL )
	{
		if ($this->_checkMode === 1) {
			return parent::acl_check( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group, $root_axo_group );
		}

		$this->debug_text( "\n<br> ACO=$aco_section_value:$aco_value, ARO=$aro_section_value:$aro_value, AXO=$axo_section_value|$axo_value" );

		$acl_result = 0;
		for ($i=0; $i < $this->acl_count; $i++)
		{
			$acl =& $this->acl[$i];
			if (strcasecmp( $aco_section_value, $acl[0] ) == 0) {
				if (strcasecmp( $aco_value, $acl[1] ) == 0) {
					if (strcasecmp( $aro_section_value, $acl[2] ) == 0) {
						if (strcasecmp( $aro_value, $acl[3] ) == 0) {
							if ($axo_section_value && $acl[4]) {
								if (strcasecmp( $axo_section_value, $acl[4] ) == 0) {
									if (strcasecmp( $axo_value, $acl[5] ) == 0) {
										$acl_result = @$acl[6] ? $acl[6] : 1;
										break;
									}
								}
							} else {
								$acl_result = @$acl[6] ? $acl[6] : 1;
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
	function get_group_name($group_id = null, $group_type = 'ARO')
	{
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

	function _getGroup( $type, $value )
	{
		$db =& JFactory::getDBO();

		$db->setQuery( 'SELECT g.*'
			. ' FROM #__core_acl_'.$type.'_groups AS g'
			. ' INNER JOIN #__core_acl_groups_'.$type.'_map AS gm ON gm.group_id = g.id'
			. ' INNER JOIN #__core_acl_'.$type.' AS ao ON ao.id = gm.'.$type.'_id'
			. ' WHERE ao.value="'.$value.'"'
		);
		$obj = $db->loadObject(  );
		return $obj;
	}

	function _getBelow( $table, $fields, $groupby=null, $root_id=null, $root_name=null, $inclusive=true )
	{
		$db =& JFactory::getDBO();

		$root = new stdClass();
		$root->lft = 0;
		$root->rgt = 0;

		if ($root_id) {
		} else if ($root_name) {
			$query	= "SELECT lft, rgt FROM $table WHERE name = '$root_name' ";
			$db->setQuery( $query );
			$root = $db->loadObject();
		}

		$where = '';
		if ($root->lft+$root->rgt <> 0) {
			if ($inclusive) {
				$where = " WHERE g1.lft BETWEEN $root->lft AND $root->rgt ";
			} else {
				$where = ' WHERE g1.lft BETWEEN 3 AND 22 ';
			}
		}

		$query	= 'SELECT '. $fields
				. ' FROM '. $table .' AS g1'
				. ' INNER JOIN '. $table .' AS g2 ON g1.lft BETWEEN g2.lft AND g2.rgt'
				. $where
				. ($groupby ? ' GROUP BY ' . $groupby : '')
				. ' ORDER BY g1.lft';
		$db->setQuery( $query );

		return $db->loadObjectList();
	}

	/**
	 * @param int
	 * @param string
	 * @param boolean
	 * @param boolean Returns the complete html if true
	 * @return string|array String if html, otherwise an array
	 */
	function get_group_children_tree( $root_id=null, $root_name=null, $inclusive=true, $html=true )
	{
		$db =& JFactory::getDBO();

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
				$list[$i] = JHTML::_('select.option',  $tree[$i]->id, $shim.$twist.$groupName );
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
	function is_group_child_of( $grp_src, $grp_tgt, $group_type='ARO' )
	{
		$db =& JFactory::getDBO();

		$this->debug_text("has_group_parent(): Source=$grp_src, Target=$grp_tgt, Type=$group_type");

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		$query = 'SELECT COUNT(*) '.
				 'FROM '.$table.' AS g1 '.
				 'LEFT JOIN '.$table.' AS g2 ON (g1.lft > g2.lft AND g1.lft < g2.rgt) ';

		if (is_int( $grp_src ) && is_int($grp_tgt)) {
			$query .= 'WHERE g1.id = '.$grp_src.' AND g2.id = '.$grp_tgt;
		} else if (is_string( $grp_src ) && is_string($grp_tgt)) {
			$query .= 'WHERE g1.name = '.$db->Quote($grp_src).' AND g2.name = '.$db->Quote($grp_tgt);
		} else if (is_int( $grp_src ) && is_string($grp_tgt)) {
			$query .= 'WHERE g1.id = '.$grp_src.' AND g2.name = '.$db->Quote($grp_tgt);
		} else {
			$query .= 'WHERE g1.name = '.$db->Quote($grp_src).' AND g2.id = '.$grp_tgt;
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/*======================================================================*\
		Function:	get_group_children()
		Purpose:	Gets a groups child IDs
	\*======================================================================*/
	function get_group_parents($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE')
	{
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

		$query = '
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
	
	
	/**
	 * Deprecated, use JAuthorisation::addACL() instead.
	 *
	 * @since 1.0
	 * @deprecated As of version 1.5
	 * @see JAuthorisation::addACL()
	 */
	function _mos_add_acl( $aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $return_value=NULL ) {
		$this->addACL($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $return_value);
	}

}

/**
 * Required for both Classess below
 */
jimport('joomla.database.table');

/**
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JTableARO extends JTable
{
	/** @var int Primary key */
	var $id			  	= null;
	var $section_value	= null;
	var $value			= null;
	var $order_value	= null;
	var $name			= null;
	var $hidden			= null;

	function __construct( &$db )
	{
		parent::__construct( '#__core_acl_aro', 'aro_id', $db );
	}
}

/**
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
 class JTableAROGroup extends JTable
 {
	/** @var int Primary key */
	var $id			= null;
	var $parent_id	= null;
	var $name		= null;
	var $value		= null;
	var $lft		= null;
	var $rgt		= null;

	function __construct( &$db )
	{
		parent::__construct( '#__core_acl_aro_groups', 'group_id', $db );
	}
}