<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
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
 * @version		1.6
 */
class JAclAdmin
{
	/**
	 * Generic method to register a section
	 *
	 * @param	string $type	ACL | ACO | ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @return	int				The ID of the section
	 */
	public static function registerSection($type, $name, $value = null)
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

		// Bind the data.
		$data = array('name' => $name, 'value' => $value, 'order_value' => 0, 'hidden' => 0);
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
		throw new JException('TODO');
	}

	/**
	 * Registers a section for rules (ACL's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForRules($name, $value = null)
	{
		return JAclAdmin::registerSection('Acl', $name, $value);
	}

	/**
	 * Registers a section for actions (ACO's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForActions($name, $value = null)
	{
		return JAclAdmin::registerSection('Aco', $name, $value);
	}

	/**
	 * Registers a seciton for assets (AXO's)
	 *
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @return	int				The ID of the section
	 */
	public static function registerSectionForAssets($name, $value = null)
	{
		return JAclAdmin::registerSection('Axo', $name, $value);
	}

	/**
	 * Generic method to register a group
	 *
	 * @param	string $type	ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $parentId	The parent group
	 * @return	int				The ID of the group
	 */
	public static function registerGroup($type, $name, $value, $parentId = 29)
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

		// Sanitize and validate group parent id.
		$parentId = (int) $parentId;
		if (empty($parentId)) {
			throw new JException(JText::_('Error Acl Invalid parent group'));
		}

		// Get a group row instance.
		$group = JTable::getInstance($type.'Group');

		// Verify we got a proper JTable object.
		if (!is_a($group, 'JTable'.$type.'Group')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Bind the data.
		$data = array(
			'name'		=> $name,
			'value'		=> $value,
			'parent_id'	=> $parentId
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
		throw new JException('TODO');
	}

	/**
	 * Register a group for users (ARO's)
	 *
	 * @param	string $type	ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $parentId	The parent group
	 * @return	int				The ID of the group
	 */
	public static function registerGroupForUsers($name, $parentId = 29, $value = null)
	{
		if (empty($value)) {
			$value = preg_replace('#[^a-z0-9\-_]+#i', '-', strtolower($name));
		}
		return JAclAdmin::registerGroup('Aro', $name, $value, $parentId);
	}

	/**
	 * Register a group for assets (AXO's)
	 *
	 * @param	string $type	ARO | AXO
	 * @param	string $name	The name of the section
	 * @param	string $value	The value of the section (typically the option value for a component)
	 * @param	int $parentId	The parent group
	 * @return	int				The ID of the group
	 */
	public static function registerGroupForAssets($name)
	{
		return JAclAdmin::registerGroup('Axo', $name, 0, 1);
	}

	/**
	 * Creates a new action (ACO)
	 *
	 * JAclAdmin::createAction('com_fireboard', 'Create a forum', 'forum.create')
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
	 * @return	int				The ID of the Action
	 */
	public static function registerAction($type, $section, $name, $value, $note = '', $order = 0)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Aco');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAco')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

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

	public static function removeAction($section, $value)
	{
		throw new JException('TODO');
	}

	/**
	 * Creates a user (ARO) record (note this does not create a Joomla user)
	 *
	 * @param	string $name	The name of the user
	 * @param	int $value		The Joomla ID of the user
	 *
	 * @return	int				The ID of the section
	 */
	public function registerUser($name, $value)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Aro');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableAro')) {
			return new JException(JText::_('Error Acl Missing API'));
		}

		// Bind the data.
		$data = array(
			'section_value'	=> 'users',
			'name'			=> $name,
			'value'			=> (int) $value,
			'order_value'	=> 0,
		);
		if (!$table->bind($data)) {
			return new JException(JText::sprintf('Error Acl USer Bind Failed %s', $table->getError()));
		}

		// Check and validate the data.
		if (!$table->check()) {
			return new JException($table->getError());
		}

		// Store the data.
		if (!$table->store()) {
			$db = &JFactory::getDBO();
			return new JException($db->getErrorMsg());
		}

		return $table->id;
	}

	/**
	 * Creates a asset (AXO)
	 *
	 * @param	string $seciton	The section for the asset
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

	public static function removeAsset($section, $value)
	{
		throw new JException('TODO');
	}

	/**
	 * Adds a rule
	 *
	 * The mode allows you to add rules by Values or by Id's. Mode 0 is most typically used
	 * when creating rules by hand, such as at install time.  Mode 1 is most typically used
	 * when maintain rules via a user interface and Id's are posted back to for saving.
	 *
	 * @param	string $type		The type for the rule: 1, 2 or 3 (4 not supported)
	 * @param	string $section		The rule section
	 * @param	string $note		The title for the rule
	 * @param	array $userGroups	An array of User Group Values or Id's
	 * @param	array $actions		A nested an named array (by section) of ACO Values or Id's
	 * @param	array $assets		Optional: nested an named array (by section) of Asset Id's
	 * @param	array $assetGroups	Optional: An array of Asset Group Values or Id's
	 * @param	int $mode			The input mode; 0: By values (default, requiring translation to Id's); 1: By Id's
	 */
	public function registerRule($type, $section, $note, $userGroups, $actions, $assets = null, $assetGroups = null, $mode = 0)
	{
		static $cache = null;

		// Input validation checks
		if (empty($section)) {
			return new JException(JText::_('Error Acl Rule Section Empty'));
		}

		if (empty($userGroups)) {
			throw new JException(JText::_('Error Acl No user groups'));
		}

		if (empty($actions)) {
			throw new JException(JText::_('Error Acl No actions'));
		}

		if (!empty($assets) && !empty($assetGroups)) {
			throw new JException(JText::_('Error Acl Type 4 Rules not supported'));
		}

		if ($cache === null) {
			$cache = array();
		}

		// The references file is in the wrong place I know
		require_once dirname(__FILE__).DS.'references.php';
		$references = new JAclAdminReferences;
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
					throw new JException(JText::_sprinf('Error Acl User Group with value %s not found', $value));
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

			foreach ($actions as $section => $values)
			{
				foreach ($values as $k => $value)
				{
					$key = strtolower($section.'/'.$value);
					if (!isset($cache['acos'][$key])) {
						throw new JException(JText::_sprinf('Error Acl Action with value %s not found', $value));
					}
					$actions[$section][$k] = $cache['acos'][$key]['id'];
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

				foreach ($assets as $section => $values)
				{
					foreach ($values as $k => $value)
					{
						$key = strtolower($section.'/'.$value);
						if (!isset($cache['axos'][$key])) {
							throw new JException(JText::_sprinf('Error Acl Asset with value %s not found', $value));
						}
						$assets[$section][$k] = $cache['axos'][$key]['id'];

					}
				}
			}

			// AXO Groups reverse lookup
			if (!empty($assetGroups))
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
					$userGroups[$k] = $cache['axo_groups'][$value]['id'];
				}
			}
		}

		// Now we assemble the References
		$references->addAroGroup($userGroups);

		foreach ($actions As $section => $values) {
			$references->addAco($section, $values);
		}

		if (!empty($assets)) {
			foreach ($assets As $section => $values) {
				$references->addAxo($section, $values);
			}
		}

		if (!empty($assetGroups)) {
			$references->addAroGroup($assetGroups);
		}

		$table = &JTable::getInstance('Acl');

		$input = array(
			'section_value'	=> $section,
			'note'			=> $note,
			'enabled'		=> 1,
			'allow'			=> 1,
			'return_value'	=> '',
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

	public static function updateRule($id, $userGroups, $actions, $assets = null, $assetGroups = null)
	{
		throw new JException('TODO');
	}

	public static function deleteRule($id)
	{
		throw new JException('TODO');
	}

	//
	// Mapping Operations
	//

	function registerUserInGroups($mapId, $groupIds)
	{
		// Get a group row instance.
		$table = JTable::getInstance('GroupAroMap');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableGroupAroMap')) {
			return new JException(JText::_('Error Acl Missing Api'));
		}

		if (!$table->store($groupIds, $mapId)) {
			return new JException($table->getError());
		}

		return true;
	}

	function registerAssetInGroups($mapId, $groupIds)
	{
		// Get a group row instance.
		$table = JTable::getInstance('GroupAxoMap');

		// Verify we got a proper JTable object.
		if (!is_a($table, 'JTableGroupAxoMap')) {
			return new JException(JText::_('Error Acl Missing Api'));
		}

		if (!$table->store($groupIds, $mapId)) {
			return new JException($table->getError());
		}

		return true;
	}

	//
	// ACL Querying
	//

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

	function getGroupForUsers($value = null, $name = null)
	{
		return JAclAdmin::getGroup('Aro', $value, $name);
	}

	function getGroupForAssets($value = null, $name = null)
	{
		return JAclAdmin::getGroup('Aro', $value, $name);
	}

	function getObject($type, $value = null, $name = null)
	{
		$type = ucfirst(strtolower($type));
		if (!in_array($type, array('Acl', 'Aco', 'Aro', 'Axo'))) {
			return new JException(JText::_('Error Acl Invalid Object Type'));
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

	function getAction($value = null, $name = null)
	{
		return JAclAdmin::getObject('Aco', $value, $name);
	}

	function getUser($value = null, $name = null)
	{
		return JAclAdmin::getObject('Aro', $value, $name);
	}

	function getAsset($value = null, $name = null)
	{
		return JAclAdmin::getGroup('Axo', $value, $name);
	}
}
