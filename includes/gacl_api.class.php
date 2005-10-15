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

/*
 *
 *
 *  == If you find a feature may be missing from this API, please email me: ipso@snappymail.ca and I will be happy to add it. ==
 *
 *
 * Example:
 *	$gacl_api = new gacl_api;
 *
 *	$section_id = $gacl_api->get_aco_section_id('System');
 *	$aro_id= $gacl_api->add_aro($section_id, 'John Doe', 10);
 *
 * For more examples, see the Administration interface, as it makes use of nearly every API Call.
 *
 */

class gacl_api extends gacl {
	/*
	 * Administration interface settings
	 */
	var $_items_per_page = 100;
	var $_max_select_box_items = 100;
	var $_max_search_return_items = 100;

	/*
	 *
	 * Misc helper functions.
	 *
	 */

	/*======================================================================*\
		Function:   showarray()
		Purpose:	Dump all contents of an array in HTML (kinda).
	\*======================================================================*/
	function showarray($array) {
		echo "<br><pre>\n";
		var_dump($array);
		echo "</pre><br>\n";
	}

	/*======================================================================*\
		Function:   $gacl_api->return_page()
		Purpose:	Sends the user back to a passed URL, unless debug is enabled, then we don't redirect.
						If no URL is passed, try the REFERER
	\*======================================================================*/
	function return_page($url="") {
		global $_SERVER, $debug;

		if (empty($url) AND !empty($_SERVER[HTTP_REFERER])) {
			$this->debug_text("return_page(): URL not set, using referer!");
			$url = $_SERVER[HTTP_REFERER];
		}

		if (!$debug OR $debug==0) {
			header("Location: $url\n\n");
		} else {
			$this->debug_text("return_page(): URL: $url -- Referer: $_SERVER[HTTP_REFERRER]");
		}
	}

	/*======================================================================*\
		Function:   get_paging_data()
		Purpose:	Creates a basic array for Smarty to deal with paging large recordsets.
						Pass it the ADODB recordset.
	\*======================================================================*/
	function get_paging_data($rs) {
				return array(
								'prevpage' => $rs->absolutepage() - 1,
								'currentpage' => $rs->absolutepage(),
								'nextpage' => $rs->absolutepage() + 1,
								'atfirstpage' => $rs->atfirstpage(),
								'atlastpage' => $rs->atlastpage(),
								'lastpageno' => $rs->lastpageno()
						);
	}

	/*======================================================================*\
		Function:	count_all()
		Purpose:	Recursively counts elements in an array and sub-arrays.
					The returned count is a count of all scalar elements found.

					This is different from count($arg, COUNT_RECURSIVE)
					in PHP >= 4.2.0, which includes sub-arrays in the count.
	\*======================================================================*/
	function count_all($arg = NULL) {
		switch (TRUE) {
			case is_scalar($arg):
			case is_object($arg):
				// single object
				return 1;
			case is_array($arg):
				// call recursively for all elements of $arg
				$count = 0;
				foreach ($arg as $val) {
					$count += $this->count_all($val);
				}
				return $count;
		}
		return FALSE;
	}

	/*======================================================================*\
		Function:	get_version()
		Purpose:	Grabs phpGACL version from the database.
	\*======================================================================*/


	/*======================================================================*\
		Function:	get_schema_version()
		Purpose:	Grabs phpGACL schema version from the database.
	\*======================================================================*/
	/*
	 *
	 * ACL
	 *
	 */

	/*======================================================================*\
		Function:	consolidated_edit_acl()
		Purpose:	Add's an ACL but checks to see if it can consolidate it with another one first.
					This ONLY works with ACO's and ARO's. Groups, and AXO are excluded.
					As well this function is designed for handling ACLs with return values,
					and consolidating on the return_value, in hopes of keeping the ACL count to a minimum.

					A return value of false must _always_ be handled outside this function.
					As this function will remove AROs from ACLs and return false, in most cases
					you will need to a create a completely new ACL on a false return.
	\*======================================================================*/

	/*======================================================================*\
		Function:	shift_acl()
		Purpose:	Opposite of append_acl(). Removes objects from a specific ACL. (named after PHP's array_shift())
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_acl()
		Purpose:	Grabs ACL data.
	\*======================================================================*/

	/*======================================================================*\
		Function:	is_conflicting_acl()
		Purpose:	Checks for conflicts when adding a specific ACL.
	\*======================================================================*/

	/*======================================================================*\
		Function:	add_acl()
		Purpose:	Add's an ACL. ACO_IDS, ARO_IDS, GROUP_IDS must all be arrays.
	\*======================================================================*/

	/*======================================================================*\
		Function:	edit_acl()
		Purpose:	Edit's an ACL, ACO_IDS, ARO_IDS, GROUP_IDS must all be arrays.
	\*======================================================================*/

	/*======================================================================*\
		Function:	del_acl()
		Purpose:	Deletes a given ACL
	\*======================================================================*/


	/*
	 *
	 * Groups
	 *
	 */

	/*======================================================================*\
		Function:	sort_groups()
		Purpose:	Grabs all the groups from the database doing preliminary grouping by parent
	\*======================================================================*/

	/*======================================================================*\
		Function:	format_groups()
		Purpose:	Takes the array returned by sort_groups() and formats for human consumption.
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_group_id()
		Purpose:	Gets the group_id given the name.
						Will only return one group id, so if there are duplicate names, it will return false.
	\*======================================================================*/
	function get_group_id($name = null, $group_type = 'ARO') {

		$this->debug_text("get_group_id(): Name: $name");

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		$name = trim($name);

		if (empty($name) ) {
			$this->debug_text("get_group_id(): name ($name) is empty, this is required");
			return false;
		}

		$this->db->setQuery( "SELECT group_id FROM $table WHERE name='$name'" );

		$rows = $this->db->loadRowList();
		if ($this->db->getErrorNum()) {
			$this->debug_db('get_group_id');
			return false;
		}

		$row_count = count( $rows );

		if ($row_count > 1) {
			$this->debug_text("get_group_id(): Returned $row_count rows, can only return one. Please make your names unique.");
			return false;
		}

		if ($row_count == 0) {
			$this->debug_text("get_group_id(): Returned $row_count rows");
			return false;
		}

		$row = $rows[0];

		//Return the ID.
		return $row[0];
	}

