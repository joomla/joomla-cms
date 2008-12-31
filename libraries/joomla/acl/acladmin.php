<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Acl Administration class
 *
 * This basically replaces all the CRUD methods in the gacl_api class and can be lazy loaded on demand.
 * It should mainly be used during install and uninstall time for 3PD's to setup and teardown their
 * access control.
 *
 * @package		Joomla.Framework
 * @subpackage	Acl
 * @since		1.6
 */
class JAclAdmin
{
	//
	// ACL Maintenance
	//

	/**
	 * Generic method to register a section
	 *
	 * @param	string $type	ACL | ACO | ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $order		The order value for the section
	 * @return	int				The ID of the section
	 */
	public static function registerSection($type, $name, $value = null, $order = 0)
	{
		$type = ucfirst(strtolower($type));
		if (!in_array($type, array('Acl', 'Aco', 'Aro', 'Axo'))) {
			throw new JException(JText::_('Error Acl Invalid Section Type'));
		}

		// Get a group row instance.
		$table = JTable::getInstance($type.'Section');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTable'.$type.'Section')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the section if it already exists
		$table->loadByValue($value);

		// Bind the data.
		$data = array(
			'name'			=> $name,
			'value'			=> $value,
			'order_value'	=> (int) $order,
			'hidden'		=> 0
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Section Bind failed %s', $table->getError()));
		}

		// Check the data.
		if (!$table->check()) {
			throw new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			throw new JException($db->getErrorMsg());
		}

		return $table->id;
	}

	public static function removeSection($type, $value)
	{
		// Load the table object based on section and value, then nuke it

		throw new JException('TODO');
	}

