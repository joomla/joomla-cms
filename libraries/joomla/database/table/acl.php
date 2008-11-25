<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableACL extends JTable
{
	/**
	 * @var int unsigned
	 */
	protected $id = null;
	/**
	 * @var varchar
	 */
	protected $section_value = null;
	/**
	 * @var int unsigned
	 */
	protected $allow = null;
	/**
	 * @var int unsigned
	 */
	protected $enabled = null;
	/**
	 * @var varchar
	 */
	protected $return_value = null;
	/**
	 * @var varchar
	 */
	protected $note = null;
	/**
	 * @var int unsigned
	 */
	protected $updated_date = null;
	/**
	 * @var int unsigned
	 */
	protected $acl_type = null;

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__core_acl_acl', 'id', $db);
	}
	/**
	 * Validate the internal data
	 *
	 * @return	boolean
	 */
	function check()
	{
		// Sanitize and validate group name.
		if (empty($this->note)) {
			$this->setError(JText::_('Error Acl Table Invalid Note'));
			return false;
		}

		// Check that section exists
		$this->_db->setQuery(
			'SELECT id FROM $__core_acl_acl_sections WHERE value = '.$this->_db->quote($this->section_value)
		);
		$id = $this->_db->loadResult();
		if (empty($id)) {
			$this->setError(JText::_('Error Acl Table Invalid Section'));
			return false;
		}

		return true;
	}

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 * @access	public
	 * @param	boolean		If false, null object variables are not updated
	 * @return	boolean 	True successful, false otherwise and an internal error message is set`
	 */
	function store($updateNulls = false)
	{
		// Flag if this is a new record
		$isNew	= empty($this->id);

		if ($result = parent::store($updateNulls)) {
		}

		return $result;
	}

	/**
	 * Delete a record from the database table
	 *
	 * @param	int $id			An optional ID column value. If not supplied, the internal property is used
	 *
	 * @return	boolean			True if successful otherwise returns and error message
	 *
	 * @access	public
	 */
	function delete($id = null)
	{
		if (empty($id)) {
			if (empty($this->id)) {
				$this->setError(JText::_('Error Acl Table Invalid Id to delete'));
				return false;
			}
			else {
				$id = $this->id;
			}
		}

		// Load the existing data
		if (!$this->load($id)) {
			return false;
		}

		if (!$this->_deleteReferences()) {
			return false;
		}

		return parent::delete($id);
	}

	/**
	 * Find the references to this object
	 *
	 * This method can only operate on a previously loaded object.
	 *
	 * @return	JAclReferences
	 * @access	public
	 */
	function &findReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Table Invalid properties to find references'));
			return false;
		}

		$false = false;

		if (empty($this->_references))
		{
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'acl'.DS.'references.php';

			$this->_references = new JAclReferences;

			// Find the references to mapped ACO's
			$this->_db->setQuery(
				'SELET m.aco_id, a.section_value'.
				' FROM #__core_acl_aco_map AS m'.
				' LEFT JOIN #__core_acl_acl AS a ON a.id = m.acl_id'.
				' WHERE m.acl_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAco($acl->section_value, $acl->aco_id);
				}
			}

			// Find the references to mapped ARO's
			$this->_db->setQuery(
				'SELET m.aro_id, a.section_value'.
				' FROM #__core_acl_aro_map AS m'.
				' LEFT JOIN #__core_acl_acl AS a ON a.id = m.acl_id'.
				' WHERE acl_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAro($acl->section_value, $acl->aro_id);
				}
			}

			// Find the references to mapped AXO's
			$this->_db->setQuery(
				'SELET m.axo_id, a.section_value'.
				' FROM #__core_acl_axo_map AS m'.
				' LEFT JOIN #__core_acl_axo AS a ON a.id = m.acl_id'.
				' WHERE acl_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAxo($acl->section_value, $acl->axo_id);
				}
			}

			// Find the references to mapped ARO's
			$this->_db->setQuery(
				'SELET m.group_id'.
				' FROM #__core_acl_aro_groups_map AS m'.
				' WHERE acl_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				$this->_references->addAroGroup($result);
			}

			// Find the references to mapped AXO's
			$this->_db->setQuery(
				'SELET m.group_id'.
				' FROM #__core_acl_axo_groups_map AS m'.
				' WHERE acl_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				$this->_references->addAxoGroup($result);
			}
		}

		return $this->_references;
	}

	/**
	 * Updates all of the references data for the ACL
	 *
	 * @param	JAclReferences $references
	 * @return	boolean
	 */
	function updateReferences($references)
	{
		if (!is_a($references, 'JAclReferences')) {
			$this->setError(JText::_('Error Acl Table invalid references object'));
			return false;
		}

		// Check for conflicting ACLs.
		if ($this->findConflicts($references, array($this->id))) {
			return false;
		}

		if (!$this->deleteReferences()) {
			return false;
		}

		$maps = array(
			'aco' => $references->getAcos(true),
			'aro' => $references->getAros(true),
			'axo' => $references->getAxos(true)
		);

		// Insert ACO/ARO/AXO mappings
		foreach ($maps as $type => $map)
		{
			if (empty($map)) {
				continue;
			}

			$tuples		= array();

			foreach ($map as $sectionValue => $ids)
			{
				if (empty($ids)) {
					continue;
				}

				// Objects come in as Id's, so collect the Id's and look them up in one go
				foreach (array_unique($ids) as $id)
				{
					if (empty($id)) {
						$this->setError(JText::sprintf('Error Acl Table Adding Invalid ID; % Section %s', $type, $sectionValue));
						return false;
					}
					$tuples[$id] = $this->_db->quote($sectionValue);
				}
			}

			if (empty($tuples)) {
				continue;
			}

			// Create the lookup array
			$this->_db->setQuery(
				'SELECT value, id'.
				' FROM #__core_acl_'.$type.
				' WHERE id IN ('.implode(',', $tuples).')'
			);
			$lookup = $this->_db->loadRowList(0);
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Assemble the mapping tuples
			foreach ($tuples as $id => $tuple) {
				$tuples[$id] = '('.(int) $this->id.','.$tuple.','.$lookup[$id][0].')';
			}

			// Add the tuples
			$this->_db->setQuery(
				'INSERT INTO #__core_acl_'.$type.'_map (acl_id,section_value,value)'.
				' VALUES '.implode(',', $tuples)
			);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		$maps = array(
			'aro' => $references->getAroGroups(true),
			'axo' => $references->getAxoGroups(true)
		);

		// Insert ARO/AXO GROUP mappings
		foreach ($maps as $type => $groups)
		{
			if (empty($map)) {
				continue;
			}

			$tuples = array();

			foreach ($groups as $groupId)
			{
				// @todo Check the groupId is valid
				$tuples[] = '('.(int) $this->id.','.$groupId.')';
			}

			// Add the tuples
			$this->_db->setQuery(
				'INSERT INTO #__core_acl_'.$type.'_groups_map (acl_id,group_id)'.
				' VALUES '.implode(',', $tuples)
			);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		// @todo Clean caching here

		return true;
	}

	function search()
	{

	}

	/**
	 * Check for potential conflicts.
	 *
	 * Ignore user groups, as groups will almost always have "conflicting" ACLs. Thats part of inheritance.
	 *
	 * @param	JAclReferences $references
	 * @param	array $ignoreAclIds
	 */
	function findConflicts(&$references, $ignoreAclIds = array())
	{
		$acos = $references->getAcos(true);
		$aros = $references->getAros(true);
		$axos = $references->getAros(true);

		$query  = new JXQuery;
		$query->select('a.id');
		$query->from('#__core_acl_acl AS a');
		$query->join('LEFT', '#__core_acl_aco_map AS ac ON ac.acl_id=a.id');
		$query->join('LEFT', '#__core_acl_aro_map AS ar ON ar.acl_id=a.id');
		$query->join('LEFT', '#__core_acl_axo_map AS ax ON ax.acl_id=a.id');
		$query->join('LEFT', '#__core_acl_axo_groups_map AS axg ON axg.acl_id=a.id');
		$query->join('LEFT', '#__core_acl_axo_groups AS xg ON xg.id=axg.group_id');
		$sql	= $query->toString();

		// Scan ACOs
		foreach ($acos as $acoSectionValue => $acoValues)
		{
			if (empty($acoValues)) {
				continue;
			}

			$wheres = array(
				'ac2' => '(ac.section_value='. $this->db->quote($acoSectionValue) .' AND ac.value IN (\''. implode ('\',\'', $acoValues) .'\'))'
			);

			// Scan AROs
			foreach ($aros as $aroSectionValue => $aroValues)
			{
				if (empty($aroValues)) {
					continue;
				}

				$wheres['ar2'] = '(ar.section_value='. $this->db->quote($aroSectionValue) .' AND ar.value IN (\''. implode ('\',\'', $aroValues) .'\'))';

				if (!empty($axos))
				{
					// Scan AXOs
					foreach ($axos as $axoSectionValue => $axoValues)
					{
						if (empty($axoValues)) {
							continue;
						}

						$wheres['ax1']	= 'ax.acl_id=a.id';
						$wheres['ax2']	= '(ax.section_value='. $this->db->quote($axoSectionValue) .' AND ax.value IN (\''. implode ('\',\'', $axoValues) .'\'))';
						$this->_db->setQuery($sql.' WHERE '.implode(' AND ', $wheres));
						$result = $this->db->loadResultArray($sql.$where);

						if (!empty($result))
						{
							if (is_array($ignoreAclIds)) {
								$result = array_diff($result, $ignoreAclIds);
							}

							if (!empty($result)) {
								$this->setError('Conflicts Found ACLs: '.implode(', ', $result));
								return true;
							}
						}
					}
				}
				else
				{
					$wheres['ax1'] = '(ax.section_value IS NULL AND ax.value IS NULL)';
					$wheres['ax2'] = 'xg.name IS NULL';
					$this->_db->setQuery($sql.' WHERE '.implode(' AND ', $wheres));
					$result = $this->db->loadResultArray($sql.$where);

					if (!empty($result))
					{
						if (is_array($ignoreAclIds)) {
							$result = array_diff($result, $ignoreAclIds);
						}

						if (!empty($result)) {
							$this->setError('Conflicts Found ACLs: '.implode(', ', $result));
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Deletes referenced data
	 *
	 * This method can only operate on a previously loaded object.
	 * Grouped object must extend this method to also clean up groups
	 *
	 * @return	boolean
	 * @access	protected
	 */
	function _deleteReferences()
	{
		if (empty($this->id)) {
			$this->setError(JText::_('Error Acl Table Invalid Id to delete references'));
			return false;
		}

		// Delete all mappings to this ACL
		foreach (array('aco_map', 'aro_map', 'axo_map', 'aro_groups_map', 'axo_groups_map') as $map)
		{
			$this->_db->setQuery(
				'DELETE FROM #__core_acl_'.$map.' WHERE acl_id = '. (int) $this->id
			);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		// Gosh that was easy compared to the rest!

		return true;
	}
}