	/*======================================================================*\
		Function:	get_group_name()
		Purpose:	Gets the name given the group_id.
						Will only return one group id, so if there are duplicate names, it will return false.
	\*======================================================================*/
	function get_group_name($group_id = null, $group_type = 'ARO') {

		$this->debug_text("get_group_name(): ID: $group_id");

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		$group_id = intval($group_id);

		if (!$group_id) {
			$this->debug_text("get_group_name(): group_id ($group_id) is empty, this is required");
			return false;
		}

		$this->db->setQuery( "SELECT name FROM $table WHERE group_id='$group_id'" );

		$rows = $this->db->loadRowList();
		if ($this->db->getErrorNum()) {
			$this->debug_db('get_group_name');
			return false;
		}

		$row_count = count( $rows );

		if ($row_count > 1) {
			$this->debug_text("get_group_name(): Returned $row_count rows, can only return one. Please make your names unique.");
			return false;
		}

		if ($row_count == 0) {
			$this->debug_text("get_group_name(): Returned $row_count rows");
			return false;
		}

		$row = $rows[0];

		//Return the ID.
		return $row[0];
	}

	/*======================================================================*\
		Function:	get_group_children()
		Purpose:	Gets a groups child IDs
	\*======================================================================*/
	function get_group_children($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE') {
		$this->debug_text("get_group_children(): Group_ID: $group_id Group Type: $group_type Recurse: $recurse");

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
			$this->debug_text("get_group_children(): ID ($group_id) is empty, this is required");
			return FALSE;
		}

		$query  = '
				SELECT		g1.group_id
				FROM		'. $table .' g1';

		//FIXME-mikeb: Why is group_id in quotes?
		switch (strtoupper($recurse)) {
			case 'RECURSE':
				$query .= '
				LEFT JOIN 	'. $table .' g2 ON g2.lft<g1.lft AND g2.rgt>g1.rgt
				WHERE		g2.group_id='. $group_id;
				break;
			default:
				$query .= '
				WHERE		g1.parent_id='. $group_id;
		}

		$query .= '
				ORDER BY	g1.name';