	/**
	 * Registers a section for rules (ACL's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $order		The order value for the section
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForRules($name, $value = null, $order = 0)
	{
		return JAclAdmin::registerSection('Acl', $name, $value, $order);
	}

	/**
	 * Registers a section for actions (ACO's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $order		The order value for the section
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForActions($name, $value = null, $order = 0)
	{
		return JAclAdmin::registerSection('Aco', $name, $value, $order);
	}

	/**
	 * Registers a seciton for assets (AXO's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $order		The order value for the section
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForAssets($name, $value = null, $order = 0)
	{
		return JAclAdmin::registerSection('Axo', $name, $value, $order);
	}

	/**
	 * Generic method to register a group
	 *
	 * @param	string $type	ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $parentId	The parent group
	 * @param	string $section	The name of the section in which the group is placed
	 * @return	int				The ID of the group
	 */
	public static function registerGroup($type, $name, $value, $parentId = 1, $sectionValue = 'core')
	{
		$type = ucfirst(strtolower($type));
		if (!in_array($type, array('Aro', 'Axo'))) {
			throw new JException(JText::_('Error Acl Invalid section type'));
		}

		// Sanitize and validate group name.
		if (empty($name)) {
			throw new JException(JText::_('Error Acl Invalid group name'));
		}

		// Sanitize and validate group value.
		if (($value === null || $value === '') && $type == 'Aro') {
			throw new JException(JText::_('Error Acl Invalid group value'));
		}

		// Get the section
		if (!($section = JAclAdmin::getSection($type, $sectionValue))) {
			return new JException(JText::_('Error Acl Invalid section'));
		}

		// Get a group row instance.
		$group = JTable::getInstance($type.'Group');

		// Verify we got a proper JTable object.
		if (!is_a($group, 'JTable'.$type.'Group')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the section if it already exists
		if ($value !== null) {
			$group->loadByValue($value);
		}
		else if ($name) {
			$group->loadByName($value, $section->id);
		}

		$isNew = empty($group->id);

		// Sanitize and validate group parent id.
		$parentId = (int) $parentId;
		if ($isNew && empty($parentId)) {
			return new JException(JText::_('Error Acl Invalid parent group'));
		}

		// Bind the data.
		$data = array(
			'name'		=> $name,
			'value'		=> $value,
			'parent_id'	=> $parentId,
			'section_id' => $section->id
		);
		if (!$group->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Group bind failed %s', $group->getError()));
		}

		// Check the data.
		if (!$group->check()) {
			throw new JException($group->getError());
		}

		// Store the data.
		if (!$group->store()) {
			throw new JException($db->getErrorMsg());
		}

		if ($type == 'Axo') {
			// We need to syncronise the value with the Id to ensure uniqueness and to fool
			// older extensions into working properly (that is, they won't know about the new
			// access levels.
			$group->value = $group->id;
			if (!$group->store()) {
				throw new JException($db->getErrorMsg());
			}
		}

		return $group->id;
	}

	public static function removeGroup($type, $value)
	{
		// Load the table object based on section and value, then nuke it

		throw new JException('TODO');
	}

	/**
	 * Register a group for users (ARO's)
	 *
	 * @param	string $type	ARO | AXO
	 * @param	string $name	The name of the user group
	 * @param	string $value	The value of the user group
	 * @param	string $section	The name of the section in which the group is placed
	 * @param	int $parentId	The parent group id
	 * @return	int				The ID of the group
	 */
	public static function registerGroupForUsers($name, $value = null, $section = 'core', $parentId = 29)
	{
		if (empty($value)) {
			$value = preg_replace('#[^a-z0-9\-_]+#i', '-', strtolower($name));
		}
		return JAclAdmin::registerGroup('Aro', $name, $value, $parentId, $section);
	}

	/**
	 * Register a group for assets (AXO's)
	 *
	 * @param	string $name	The name of the asset group
	 * @param	string $section	The name of the section in which the group is placed
	 * @return	int				The ID of the group
	 */
	public static function registerGroupForAssets($name, $section = 'core')
	{
		return JAclAdmin::registerGroup('Axo', $name, 0, 1, $section);
	}

	/**
	 * Creates or updates an action (ACO) record
	 *
	 * If the action does not exist it is created.
	 * If an action matching the value is found, then the object is updated.
	 *
	 * $actionId = JAclAdmin::registerAction('com_fireboard', 'Create a forum', 'forum.create');
	 *
	 * Permission types:
	 *
	 * Type 1: User Group + Permission; such as Managers can Install Components
	 * (inheritance applies)
	 *
	 * Type 2: User Group + Permission + Object; such as Authors can Create Articles in the News Category
	 * (inheritance applies)
	 *
	 * Type 3: User Group + Permission + Access Level; such as Registered can View Articles in the Registered Access Level
	 * (inheritance not available)
	 *
	 * @param	int $type		Used in rule type (1, 2 or 3)
	 * @param	string $section	The section for the permission
	 * @param	string $name	The readable name of the permission
	 * @param	string $value	The permission value
	 * @param	string $note	An optional note to describe the purpose of the action
	 * @param	string $ording	An optional order value
	 *
	 * @return	int				The ID of the Action record
	 */
	public static function registerAction($type, $section, $name, $value, $note = '', $order = 0)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Aco');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAco')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the object if it already exists
		$table->loadByValue($value, $section);

		// Bind the data.
		$data = array(
			'section_value'	=> $section,
			'name'			=> $name,
			'value'			=> $value,
			'acl_type'		=> $type,
			'order_value'	=> $order,
			'hidden'		=> 0,
			'note'			=> $note
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Action bind failed %s', $table->getError()));
		}

		// Check and validate the data.
		if (!$table->check()) {
			throw new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			throw new JException($db->getErrorMsg());
		}

