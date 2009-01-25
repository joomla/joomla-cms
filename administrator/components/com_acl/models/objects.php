<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_prototypelist.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessModelObjects extends AccessModelPrototypeList
{
	/**
	 * Valid types
	 */
	function isValidType($type)
	{
		$types	= array('acl', 'aro', 'aco', 'axo');
		return in_array($type, $types);
	}

	/**
	 * Gets a list of objects
	 *
	 * @param	boolean	True to resolve foreign keys
	 *
	 * @return	string
	 */
	function _getListQuery($resolveFKs = false)
	{
		if (empty($this->_list_query))
		{
			$db			= &$this->getDBO();
			$query		= new JQuery;
			$section	= $this->getState('list.section_value');
			$type		= strtolower($this->getState('list.object_type'));
			$aclType	= $this->getState('list.acl_type');
			$groupId	= $this->getState('list.group_id');
			$select		= $this->getState('list.select', 'a.*');
			$search		= $this->getState('list.search');
			$where		= $this->getState('list.where');
			$orderBy	= $this->getState('list.order');

			$query->select($select);
			$query->from('#__core_acl_'.$type.' AS a');

			if ($resolveFKs)
			{
				// If a user object, resolve the username
				if ($type == 'aro') {
					$query->select('u.username');
					$query->join('LEFT', '#__users AS u ON u.id = a.value');
				}

				// Get the section name of the object
				$query->select('s.name AS section_name');
				$query->join('LEFT', '#__core_acl_'.$type.'_sections AS s ON s.value = a.section_value');

				if ($type == 'aro' OR $type == 'axo') {
					// Count the number of groups or access levels the item is in
					$query->select('COUNT(map.group_id) AS group_count');
					$query->join('LEFT', '#__core_acl_groups_'.$type.'_map AS map ON map.'.$type.'_id=a.id');
					$query->group('a.id');

					// Collect the group names as a new-line seperated string
					$query->select('GROUP_CONCAT(g2.name SEPARATOR '.$db->Quote("\n").') AS group_names');
					$query->join('LEFT', '#__core_acl_'.$type.'_groups AS g2 ON g2.id = map.group_id');
				}
			}

			// Filter on a section
			if ($section) {
				if (is_array($section)) {
					foreach ($section as $k => $v) {
						$section[$k] = $db->Quote($v);
					}
					$query->where('a.section_value IN ('.implode(',', $section).')');
				}
				else {
					$query->where('a.section_value = '.$db->Quote($section));
				}
			}

			// Filter by a search
			if ($search) {
				// id:123 will search for a specific ID
				if (strpos($search, 'id:') === 0) {
					$query->where('a.id = '.(int) substr($search, 3));
				}
				// value:123 will search for a specific value of the `value` field
				else if (strpos($search, 'value:') === 0) {
					$query->where('a.value = '.$db->Quote(substr($search, 6)));
				}
				else {
					// Otherwise search in the name, and for a user, search in the username
					$search = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
					if ($type == 'aro') {
						$query->where('(a.name LIKE '.$search.' OR u.username LIKE '.$search.')');
					}
					else {
						$query->where('a.name LIKE '.$search);
					}
				}
			}

			// Filter on the Group ID
			if ($groupId) {
				if ($type == 'aro' OR $type == 'axo') {
					$query->join('LEFT', '#__core_acl_groups_'.$type.'_map AS map2 ON map2.'.$type.'_id=a.id');
					$query->where('map2.group_id = '.(int) $groupId);
				}
			}

			if ($aclType && $type == 'aco') {
				$query->where('a.acl_type = '.(int) $aclType);
			}

			// An abritrary where clause
			if ($where) {
				$query->where($where);
			}

			if ($orderBy) {
				$query->order($this->_db->getEscaped($orderBy));
			}

			//echo nl2br($query->toString()).'<hr />';
			$this->_list_query = (string) $query;
		}

		return $this->_list_query;
	}
}