		$this->db->setQuery( $query );
		return $this->db->loadResultArray();
	}

	/*======================================================================*\
		Function:	get_group_data()
		Purpose:	Gets the group data given the GROUP_ID.
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_group_parent_id()
		Purpose:	Grabs the parent_id of a given group
	\*======================================================================*/

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
				WHERE		g1.group_id='. $group_id;
				break;
			case 'RECURSE_INCL':
				// inclusive resurse
				$query .= '
				LEFT JOIN 	'. $table .' g2 ON g1.lft >= g2.lft AND g1.lft <= g2.rgt
				WHERE		g1.group_id='. $group_id;
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

	/*======================================================================*\
		Function:	get_root_group_id ()
		Purpose:	Grabs the id of the root group for the specified tree
	\*======================================================================*/

	/*======================================================================*\
		Function:	map_path_to_root()
		Purpose:	Maps a unique path to root to a specific group. Each group can only have
						one path to root.
	\*======================================================================*/
	/** REMOVED **/
	/*======================================================================*\
		Function:	put_path_to_root()
		Purpose:	Writes the unique path to root to the database. There should really only be
						one path to root for each level "deep" the groups go. If the groups are branched
						10 levels deep, there should only be 10 unique path to roots. These of course
						overlap each other more and more the closer to the root/trunk they get.
	\*======================================================================*/
	/** REMOVED **/
	/*======================================================================*\
		Function:	clean_path_to_root()
		Purpose:	Cleans up any paths that are not being used.
	\*======================================================================*/
	/** REMOVED **/
	/*======================================================================*\
		Function:	get_path_to_root()
		Purpose:	Generates the path to root for a given group.
	\*======================================================================*/
	/** REMOVED **/

	/*======================================================================*\
		Function:	add_group()
		Purpose:	Inserts a group, defaults to be on the "root" branch.
	\*======================================================================*/
	function add_group($name, $parent_id=0, $group_type='ARO') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		$this->debug_text("add_group(): Name: $name Parent ID: $parent_id Group Type: $group_type");

		$name = trim($name);

		if (empty($name)) {
			$this->debug_text("add_group(): name ($name) OR parent id ($parent_id) is empty, this is required");
			return false;
		}

		//This has to be outside the transaction, because the first time it is run, it will say the sequence
		//doesn't exist. Then try to create it, but the transaction will already by aborted by then.
		//$insert_id = $this->db->GenID($this->_db_table_prefix.$group_type.'_groups_id_seq',10);
		$this->db->setQuery( "SELECT MAX(group_id)+1 FROM $table" );
		$insert_id = intval( $this->db->loadResult() );

		// <mos> $this->db->BeginTrans();

		// special case for root group
		if ($parent_id == 0) {
			// check a root group is not already defined
			$$this->db->setQuery( 'SELECT group_id FROM '. $table .' WHERE parent_id=0' );
			$rs = $this->db->loadResultArray();

			if (!is_array( $rs )) {
				$this->debug_db('add_group');
				$this->db->RollBackTrans();
				return FALSE;
			}

			if (count( $rs ) > 0) {
				$this->debug_text('add_group (): A root group already exists.');
				// <mos> $this->db->RollBackTrans();
				return FALSE;
			}

			$parent_lft = 0;
			$parent_rgt = 1;
		} else {
			if (empty($parent_id)) {
				$this->debug_text("add_group (): parent id ($parent_id) is empty, this is required");
				return FALSE;
			}

			// grab parent details from database
			$this->db->setQuery( 'SELECT group_id, lft, rgt FROM '. $table .' WHERE group_id='. $parent_id );
			$rows = $this->db->loadRowList();

			if (!is_array($rows) OR $this->db->getErrorNum() > 0) {
				$this->debug_db('add_group');
				// <mos> $this->db->RollBackTrans();
				return FALSE;
			}

			if (empty($rows)) {
				$this->debug_text('add_group (): Parent ID: '. $parent_id .' not found.');
				// <mos> $this->db->RollBackTrans();
				return FALSE;
			}
			$row = $rows[0];
			$parent_lft = &$row[1];
			$parent_rgt = &$row[2];

			// make room for the new group
			$this->db->setQuery( 'UPDATE '. $table .' SET rgt=rgt+2 WHERE rgt>='. $parent_rgt );
			$rs = $this->db->query();

			if (!$rs) {
				$this->debug_db('add_group: make room for the new group - right');
				// <mos> $this->db->RollBackTrans();
				return FALSE;
			}

			$this->db->setQuery( 'UPDATE '. $table .' SET lft=lft+2 WHERE lft>'. $parent_rgt );
			$rs = $this->db->query($query);

			if (!$rs) {
				$this->debug_db('add_group: make room for the new group - left');
				// <mos> $this->db->RollBackTrans();
				return FALSE;
			}
		}

		$this->db->setQuery( 'INSERT INTO '. $table .' (group_id,parent_id,name,lft,rgt) VALUES ('. $insert_id .','. $parent_id .',\''. $this->db->getEscaped($name) .'\','. $parent_rgt .','. ($parent_rgt + 1) .')' );
		$rs = $this->db->query($query);

		if (!$rs) {
			$this->debug_db('add_group: insert record');
			// <mos> $this->db->RollBackTrans();
			return FALSE;
		}

		// <mos> $this->db->CommitTrans();

		$this->debug_text('add_group (): Added group as ID: '. $insert_id);
		return $insert_id;
	}

	/*======================================================================*\
		Function:	get_group_objects()
		Purpose:	Gets all objects assigned to a group.
						If $option == 'RECURSE' it will get all objects in child groups as well.
						defaults to omit child groups.
	\*======================================================================*/
	function get_group_objects($group_id, $group_type='ARO', $option='NO_RECURSE') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$object_table = $this->_db_table_prefix .'axo';
				$group_table = $this->_db_table_prefix .'axo_groups';
				$map_table = $this->_db_table_prefix .'groups_axo_map';
				break;
			default:
				$group_type = 'aro';
				$object_table = $this->_db_table_prefix .'aro';
				$group_table = $this->_db_table_prefix .'aro_groups';
				$map_table = $this->_db_table_prefix .'groups_aro_map';
				break;
		}

		$this->debug_text("get_group_objects(): Group ID: $group_id");

		if (empty($group_id)) {
			$this->debug_text("get_group_objects(): Group ID:  ($group_id) is empty, this is required");
			return false;
		}

		$query = '
				SELECT		o.section_value,o.value
				FROM		'. $object_table .' o
				LEFT JOIN	'. $map_table .' gm ON o.'. $group_type .'_id=gm.'. $group_type .'_id';

		if ($option == 'RECURSE') {
			$query .= '
				LEFT JOIN	'. $group_table .' g1 ON g1.group_id=gm.group_id
				LEFT JOIN	'. $group_table .' g2 ON g2.lft<=g1.lft AND g2.rgt>=g1.rgt
				WHERE		g2.group_id='. $group_id;
		} else {
			$query .= '
				WHERE		gm.group_id='. $group_id;
		}

		$this->db->setQuery( $query );

		$rs = $this->db->loadRowList();

		if (!is_array( $rs )) {
			$this->debug_db('get_group_objects');
			return false;
		}

		$this->debug_text("get_group_objects(): Got group objects, formatting array.");

		$retarr = array();

		//format return array.
		foreach ($rs as $row) {
			$section = &$row[0];
			$value = &$row[1];

			$retarr[$section][] = $value;
		}

		return $retarr;
	}

	/*======================================================================*\
		Function:	add_group_object()
		Purpose:	Assigns an Object to a group
	\*======================================================================*/
	function add_group_object($group_id, $object_section_value, $object_value, $group_type='ARO') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'groups_axo_map';
				$object_table = $this->_db_table_prefix .'axo';
				$group_table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'groups_aro_map';
				$object_table = $this->_db_table_prefix .'aro';
				$group_table = $this->_db_table_prefix .'aro_groups';
				break;
		}

		$this->debug_text("add_group_object(): Group ID: $group_id, Section Value: $object_section_value, Value: $object_value, Group Type: $group_type");

		$object_section_value = trim($object_section_value);
		$object_value = trim($object_value);

		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
			$this->debug_text("add_group_object(): Group ID:  ($group_id) OR Value ($object_value) OR Section value ($object_section_value) is empty, this is required");
			return false;
		}

		// test to see if object & group exist and if object is already a member
		$this->db->setQuery( '
			SELECT		g.group_id,o.'. $group_type .'_id,gm.group_id AS member
			FROM		'. $object_table .' o
			LEFT JOIN	'. $group_table .' g ON g.group_id='. $group_id .'
			LEFT JOIN	'. $table .' gm ON (gm.group_id=g.group_id AND gm.'. $group_type .'_id=o.'. $group_type .'_id)
			WHERE		(o.section_value=\''. $this->db->getEscaped($object_section_value) .'\' AND o.value=\''. $this->db->getEscaped($object_value) .'\')'
		);

		$rows = $this->db->loadRowList();
		if ($this->db->getErrorNum()) {
			$this->debug_db('add_group_object');
			return FALSE;
		}

		if (count( $rows ) != 1) {
			$this->debug_text("add_group_object (): Group ID ($group_id) OR Value ($object_value) OR Section value ($object_section_value) is invalid. Does this object exist?");
			return FALSE;
		}

		$row = $rows[0];

		if ($row[2] == 1) {
			$this->debug_text("add_group_object (): Object: $object_value is already a member of Group ID: $group_id");
			//Object is already assigned to group. Return true.
			return true;
		}

		$object_id = $row[1];

		$this->db->setQuery( 'INSERT INTO '. $table .' (group_id,'. $group_type .'_id) VALUES ('. $group_id .','. $object_id .')' );

		if (!$this->db->query()) {
			$this->debug_db('add_group_object');
			return FALSE;
		}

		$this->debug_text('add_group_object(): Added Object: '. $object_id .' to Group ID: '. $group_id);

		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
			//Expire all cache.
			$this->Cache_Lite->clean('default');
		}

		return TRUE;
	}

	/*======================================================================*\
		Function:	del_group_object()
		Purpose:	Removes an Object from a group.
	\*======================================================================*/
	function del_group_object($group_id, $object_section_value, $object_value, $group_type='ARO') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'groups_axo_map';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'groups_aro_map';
				break;
		}

		$this->debug_text("del_group_object(): Group ID: $group_id Section value: $object_section_value Value: $object_value");

		$object_section_value = trim($object_section_value);
		$object_value = trim($object_value);

		if (empty($group_id) OR empty($object_value) OR empty($object_section_value)) {
			$this->debug_text("del_group_object(): Group ID:  ($group_id) OR Section value: $object_section_value OR Value ($object_value) is empty, this is required");
			return false;
		}

 		if (!$object_id = $this->get_object_id($object_section_value, $object_value, $group_type)) {
			$this->debug_text ("del_group_object (): Group ID ($group_id) OR Value ($object_value) OR Section value ($object_section_value) is invalid. Does this object exist?");
			return FALSE;
		}

		$this->db->setQuery( 'DELETE FROM '. $table .' WHERE group_id='. $group_id .' AND '. $group_type .'_id='. $object_id );
		$this->db->query();

		if ($this->db->getErrorNum()) {
			$this->debug_db('del_group_object');
			return false;
		}

		$this->debug_text("del_group_object(): Deleted Value: $object_value to Group ID: $group_id assignment");

		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
			//Expire all cache.
			$this->Cache_Lite->clean('default');
		}

		return true;
	}

	/*======================================================================*\
		Function:	edit_group()
		Purpose:	Edits a group
	\*======================================================================*/

	/*======================================================================*\
		Function:	rebuild_tree ()
		Purpose:	rebuilds the group tree for the given type
	\*======================================================================*/

	/*======================================================================*\
		Function:	del_group()
		Purpose:	deletes a given group
	\*======================================================================*/
	function del_group($group_id, $reparent_children=TRUE, $group_type='ARO') {

		switch(strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'axo_groups';
				$groups_map_table = $this->_db_table_prefix .'axo_groups_map';
				$groups_object_map_table = $this->_db_table_prefix .'groups_axo_map';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'aro_groups';
				$groups_map_table = $this->_db_table_prefix .'aro_groups_map';
				$groups_object_map_table = $this->_db_table_prefix .'groups_aro_map';
				break;
		}

		$this->debug_text("del_group(): ID: $group_id Reparent Children: $reparent_children Group Type: $group_type");

		if (empty($group_id) ) {
			$this->debug_text("del_group(): Group ID ($group_id) is empty, this is required");
			return false;
		}

		// Get details of this group
		$this->db->setQuery( 'SELECT group_id, parent_id, name, lft, rgt FROM '. $table .' WHERE group_id='. $group_id );
		$group_details = $this->db->loadRow($query);

		if (!is_array($group_details)) {
			$this->debug_db('del_group: get group details');
			return false;
		}

		$parent_id = $group_details[1];

		$left = $group_details[3];
		$right = $group_details[4];

		// <mos> $this->db->BeginTrans();

		// grab list of all children
		$children_ids = $this->get_group_children($group_id, $group_type, 'RECURSE');

		// prevent deletion of root group & reparent of children if it has more than one immediate child
		if ($parent_id == 0) {
			$this->db->setQuery( 'SELECT count(*) FROM '. $table .' WHERE parent_id='. $group_id );
			$child_count = $this->db->loadResult($query);

			if ($child_count > 1 && $reparent_children) {
				$this->debug_text ('del_group (): You cannot delete the root group and reparent children, this would create multiple root groups.');
				return FALSE;
			}
		}

		$success = FALSE;

		/*
		 * Handle children here.
		 */
		switch (TRUE) {
			// there are no child groups, just delete group
			case !is_array($children_ids):
			case count($children_ids) == 0:
				// remove acl maps
			/* Reapply when ACL's implemented
				$this->db->setQuery( 'DELETE FROM '. $groups_map_table .' WHERE group_id='. $group_id );
				$rs = $this->db->Execute($query);

				if (!is_object($rs)) {
					break;
				}*/

				// remove group object maps
				$this->db->setQuery( 'DELETE FROM '. $groups_object_map_table .' WHERE group_id='. $group_id );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// remove group
				$this->db->setQuery( 'DELETE FROM '. $table .' WHERE group_id='. $group_id );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// move all groups right of deleted group left by width of deleted group
				$this->db->setQuery( 'UPDATE '. $table .' SET lft=lft-'. ($right-$left+1) .' WHERE lft>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$this->db->setQuery( 'UPDATE '. $table .' SET rgt=rgt-'. ($right-$left+1) .' WHERE rgt>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$success = TRUE;
				break;
			case $reparent_children == TRUE:
				// remove acl maps
			/* Reapply when ACL's implemented
				$query = 'DELETE FROM '. $groups_map_table .' WHERE group_id='. $group_id;
				$rs = $this->db->Execute($query);

				if (!is_object($rs)) {
					break;
				}*/

				// remove group object maps
				$this->db->setQuery( 'DELETE FROM '. $groups_object_map_table .' WHERE group_id='. $group_id );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// remove group
				$this->db->setQuery( 'DELETE FROM '. $table .' WHERE group_id='. $group_id );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// set parent of immediate children to parent group
				$this->db->setQuery( 'UPDATE '. $table .' SET parent_id='. $parent_id .' WHERE parent_id='. $group_id );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// move all children left by 1
				$this->db->setQuery( 'UPDATE '. $table .' SET lft=lft-1, rgt=rgt-1 WHERE lft>'. $left .' AND rgt<'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// move all groups right of deleted group left by 2
				$this->db->setQuery( 'UPDATE '. $table .' SET lft=lft-2 WHERE lft>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$this->db->setQuery( 'UPDATE '. $table .' SET rgt=rgt-2 WHERE rgt>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$success = TRUE;
				break;
			default:
				// make list of group and all children
				$group_ids = $children_ids;
				$group_ids[] = $group_id;

				// remove acl maps
			/* Reapply when ACL's implemented
				$query = 'DELETE FROM '. $groups_map_table .' WHERE group_id IN ('. implode (',', $group_ids) .')';
				$rs = $this->db->Execute($query);

				if (!is_object($rs)) {
					break;
				}*/

				// remove group object maps
				$this->db->setQuery( 'DELETE FROM '. $groups_object_map_table .' WHERE group_id IN ('. implode (',', $group_ids) .')' );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// remove groups
				$this->db->setQuery( 'DELETE FROM '. $table .' WHERE group_id IN ('. implode (',', $group_ids) .')' );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				// move all groups right of deleted group left by width of deleted group
				$this->db->setQuery( 'UPDATE '. $table .' SET lft=lft-'. ($right - $left + 1) .' WHERE lft>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$this->db->setQuery( 'UPDATE '. $table .' SET rgt=rgt-'. ($right - $left + 1) .' WHERE rgt>'. $right );
				$rs = $this->db->query();

				if (!$rs) {
					break;
				}

				$success = TRUE;
		}

		// if the delete failed, rollback the trans and return false
		if (!$success) {

			$this->debug_db('del_group');
			$this->db->RollBackTrans();
			return false;
		}

		$this->debug_text("del_group(): deleted group ID: $group_id");
		// <mos> $this->db->CommitTrans();

		if ($this->_caching == TRUE AND $this->_force_cache_expire == TRUE) {
			//Expire all cache.
			$this->Cache_Lite->clean('default');
		}

		return true;

	}


	/*
	 *
	 * Objects (ACO/ARO/AXO)
	 *
	 */
	/*======================================================================*\
		Function:	get_object()
		Purpose:	Grabs all Objects's in the database, or specific to a section_value
	\*======================================================================*/
	function get_object($section_value = null, $return_hidden=1, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			default:
				$this->debug_text('get_object(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_object(): Section Value: $section_value Object Type: $object_type");

		$$this->db->setQuery( 'SELECT '. $object_type .'_id FROM '. $table );

		$where = array();

		if (!empty($section_value)) {
			$where[] = 'section_value='. $this->db->getEscaped($section_value);
		}

		if ($return_hidden==0) {
			$where[] = 'hidden=0';
		}

		if (!empty($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}

		$rs = $this->db->loadResultArray();

		if (!is_array($rs)) {
			$this->debug_db('get_object');
			return false;
		}

		// Return Object IDs
		return $rs;
	}

	/*======================================================================*\
		Function:	get_objects ()
		Purpose:	Grabs all Objects in the database, or specific to a section_value
					returns format suitable for add_acl and is_conflicting_acl
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_object_data()
		Purpose:	Gets all data pertaining to a specific Object.
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_object_groups()
		Purpose:	Gets the group_id's for the given the section_value AND value
		of the object.
	\*======================================================================*/
	function get_object_groups($object_section_value, $object_value, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aro':
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'groups_aro_map';
				$object_table = $this->_db_table_prefix .'aro';
				$group_table = $this->_db_table_prefix .'aro_groups';
				break;
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'groups_axo_map';
				$object_table = $this->_db_table_prefix .'axo';
				$group_table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$this->debug_text('get_object_groups(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_object_groups(): Section Value: $object_section_value Value: $object_value Object Type: $object_type");

		$object_section_value = trim($object_section_value);
		$object_value = trim($object_value);

		if (empty($object_section_value) AND empty($object_value) ) {
			$this->debug_text("get_object_groups(): Section Value ($object_section_value) AND value ($object_value) is empty, this is required");
			return false;
		}

		if (empty($object_type) ) {
			$this->debug_text("get_object_groups(): Object Type ($object_type) is empty, this is required");
			return false;
		}
//			SELECT		g.group_id,o.'. $group_type .'_id,(gm.group_id IS NOT NULL) AS member

		$this->db->setQuery( '
			SELECT		g.group_id,o.'. $group_type .'_id,(gm.group_id IS NOT NULL) AS member
			FROM		'. $group_table .' g
			LEFT JOIN	'. $table .' gm ON gm.group_id=g.group_id
			LEFT JOIN	'. $object_table .' o ON o.'. $group_type .'_id = gm.'. $group_type .'_id
			WHERE		(o.section_value=\''. $this->db->getEscaped($object_section_value) .'\' AND o.value=\''. $this->db->getEscaped($object_value) .'\')'
		);
		$rs = $this->db->loadResultArray();

		if ($this->db->getErrorNum()) {
			$this->debug_db('get_object_id');
			return false;
		}

		//Return the array of group id's
		return $rs;
	}

	/*======================================================================*\
		Function:	get_object_id()
		Purpose:	Gets the object_id given the section_value AND value of the object.
	\*======================================================================*/
	function get_object_id($section_value, $value, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			default:
				$this->debug_text('get_object_id(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_object_id(): Section Value: $section_value Value: $value Object Type: $object_type");

		$section_value = trim($section_value);
		$value = trim($value);

		if (empty($section_value) AND empty($value) ) {
			$this->debug_text("get_object_id(): Section Value ($value) AND value ($value) is empty, this is required");
			return false;
		}

		if (empty($object_type) ) {
			$this->debug_text("get_object_id(): Object Type ($object_type) is empty, this is required");
			return false;
		}

		$this->db->setQuery( 'SELECT '. $object_type .'_id FROM '. $table .' WHERE section_value=\''. $this->db->getEscaped($section_value) .'\' AND value=\''. $this->db->getEscaped($value) .'\''
		);
		$rs = $this->db->loadRowList();

		if ($this->db->getErrorNum()) {
			$this->debug_db('get_object_id');
			return false;
		}

		$row_count = count( $rs );

		if ($row_count > 1) {
			$this->debug_text("get_object_id(): Returned $row_count rows, can only return one. This should never happen, the database may be missing a unique key.");
			return false;
		}

		if ($row_count == 0) {
			$this->debug_text("get_object_id(): Returned $row_count rows");
			return false;
		}

		$row = $rs[0];

		//Return the ID.
		return $row[0];
	}

	/*======================================================================*\
		Function:	get_object_section_value()
		Purpose:	Gets the object_section_value given object id
	\*======================================================================*/

	/*======================================================================*\
		Function:	get_object_groups()
		Purpose:	Gets all groups an object is a member of.
					If $option == 'RECURSE' it will get all ancestor groups.
					defaults to only get direct parents.
	\*======================================================================*/

	/*======================================================================*\
		Function:	add_object()
		Purpose:	Inserts a new object
	\*======================================================================*/
	function add_object($section_value, $name, $value=0, $order=0, $hidden=0, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				$object_sections_table = $this->_db_table_prefix .'aco_sections';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				$object_sections_table = $this->_db_table_prefix .'aro_sections';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				$object_sections_table = $this->_db_table_prefix .'axo_sections';
				break;
			default:
				$this->debug_text('add_object(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("add_object(): Section Value: $section_value Value: $value Order: $order Name: $name Object Type: $object_type");

		$section_value = trim($section_value);
		$name = trim($name);
		$value = trim($value);
		$order = trim($order);

		if ($order == NULL OR $order == '') {
			$order = 0;
		}

		if (empty($name) OR empty($section_value) ) {
			$this->debug_text("add_object(): name ($name) OR section value ($section_value) is empty, this is required");
			return false;
		}

		if (strlen($name) >= 255 OR strlen($value) >= 230 ) {
			$this->debug_text("add_object(): name ($name) OR value ($value) is too long.");
			return false;
		}

		if (empty($object_type) ) {
			$this->debug_text("add_object(): Object Type ($object_type) is empty, this is required");
			return false;
		}

		// Test to see if the section is invalid or object already exists.
		$this->db->setQuery( '
			SELECT		(o.'. $object_type .'_id IS NOT NULL) AS object_exists
			FROM		'. $object_sections_table .' s
			LEFT JOIN	'. $table .' o ON (s.value=o.section_value AND o.value=\''. $this->db->getEscaped($value) .'\')
			WHERE		s.value=\''. $this->db->getEscaped($section_value). '\''
		);

		$rows = $this->db->loadRowList();
		if ($this->db->getErrorNum()) {
			$this->debug_db('add_object');
			return FALSE;
		}

		if (count( $rows ) != 1) {
			// Section is invalid
			$this->debug_text("add_object(): Section Value: $section_value Object Type ($object_type) does not exist, this is required");
			return false;
		}

		$row = $rows[0];

		if ($row[0] == 1) {
			//Object is already created.
			return true;
		}

		$insert_id = $this->db->GenID($this->_db_table_prefix.$object_type.'_seq',10);
		$this->db->setQuery( "INSERT INTO $table ({$object_type}_id,section_value,value,order_value,name,hidden) VALUES($insert_id,'$section_value','$value','$order','$name','$hidden')" );

		if (!$this->db->query()) {
			$this->debug_db('add_object');
			return false;
		}

		$insert_id = $this->db->insertid();
		$this->debug_text("add_object(): Added object as ID: $insert_id");
		return $insert_id;
	}
	/*======================================================================*\
		Function:	edit_object()
		Purpose:	Edits a given Object
	\*======================================================================*/
	function edit_object($object_id, $section_value, $name, $value=0, $order=0, $hidden=0, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				$object_map_table = 'aco_map';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				$object_map_table = 'aro_map';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				$object_map_table = 'axo_map';
				break;
		}

		$this->debug_text("edit_object(): ID: $object_id, Section Value: $section_value, Value: $value, Order: $order, Name: $name, Object Type: $object_type");

		$section_value = trim($section_value);
		$name = trim($name);
		$value = trim($value);
		$order = trim($order);

		if (empty($object_id) OR empty($section_value) ) {
			$this->debug_text("edit_object(): Object ID ($object_id) OR Section Value ($section_value) is empty, this is required");
			return false;
		}

		if (empty($name) ) {
			$this->debug_text("edit_object(): name ($name) is empty, this is required");
			return false;
		}

		if (empty($object_type) ) {
			$this->debug_text("edit_object(): Object Type ($object_type) is empty, this is required");
			return false;
		}

		//Get old value incase it changed, before we do the update.
		$this->db->setQuery( 'SELECT value, section_value FROM '. $table .' WHERE '. $object_type .'_id='. $object_id );
		$old = $this->db->loadRow();

		$this->db->setQuery( '
			UPDATE	'. $table .'
			SET		section_value=\''. $this->db->getEscaped($section_value) .'\',
					value='. $this->db->getEscaped($value) .',
					order_value='. $this->db->getEscaped($order) .',
					name=\''. $this->db->getEscaped($name) .'\',
					hidden='. $hidden .'
			WHERE	'. $object_type .'_id='. $object_id
		);
		$this->db->query();

		if (!$this->db->getErrorNum()) {
			$this->debug_db('edit_object');
			return false;
		}

		$this->debug_text('edit_object(): Modified '. strtoupper($object_type) .' ID: '. $object_id);

		if ($old[0] != $value OR $old[1] != $section_value) {
			$this->debug_text("edit_object(): Value OR Section Value Changed, update other tables.");

			$this->db->setQuery( '
				UPDATE	'. $object_map_table .'
				SET		value=\''. $this->db->getEscaped($value) .'\',
						section_value=\''. $this->db->getEscaped($section_value) .'\'
				WHERE	section_value=\''. $this->db->getEscaped($old[1]) .'\'
					AND	value='. $this->db->getEscaped($old[0])
			);
			$this->db->query();

		if (!$this->db->getErrorNum()) {
				$this->debug_db('edit_object');
				return FALSE;
			}

			$this->debug_text ('edit_object(): Modified Map Value: '. $value .' Section Value: '. $section_value);
		}

		return TRUE;
	}

	/*======================================================================*\
		Function:	del_object()
		Purpose:	Deletes a given Object and, if instructed to do so,
						erase all referencing objects
						ERASE feature by: Martino Piccinato
	\*======================================================================*/
	function del_object($object_id, $object_type=NULL, $erase=FALSE) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				$object_map_table = $this->_db_table_prefix .'aco_map';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				$object_map_table = $this->_db_table_prefix .'aro_map';
				$groups_map_table = $this->_db_table_prefix .'aro_groups_map';
				$object_group_table = $this->_db_table_prefix .'groups_aro_map';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				$object_map_table = $this->_db_table_prefix .'axo_map';
				$groups_map_table = $this->_db_table_prefix .'axo_groups_map';
				$object_group_table = $this->_db_table_prefix .'groups_axo_map';
				break;
			default:
				$this->debug_text('del_object(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("del_object(): ID: $object_id Object Type: $object_type, Erase all referencing objects: $erase");

		if (empty($object_id) ) {
			$this->debug_text("del_object(): Object ID ($object_id) is empty, this is required");
			return false;
		}

		if (empty($object_type) ) {
			$this->debug_text("del_object(): Object Type ($object_type) is empty, this is required");
			return false;
		}

		// <mos> $this->db->BeginTrans();

		// Get Object section_value/value (needed to look for referencing objects)
		$this->db->setQuery( 'SELECT section_value,value FROM '. $table .' WHERE '. $object_type .'_id='. $object_id );
		$object = $this->db->loadRow();

		if (empty($object)) {
			$this->debug_text('del_object(): The specified object ('. strtoupper($object_type) .' ID: '. $object_id .') could not be found.<br />SQL = '.$this->db->stderr());
			return FALSE;
		}

		$section_value = $object[0];
		$value = $object[1];

		// Get ids of acl referencing the Object (if any)
		$this->db->setQuery( "SELECT acl_id FROM $object_map_table WHERE value='$value' AND section_value='$section_value'" );
		$acl_ids = $this->db->loadResultArray();

		if ($erase) {
			// We were asked to erase all acl referencing it

			$this->debug_text("del_object(): Erase was set to TRUE, delete all referencing objects");

			if ($object_type == "aro" OR $object_type == "axo") {
				// The object can be referenced in groups_X_map tables
				// in the future this branching may become useless because
				// ACO might me "groupable" too

				// Get rid of groups_map referencing the Object
				$this->db->setQuery( 'DELETE FROM '. $object_group_table .' WHERE '. $object_type .'_id='. $object_id );
				$rs = $this->db->query();

				if (!$rs) {
					$this->debug_db('edit_object');
					// <mos> $this->db->RollBackTrans();
					return false;
				}
			}

			if ($acl_ids) {
				//There are acls actually referencing the object

				if ($object_type == 'aco') {
					// I know it's extremely dangerous but
					// if asked to really erase an ACO
					// we should delete all acl referencing it
					// (and relative maps)

					// Do this below this branching
					// where it uses $orphan_acl_ids as
					// the array of the "orphaned" acl
					// in this case all referenced acl are
					// orhpaned acl

					$orphan_acl_ids = $acl_ids;
				} else {
					// The object is not an ACO and might be referenced
					// in still valid acls regarding also other object.
					// In these cases the acl MUST NOT be deleted

					// Get rid of $object_id map referencing erased objects
					$this->db->setQuery( "DELETE FROM $object_map_table WHERE section_value='$section_value' AND value='$value'" );
					$rs = $this->db->query($query);

					if (!$rs) {
						$this->debug_db('edit_object');
						$this->db->RollBackTrans();
						return false;
					}

					// Find the "orphaned" acl. I mean acl referencing the erased Object (map)
					// not referenced anymore by other objects

					$sql_acl_ids = implode(",", $acl_ids);

					$this->db->setQuery( '
						SELECT		a.id
						FROM		'. $this->_db_table_prefix .'acl a
						LEFT JOIN	'. $object_map_table .' b ON a.id=b.acl_id
						'./* <mos return for full acl stuff> LEFT JOIN	'. $groups_map_table .' c ON a.id=c.acl_id*/'
						WHERE		value IS NULL
							AND		section_value IS NULL
							AND		group_id IS NULL
							AND		a.id in ('. $sql_acl_ids .')');
					$orphan_acl_ids = $this->db->loadResultArray();

				} // End of else section of "if ($object_type == "aco")"

				if ($orphan_acl_ids) {
				// If there are orphaned acls get rid of them

					foreach ($orphan_acl_ids as $acl) {
						$this->del_acl($acl);
					}
				}

			} // End of if ($acl_ids)

			// Finally delete the Object itself
			$this->db->setQuery( "DELETE FROM $table WHERE {$object_type}_id='$object_id'" );
			$rs = $this->db->query();

			if (!$rs) {
				$this->debug_db('edit_object');
				// <mos> $this->db->RollBackTrans();
				return false;
			}

			// <mos> $this->db->CommitTrans();
			return true;

		} // End of "if ($erase)"

		$groups_ids = FALSE;

		if ($object_type == 'axo' OR $object_type == 'aro') {
			// If the object is "groupable" (may become unnecessary,
			// see above

			// Get id of groups where the object is assigned:
			// you must explicitly remove the object from its groups before
			// deleting it (don't know if this is really needed, anyway it's safer ;-)

			$this->db->setQuery( 'SELECT group_id FROM '. $object_group_table .' WHERE '. $object_type .'_id='. $object_id );
			$groups_ids = $this->db->loadResultArray();
		}

		if ( ( isset($acl_ids) AND $acl_ids !== FALSE ) OR ( isset($groups_ids) AND $groups_ids !== FALSE) ) {
			// The Object is referenced somewhere (group or acl), can't delete it

			$this->debug_text("del_object(): Can't delete the object as it is being referenced by GROUPs (".@implode($group_ids).") or ACLs (".@implode($acl_ids,",").")");

			return false;
		} else {
			// The Object is NOT referenced anywhere, delete it

			$this->db->setQuery( "DELETE FROM $table WHERE {$object_type}_id='$object_id'" );
			$this->db->query();

			if ( $this->db->getErrorNum() ) {
				$this->debug_db('edit_object');
				// <mos> $this->db->RollBackTrans();
				return false;
			}

			// <mos> $this->db->CommitTrans();
			return true;
		}

		return false;
	}

	/*
	 *
	 * Object Sections
	 *
	 */

	/*======================================================================*\
		Function:	get_object_section_section_id()
		Purpose:	Gets the object_section_id given the name AND/OR value of the section.
					Will only return one section id, so if there are duplicate names it will return false.
	\*======================================================================*/

	/*======================================================================*\
		Function:	add_object_section()
		Purpose:	Inserts an object Section
	\*======================================================================*/

	/*======================================================================*\
		Function:	edit_object_section()
		Purpose:	Edits a given Object Section
	\*======================================================================*/

	/*======================================================================*\
		Function:	del_object_section()
		Purpose:	Deletes a given Object Section and, if explicitly
						asked, all the section objects
						ERASE feature by: Martino Piccinato
	\*======================================================================*/

	/*
	 *
	 * Joomla! Utility Methods
	 *
	 */

	/*======================================================================*\
		Function:	has_group_parent
		Purpose:	Checks whether the 'source' group is a child of the 'target'
	\*======================================================================*/
	function is_group_child_of( $grp_src, $grp_tgt, $group_type='ARO' ) {
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
			$this->db->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.group_id=$grp_src AND g2.group_id=$grp_tgt"
			);
		} else if (is_string( $grp_src ) && is_string($grp_tgt)) {
			$this->db->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.name='$grp_src' AND g2.name='$grp_tgt'"
			);
		} else if (is_int( $grp_src ) && is_string($grp_tgt)) {
			$this->db->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.group_id='$grp_src' AND g2.name='$grp_tgt'"
			);
		} else {
			$this->db->setQuery( "SELECT COUNT(*)"
				. "\nFROM $table AS g1"
				. "\nLEFT JOIN $table AS g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt"
				. "\nWHERE g1.name=$grp_src AND g2.group_id='$grp_tgt'"
			);
		}

		return $this->db->loadResult();
	}

	function getAroGroup( $value ) {
		return $this->_getGroup( 'aro', $value );
	}

	function _getGroup( $type, $value ) {
		global $database;

		$database->setQuery( "SELECT g.*"
			. "\nFROM #__core_acl_{$type}_groups AS g"
			. "\nINNER JOIN #__core_acl_groups_{$type}_map AS gm ON gm.group_id = g.group_id"
			. "\nINNER JOIN #__core_acl_{$type} AS ao ON ao.{$type}_id = gm.{$type}_id"
			. "\nWHERE ao.value='$value'"
		);
		$obj = null;
		$database->loadObject( $obj );
		return $obj;
	}

	function _getAbove() {
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
		if ($root->lft+$root->rgt != 0) {
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

	function get_group_children_tree( $root_id=null, $root_name=null, $inclusive=true ) {
		global $database;

		$tree = gacl_api::_getBelow( '#__core_acl_aro_groups',
			'g1.group_id, g1.name, COUNT(g2.name) AS level',
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
			$list[$i] = mosHTML::makeOption( $tree[$i]->group_id, $shim.$twist.$tree[$i]->name );
			if ($tree[$i]->level < @$tree[$i-1]->level) {
				$indents[$tree[$i]->level+1] = '.&nbsp;';
			}
		}

		ksort($list);
		return $list;
	}
}

class mosARO extends mosDBTable {
/** @var int Primary key */
	var $aro_id=null;
	var $section_value=null;
	var $value=null;
	var $order_value=null;
	var $name=null;
	var $hidden=null;

	function mosARO( &$db ) {
		$this->mosDBTable( '#__core_acl_aro', 'aro_id', $db );
	}

/**
* Utility function for returning groups
*/

}

class mosAroGroup extends mosDBTable {
/** @var int Primary key */
	var $group_id=null;
	var $parent_id=null;
	var $name=null;
	var $lft=null;
	var $rgt=null;

	function mosAroGroup( &$db ) {
		$this->mosDBTable( '#__core_acl_aro_groups', 'group_id', $db );
	}
}

?>