		return $table->id;
	}

	/**
	 * Creates or updates a user (ARO) record (note this does not create a Joomla user)
	 *
	 * If the user does not exist it is created.
	 * If a user matching the value is found, then the object is updated.
	 *
	 * $aroId = JAclAdmin::registerUser('My Administrator', 62);
	 *
	 * @param	string $name	The name of the user
	 * @param	int $value		The Joomla ID of the user
	 *
	 * @return	int				The ID of the User record (but this is not the Joomla User ID, that is the value)
	 */
	public function registerUser($name, $value)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Aro');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAro')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the object if it already exists
		$table->loadByValue($value, 'users');

		// Bind the data.
		$data = array(
			'section_value'	=> 'users',
			'name'			=> $name,
			'value'			=> (int) $value,
			'order_value'	=> 0,
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl USer Bind Failed %s', $table->getError()));
		}

		// Check and validate the data.
		if (!$table->check()) {
			throw new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			throw new JException($db->getErrorMsg());
		}

		return $table->id;
	}

	/**
	 * Creates or updates an asset (AXO)
	 *
	 * If the asset does not exist it is created.
	 * If an asset matching the section and value is found, then the object is updated.
	 *
	 * $assetId = JAclAdmin::registerAsset('com_fireboard', 'Top level category', 1);
	 *
	 * @param	string $section	The section for the asset
	 * @param	string $name	The name of the asset
	 * @param	int $value		The Id value of the asset
	 * @param	int $order		An optional order value
	 *
	 * @return	int				The ID of the asset
	 */
	public static function registerAsset($section, $name, $value, $order = 0)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Axo');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAxo')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the object if it already exists
		$table->loadByValue($value, $section);

		// Bind the data.
		$data = array(
			'section_value'	=> $section,
			'name'			=> $name,
			'value'			=> (int) $value,
			'order_value'	=> (int) $order,
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Asset bind failed %s', $table->getError()));
		}

		// Check and validate the data.
		if (!$table->check()) {
			throw new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			throw new JException($db->getErrorMsg());
		}

		return $table->id;
	}

	/**
	 * Removes an action (Aco)
	 *
	 * JAclAdmin::removeAction('com_fireboard', 'forum.create');
	 *
	 * @param	string $section	The action section
	 * @param	string $value	The action value
	 * @param	boolean $erase	True removes all referencing elements to the section
	 *
	 * @return	mixed			True if successful, a JException otherwise
	 */
	function removeAction($section, $value, $erase = false)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Aco');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAco')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the object if it already exists
		$table->loadByValue($value, $section);

		if (!$table->delete(null, $erase)) {
			throw new JException($table->getError());
		}

		return true;
	}

	/**
	 * Removes an asset (Axo)
	 *
	 * JAclAdmin::removeAsset('com_fireboard', 23);
	 *
	 * @param	string $section	The action section
	 * @param	string $value	The action value
	 * @param	boolean $erase	True removes all referencing elements to the section
	 *
	 * @return	mixed
	 */
	function removeAsset($section, $value, $erase = false)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Axo');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAxo')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Load the object if it already exists
		$table->loadByValue($value, $section);

		if (!$table->delete(null, $erase)) {
			throw new JException($table->getError());
		}

		return true;
	}

	//
	// Note: Names can change - just using familiar terminolgy for now
	//

	/**
	 * Adds a rule
	 *
	 * The mode allows you to add rules by Values or by Id's. Mode 0 is most typically used
	 * when creating rules by hand, such as at install time.  Mode 1 is most typically used
	 * when maintain rules via a user interface and Id's are posted back to for saving.
	 *
	 * @param	string $type		The type for the rule: 1, 2 or 3 (4 not supported)
	 * @param	string $section		The rule section
	 * @param	string $name		The name of the rule
	 * @param	string $note		The title for the rule
	 * @param	array $userGroups	An array of User Group Values or Id's
	 * @param	array $actions		A nested an named array (by section) of ACO Values or Id's
	 * @param	array $assets		Optional: nested an named array (by section) of Asset Id's
	 * @param	array $assetGroups	Optional: An array of Asset Group Values or Id's
	 * @param	int $mode			The input mode; 0: By values (default, requiring translation to Id's); 1: By Id's
	 * @param	string $returnValue	An optional return value for a successful check of the rule
	 */
	public function registerRule($type, $section, $name, $note, $userGroups, $actions, $assets = null, $assetGroups = null, $mode = 0, $returnValue = null)
	{
		static $cache = null;

		// Input validation checks
		if ((int) $type < 1 || (int) $type > 3) {
			throw new JException(JText::_('Error Acl Rule Type invalid'));
		}

		if (empty($section)) {
			throw new JException(JText::_('Error Acl Rule Section Empty'));
		}

		if (empty($userGroups)) {
			throw new JException(JText::_('Error Acl No user groups'));
		}

		if (empty($actions)) {
			throw new JException(JText::_('Error Acl No actions'));
		}

		// Need to use count because sometimes the variable could be 0
		if (count($assets) > 0 && count($assetGroups) > 0) {
			throw new JException(JText::_('Error Acl Type 4 Rules not supported'));
		}

		if ($cache === null) {
			$cache = array();
		}

		// The references file is in the wrong place I know
		require_once dirname(__FILE__).DS.'aclreferences.php';
		$references = new JAclReferences;
		$db = &JFactory::getDBO();

		if ($mode == 0)
		{
			// We need to translate all of the values into Id's

			// User groups reverse lookup
			if (!isset($cache['aro_groups'])) {
				$db->setQuery(
					'SELECT id, value'.
					' FROM #__core_acl_aro_groups'
				);
				$cache['aro_groups'] = $db->loadAssocList('value');
				if ($cache['aro_groups'] === null) {
					throw new JException($db->getErrorMsg());
				}
			}

			foreach ($userGroups as $k => $value) {
				if (!isset($cache['aro_groups'][$value])) {
					throw new JException(JText::sprinf('Error Acl User Group with value %s not found', $value));
				}
				$userGroups[$k] = $cache['aro_groups'][$value]['id'];
			}

			// ACO reverse lookup
			if (!isset($cache['acos'])) {
				$db->setQuery(
					'SELECT id, LOWER(CONCAT_WS(\'/\', section_value, value)) AS section_value_key'.
					' FROM #__core_acl_aco'
				);
				$cache['acos'] = $db->loadAssocList('section_value_key');
				if ($cache['acos'] === null) {
					throw new JException($db->getErrorMsg());
				}
			}

			foreach ($actions as $sect => $values)
			{
				foreach ($values as $k => $value)
				{
					$key = strtolower($sect.'/'.$value);
					if (!isset($cache['acos'][$key])) {
						throw new JException(JText::sprintf('Error Acl Action with value %s not found', $value));
					}
					$actions[$sect][$k] = $cache['acos'][$key]['id'];
				}
			}

			// AXO reverse lookup
			if (!empty($assets))
			{
				if (!isset($cache['axos'])) {
					$db->setQuery(
						'SELECT id, LOWER(CONCAT_WS(\'/\', section_value, value)) AS section_value_key'.
						' FROM #__core_acl_axo'
					);
					$cache['axos'] = $db->loadAssocList('section_value_key');
					if ($cache['axos'] === null) {
						throw new JException($db->getErrorMsg());
					}
				}

				foreach ($assets as $sect => $values)
				{
					foreach ($values as $k => $value)
					{
						$key = strtolower($sect.'/'.$value);
						if (!isset($cache['axos'][$key])) {
							throw new JException(JText::_sprinf('Error Acl Asset with value %s not found', $value));
						}
						$assets[$sect][$k] = $cache['axos'][$key]['id'];

					}
				}
			}

			// AXO Groups reverse lookup
			if (count($assetGroups))
			{
				if (!isset($cache['axo_groups'])) {
					$db->setQuery(
						'SELECT id, value'.
						' FROM #__core_acl_axo_groups'
					);
					$cache['axo_groups'] = $db->loadAssocList('value');
					if ($cache['axo_groups'] === null) {
						throw new JException($db->getErrorMsg());
					}
				}

				foreach ($assetGroups as $k => $value) {
					if (!isset($cache['axo_groups'][$value])) {
						throw new JException(JText::_sprinf('Error Acl Asset group with value %s not found', $value));
					}
					$assetGroups[$k] = $cache['axo_groups'][$value]['id'];
				}
			}
		}

		// Now we assemble the References
		$references->addAroGroup($userGroups);

		foreach ($actions As $sect => $values) {
			$references->addAco($sect, $values);
		}

		if (count($assets)) {
			foreach ($assets As $sect => $values) {
				$references->addAxo($sect, $values);
			}
		}

		if (count($assetGroups)) {
			$references->addAxoGroup($assetGroups);
		}

		$table = &JTable::getInstance('Acl');

		// Load the rule if it already exists
		$table->loadByName($name);

		$input = array(
			'acl_type'		=> (int) $type,
			'section_value'	=> $section,
			'note'			=> $note,
			'name'			=> $name,
			'enabled'		=> 1,
			'allow'			=> 1,
			'return_value'	=> $returnValue
		);

		if (!$table->bind($input)) {
			throw new JException($table->getError());
		}

		// Check the data.
		if (!$table->check()) {
			throw new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			throw new JException($db->getErrorMsg());
		}

		// Add the reference data
		if (!$table->updateReferences($references)) {
			throw new JException($table->getError());
		}

		return $table->id;
	}

	/**
	 * Delete a rule (Acl)
	 *
	 * Either the $id  of the Acl, or the $name of the Acl can be searched for.
	 * Note, if both are provided only $id is used.
	 *
	 * @param	int $id			The Id of the rule
	 * @param	string $name	The name of the rule
	 *
	 * @return	mixed			True is successful, false if the rule was not found, a JException otherwise
	 */
	function removeRule($id = 0, $name = null)
	{
		$table = &JTable::getInstance('Acl');

		if (!empty($id)) {
			if ($table->delete($id)) {
				return true;
			}
			else {
				throw new JException($table->getError());
			}
		}
		else if ($name !== null) {
			if ($table->loadByName($name)) {
				if ($table->delete()) {
					return true;
				}
				else {
					throw new JException($table->getError());
				}
			}
		}

		return false;
	}

	//
	// Mapping Operations
	//

	/**
	 * Stores the mapping of users to a user group
	 *
	 * @param int $mapId				The (Aro) id of the user being mapped (not the Joomla User Id)
	 * @param unknown_type $groupIds	A single group id or an array of ids to map the user to
	 *
	 * @return mixed					True if successful, a JException object otherwise
	 */
	function registerUserInGroups($mapId, $groupIds)
	{
		// Get a group row instance.
		$table = JTable::getInstance('GroupAroMap');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableGroupAroMap')) {
			throw new JException(JText::_('Error Acl Missing Api'));
		}

		if (!$table->store($groupIds, $mapId)) {
			throw new JException($table->getError());
		}

		return true;
	}

	/**
	 * Stores the mapping of assets to an asset group
	 *
	 * @param int $mapId				The id of the asset being mapped
	 * @param unknown_type $groupIds	A single group id or an array of ids to map the asset to
	 *
	 * @return mixed					True if successful, a JException object otherwise
	 */
	function registerAssetInGroups($mapId, $groupIds)
	{
		// Get a group row instance.
		$table = JTable::getInstance('GroupAxoMap');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableGroupAxoMap')) {
			throw new JException(JText::_('Error Acl Missing Api'));
		}

		if (!$table->store($groupIds, $mapId)) {
			throw new JException($table->getError());
		}

		return true;
	}

	//
	// ACL Querying
	// @todo Should this should be a different helper class?
	//

	/**
	 * Gets the group object based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $type	The group type: Aro | Axo
	 * @param	string $value	The value of the group to search for
	 * @param	string $name	The name of the group to search for
	 *
	 * @return	mixed			Either a JTable object, or a JException if an error occurred
	 */
	function getGroup($type, $value = null, $name = null)
	{
		$type = ucfirst(strtolower($type));
		if (!in_array($type, array('Aro', 'Axo'))) {
			throw new JException(JText::_('Error Acl Invalid Group Type'));
		}
		$table = &JTable::getInstance($type.'Group');

		if ($value !== null) {
			if ($table->loadByvalue($value)) {
				return $table;
			}
		}
		else if ($name !== null) {
			if ($table->loadByName($name)) {
				return $table;
			}
		}
		return null;
	}

	/**
	 * Gets the user group object based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $value	The value of the group to search for
	 * @param	string $name	The name of the group to search for
	 *
	 * @return	mixed			Either a JTable object, or a JException if an error occurred
	 */
	function getGroupForUsers($value = null, $name = null)
	{
		return JAclAdmin::getGroup('Aro', $value, $name);
	}

	/**
	 * Gets the asset group object based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $value	The value of the group to search for
	 * @param	string $name	The name of the group to search for
	 *
	 * @return	mixed			Either a JTable object, or a JException if an error occurred
	 */
	function getGroupForAssets($value = null, $name = null)
	{
		return JAclAdmin::getGroup('Axo', $value, $name);
	}

	/**
	 * Gets an object of the required type based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $type	The object type: Aco | Aro | Axo
	 * @param	string $value	The value of the object to search for
	 * @param	string $name	The name of the object to search for
	 *
	 * @return	mixed			Either a JTable object, a JException if an error occurred, or null if not found
	 */
	function getObject($type, $value = null, $name = null)
	{
		$type = ucfirst(strtolower($type));
		if (!in_array($type, array('Aco', 'Aro', 'Axo'))) {
			throw new JException(JText::_('Error Acl Invalid Object Type'));
		}
		$table = &JTable::getInstance($type);
		if ($value !== null) {
			if ($table->loadByvalue($value)) {
				return $table;
			}
		}
		else if ($name !== null) {
			if ($table->loadByName($name)) {
				return $table;
			}
		}
		return null;
	}

	/**
	 * Gets an action object of the required type based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $value	The value of the object to search for
	 * @param	string $name	The name of the object to search for
	 *
	 * @return	mixed			Either a JTable object, a JException if an error occurred, or null if not found
	 */
	function getAction($value = null, $name = null)
	{
		return JAclAdmin::getObject('Aco', $value, $name);
	}

	/**
	 * Gets a user object of the required type based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $value	The value of the object to search for
	 * @param	string $name	The name of the object to search for
	 *
	 * @return	mixed			Either a JTable object, a JException if an error occurred, or null if not found
	 */
	function getUser($value = null, $name = null)
	{
		return JAclAdmin::getObject('Aro', $value, $name);
	}

	/**
	 * Gets an asset object of the required type based on the inputs
	 *
	 * Either the $value of the group, or the $name of the group can be searched for.
	 * Note, if both are provided only $value is used.
	 *
	 * @param	string $value	The value of the object to search for
	 * @param	string $name	The name of the object to search for
	 *
	 * @return	mixed			Either a JTable object, a JException if an error occurred, or null if not found
	 */
	function getAsset($value = null, $name = null)
	{
		return JAclAdmin::getObject('Axo', $value, $name);
	}

	/**
	 * Gets a rule object of the required type based on the inputs
	 *
	 * @param	string $name	The name of the rule to search for
	 * @param	string $section	An optional section to avoid duplicate names in different sections
	 *
	 * @return	mixed			Either a JTable object, a JException if an error occurred, or null if not found
	 */
	function getRule($name, $section = null)
	{
		$table = &JTable::getInstance('Acl');
		if ($table->loadByName($name)) {
			return $table;
		}
		return null;
	}

	//
	// Other utility methods
	//

	/**
	 * Syncronise the assets from a local content store
	 *
	 * @param	array $items	A named array (by the Id field of the foreign asset table) of object have the properties id, title, access and ordering
	 * @param	string $section	The asset section the object will belong to
	 * @return	mixed			True if successful, otherwise a JException
	 * @throws	JException
	 */
	function synchronizeAssets($items, $section)
	{
		//
		// Get the existing assets.
		//

		$db = &JFactory::getDbo();

		// Note the limitation of one groups per asset
		// If multiple groups are required then convert to using GROUP_CONCAT
		$db->setQuery(
			'SELECT axo.*, map.group_id'
			.' FROM #__core_acl_axo AS axo'
			.' LEFT JOIN #__core_acl_groups_axo_map AS map ON map.axo_id = axo.id'
			.' WHERE section_value = '.$db->quote($section)
			.' ORDER BY section_value,order_value,name'
		);

		// Get the raw assets from the model.
		$raw = $db->loadObjectList();

		// Cache the Asset Groups
		$db->setQuery(
			'SELECT id, value'
			.' FROM #__core_acl_axo_groups'
		);
		$groups = $db->loadObjectList('value');

		//
		// Build the synchronization lists.
		//

		// Create an asset list keyed by value.
		$assets	= array();
		foreach ($raw as $i => $axo) {
			$assets[$axo->value] = &$raw[$i];
		}

		// Get the IDs which are stored as the array keys for both assets and items.
		$keys1	= array_keys($items);
		$keys2	= array_keys($assets);

		// Create the synchronization lists to add, drop and update.
		$add	= array_diff($keys1, $keys2);
		$drop	= array_diff($keys2, $keys1);
		$update	= array_intersect($keys1, $keys2);

		//
		// Perform the asset synchronization.
		//

		// Get an AXO (asset) table object.
		$table = JTable::getInstance('Axo');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAxo')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Perform the add operations first.
		if (!empty($add))
		{
			foreach ($add as $id)
			{
				// Bind the data.
				$data = array(
					'section_value'	=> $section,
					'name'			=> $items[$id]->title,
					'value'			=> (int) $items[$id]->id,
					'order_value'	=> (int) $items[$id]->ordering,
				);
				if (!$table->bind($data)) {
					throw new JException(JText::sprintf('Error Acl Asset bind failed %s', $table->getError()));
				}

				// Check and validate the data.
				if (!$table->check()) {
					throw new JException($table->getError());
				}

				// Store the data.
				if (!$table->store()) {
					throw new JException($table->getError());
				}

				JAclAdmin::registerAssetInGroups($table->id, $groups[$items[$id]->access]);

				// Clear the table object.
				$table->clear();
			}
		}

		// Next perform the drop operations.
		if (!empty($drop))
		{
			foreach ($drop as $id)
			{
				// Attempt to delete the asset and all references.
				if (!$table->delete($assets[$id]->id, true)) {
					throw new JException($table->getError());
				}

				// Clear the table object.
				$table->clear();
			}
		}

		// Lastly perform the update operations.
		if (!empty($update))
		{
			foreach ($update as $id)
			{
				// If the name and ordering are the same, we do not need to do anything.
				if (($assets[$id]->name != $items[$id]->title) || ($assets[$id]->order_value != $items[$id]->ordering))
				{
					// Bind the data.
					$data = array(
						'id'			=> (int) $assets[$id]->id,
						'section_value'	=> $assets[$id]->section_value,
						'value'			=> (int) $assets[$id]->value,
						'name'			=> $items[$id]->title,
						'order_value'	=> (int) $items[$id]->ordering,
						'hidden'		=> 0
					);
					if (!$table->bind($data)) {
						throw new JException(JText::sprintf('Error Acl Asset bind failed %s', $table->getError()));
					}

					// Check and validate the data.
					if (!$table->check()) {
						throw new JException($table->getError());
					}

					// Store the data.
					if (!$table->store()) {
						throw new JException($table->getError());
					}
				}

				// If the access fields are the same, then no need to change the groups
				if ($assets[$id]->group_id !== $items[$id]->access) {
					JAclAdmin::registerAssetInGroups((int) $assets[$id]->id, $groups[$items[$id]->access]->id);
				}

				// Clear the table object.
				$table->clear();
			}
		}

		return true;
	}
}
