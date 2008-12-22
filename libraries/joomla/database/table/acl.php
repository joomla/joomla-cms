<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableAcl extends JTable
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

	/** @var int */
	protected $name = null;

	protected $_quiet = true;

	protected $_references = false;

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
		if (empty($this->name)) {
			$this->setError(JText::_('Error Acl Table Invalid Name'));
			return false;
		}

		// Check that section exists
		$this->_db->setQuery(
			'SELECT id FROM #__core_acl_acl_sections WHERE value = '.$this->_db->quote($this->section_value)
		);
		$id = $this->_db->loadResult();
		if (empty($id)) {
			$this->setError(JText::_('Error Acl Table Invalid Section'));
			return false;
		}

		// Check for dupliate name-section pair
		$this->_db->setQuery(
			'SELECT id FROM #__core_acl_acl'
			.' WHERE name = '.$this->_db->quote($this->name)
			.'  AND section_value = '.$this->_db->quote($this->section_value)
		);
		$id = $this->_db->loadResult();
		if (!empty($id) && $id != $this->id) {
			$this->setError(JText::_('Error Acl Table Invalid name already used'));
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
	 * Load an object by mathcing the `name` field
	 *
	 * @param	string $name
	 * @param	string $section	Optional section to match
	 *
	 * @return	boolean			True if successful, false if not found
	 */
	function loadByName($name, $section = null)
	{
		if (empty($name)) {
			$this->setError('Error Acl Invalid object name');
			return false;
		}

		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl)
			.' WHERE `name` = '.$this->_db->quote($name)
			.($section ? ' AND `section_value` = '.$this->_db->quote($section) : '')
		);
		if ($id = $this->_db->loadResult()) {
			return $this->load($id);
		}
		return false;
 	}

	/**
	 * Find the references to this object
	 *
	 * This method can only operate on a previously loaded object.
	 *
	 * @param	boolean $named		Return return values as names, otherwise values are returned
	 * @param	boolean $refresh	True if the references are to be refresh (eg, when used in a loop)
	 *
	 * @return	JAclReferences
	 * @access	public
	 */
	function &findReferences($named = false, $refresh = false)
	{
		$false = false;

		if (empty($this->id) || empty($this->section_value)) {
			$this->setError(JText::_('Error Acl Table Invalid properties to find references'));
			return $false;
		}

		if (empty($this->_references) || $refresh)
		{
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'acl'.DS.'aclreferences.php';

			$this->_references = new JAclReferences;

			// Find the references to mapped ACO's
			$this->_db->setQuery(
				($named ?
					'SELECT aco.name AS value, s.name AS section' :
					'SELECT aco.id AS value, aco.section_value AS section').
				' FROM #__core_acl_aco_map AS m'.
				//' LEFT JOIN #__core_acl_acl AS a ON a.id = m.acl_id'.
				' LEFT JOIN #__core_acl_aco AS aco ON aco.value = m.value AND aco.section_value = m.section_value'.
				($named ? ' LEFT JOIN #__core_acl_aco_sections AS s ON s.value = aco.section_value' : '').
				' WHERE m.acl_id = '.(int) $this->id
				.($named ? ' ORDER BY s.order_value, s.name, aco.order_value, aco.name' : '')
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAco($acl->section, $acl->value);
				}
			}

			// Find the references to mapped ARO's
			$this->_db->setQuery(
				($named ?
					'SELECT aro.name AS value, s.name AS section' :
					'SELECT aro.id AS value, aro.section_value AS section').
				' FROM #__core_acl_aro_map AS m'.
				//' LEFT JOIN #__core_acl_acl AS a ON a.id = m.acl_id'.
				' LEFT JOIN #__core_acl_aro AS aro ON aro.value = m.value AND aro.section_value = m.section_value'.
				($named ? ' LEFT JOIN #__core_acl_aro_sections AS s ON s.value = aro.section_value' : '').
				' WHERE m.acl_id = '.(int) $this->id
				.($named ? ' ORDER BY s.order_value, s.name, aro.order_value, aro.name' : '')
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAro($acl->section, $acl->value);
				}
			}

			// Find the references to mapped AXO's
			$this->_db->setQuery(
				($named ?
					'SELECT a.name AS value, s.name AS section' :
					'SELECT a.id AS value, a.section_value AS section').
				' FROM #__core_acl_axo_map AS m'.
				' LEFT JOIN #__core_acl_axo AS a ON a.value = m.value AND a.section_value = m.section_value'.
				($named ? ' LEFT JOIN #__core_acl_axo_sections AS s ON s.value = a.section_value' : '').
				' WHERE m.acl_id = '.(int) $this->id
				.($named ? ' ORDER BY s.order_value, s.name, a.order_value, a.name' : '')
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAxo($acl->section, $acl->value);
				}
			}

			// Find the references to mapped ARO's
			if ($named) {
				$this->_db->setQuery(
					'SELECT g.name'.
					' FROM #__core_acl_aro_groups_map AS m'.
					' INNER JOIN #__core_acl_aro_groups AS g ON g.id = m.group_id'.
					' WHERE acl_id = '.(int) $this->id
				);
			}
			else {
				$this->_db->setQuery(
					'SELECT m.group_id'.
					' FROM #__core_acl_aro_groups_map AS m'.
					' WHERE acl_id = '.(int) $this->id
				);
			}
			$result = $this->_db->loadResultArray();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}
			if (!empty($result)) {
				$this->_references->addAroGroup($result);
			}

			// Find the references to mapped AXO's
			if ($named) {
				$this->_db->setQuery(
					'SELECT g.name'.
					' FROM #__core_acl_axo_groups_map AS m'.
					' INNER JOIN #__core_acl_axo_groups AS g ON g.id = m.group_id'.
					' WHERE acl_id = '.(int) $this->id
				);
			}
			else {
				$this->_db->setQuery(
					'SELECT m.group_id'.
					' FROM #__core_acl_axo_groups_map AS m'.
					' WHERE acl_id = '.(int) $this->id
				);
			}
			$result = $this->_db->loadResultArray();
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

		if (!$this->_deleteReferences()) {
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
			$this->_quiet or $this->_log($type.': '.count($map).' maps');

			if (empty($map)) {
				continue;
			}

			$tuples		= array();

			foreach ($map as $sectionValue => $ids)
			{
				$this->_quiet or $this->_log('&raquo; '.$sectionValue.': '.count($ids).' ids');

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

				$this->_quiet or $this->_log('&raquo;&raquo; Tuples: '.print_r($tuples, true));
			}

			if (empty($tuples)) {
				continue;
			}

			// Create the lookup array
			$this->_db->setQuery(
				'SELECT value, id'.
				' FROM #__core_acl_'.$type.
				' WHERE id IN ('.implode(',', array_keys($tuples)).')'
			);
			$this->_quiet or $this->_log('&raquo; '.$this->_db->getQuery());

			$lookup = $this->_db->loadRowList(1);
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			$this->_quiet or $this->_log('&raquo; Lookup: '.print_r($lookup, true));

			// Assemble the mapping tuples
			foreach ($tuples as $id => $tuple) {
				$tuples[$id] = '('.(int) $this->id.','.$tuple.','.$this->_db->quote($lookup[$id][0]).')';
			}

			// Add the tuples
			$this->_db->setQuery(
				'INSERT INTO #__core_acl_'.$type.'_map (acl_id,section_value,value)'.
				' VALUES '.implode(',', $tuples)
			);
			$this->_quiet or $this->_log('&raquo; '.$this->_db->getQuery());
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
			$this->_quiet or $this->_log('Group '.$type.': '.count($groups).' groups');

			if (empty($groups)) {
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
			$this->_quiet or $this->_log('&raquo; '.$this->_db->getQuery());

			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		// @todo Clean ACL caching here

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

		jimport('joomla.database.query');
		$query  = new JQuery;
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
				'ac2' => '(ac.section_value='. $this->_db->quote($acoSectionValue) .' AND ac.value IN (\''. implode ('\',\'', $acoValues) .'\'))'
			);

			// Scan AROs
			foreach ($aros as $aroSectionValue => $aroValues)
			{
				if (empty($aroValues)) {
					continue;
				}

				$wheres['ar2'] = '(ar.section_value='. $this->_db->quote($aroSectionValue) .' AND ar.value IN (\''. implode ('\',\'', $aroValues) .'\'))';

				if (!empty($axos))
				{
					// Scan AXOs
					foreach ($axos as $axoSectionValue => $axoValues)
					{
						if (empty($axoValues)) {
							continue;
						}

						$wheres['ax1']	= 'ax.acl_id=a.id';
						$wheres['ax2']	= '(ax.section_value='. $this->_db->quote($axoSectionValue) .' AND ax.value IN (\''. implode ('\',\'', $axoValues) .'\'))';
						$this->_db->setQuery($sql.' WHERE '.implode(' AND ', $wheres));
						$result = $this->_db->loadResultArray();

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
					$result = $this->_db->loadResultArray();

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

	function _log($text)
	{
		echo '<br />'.$text;
	}
}
