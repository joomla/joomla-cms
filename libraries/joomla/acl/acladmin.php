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
			throw new JException(JText::sprintf('Error Acl Section Bind failed', $table->getError()));
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
			throw new JException(JText::sprintf('Error Acl Group bind failed', $group->getError()));
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
		return JAclAdmin::registerGroup('Aro', $name, $value, $parentId, $value);
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
	 * @param	string $section	The section for the permission
	 * @param	string $name	The readable name of the permission
	 * @param	string $value	The permission value
	 * @param	int $type		The permission type (1, 2 or 3)
	 *
	 * @return	int				The ID of the ACO
	 */
	public static function registerAction($section, $name, $value, $type = 1, $note = '')
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
			'order_value'	=> 0,
			'hidden'		=> 0,
			'note'			=> $note
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Action bind failed', $table->getError()));
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
	 * Creates a asset (AXO)
	 *
	 * @param	string $name	The name of the access level
	 * @return	int				The ID of the section
	 */
	public static function registerAsset($section, $name, $value)
	{
		// Get a group row instance.
		$table = JTable::getInstance('Axo');

		// Verify we got a proper JTable object.
		if (!is_a($section, 'JTableAxo')) {
			throw new JException(JText::_('Error Acl Missing API'));
		}

		// Bind the data.
		$data = array(
			'section_value'	=> $section,
			'name'			=> $name,
			'value'			=> (int) $value,
			'order_value'	=> 0,
		);
		if (!$table->bind($data)) {
			throw new JException(JText::sprintf('Error Acl Asset bind failed', $table->getError()));
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
	 * @param	string $title		The title for the rule
	 * @param	array $userGroups	An array of User Group Values or Id's
	 * @param	array $acos			A nested an named array (by section) of ACO Values or Id's
	 * @param	array $axos			Optional: nested an named array (by section) of AXO Values or Id's
	 * @param	array $levels		Optional: An array of AXO Group Values or Id's
	 * @param	int $mode			The input mode; 0: By values (default, requiring translation to Id's); 1: By Id's
	 */
	public static function registerRule($title, $userGroups, $acos, $axos = null, $levels = null, $mode = 0)
	{
		static $cache = null;

		// Input validation checks
		if (empty($userGroups)) {
			throw new JException(JText::_('Error Acl No user groups'));
		}

		if (empty($acos)) {
			throw new JException(JText::_('Error Acl No actions'));
		}

		if (!empty($axos) && !empty($levels)) {
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
				$cache['aro_groups'] = $db->loadRowList('value');
				if ($cache['aro_groups'] === null) {
					throw new JException($db->getErrorMsg());
				}
			}

			foreach ($userGroups as $k => $value) {
				if (!isset($cache['aro_groups'][$value])) {
					throw new JException(JText::_sprinf('Error Acl User Group with value %s not found', $value));
				}
				$userGroups[$k] = $cache['aro_groups'][$value][0];
			}

			// ACO reverse lookup
			if (!isset($cache['acos'])) {
				$db->setQuery(
					'SELECT id, LOWER(CONCAT_WS(\'/\', section_value, value)) AS section_value_key'.
					' FROM #__core_acl_aco'
				);
				$cache['acos'] = $db->loadRowList('section_value_key');
				if ($cache['acos'] === null) {
					throw new JException($db->getErrorMsg());
				}
			}

			foreach ($acos as $section => $values)
			{
				foreach ($values as $k => $value)
				{
					$key = strtolower($section.'/'.$value);
					if (!isset($cache['acos'][$key])) {
						throw new JException(JText::_sprinf('Error Acl Action with value %s not found', $value));
					}
					$acos[$section][$k] = $cache['acos'][$key][0];

				}
			}

			// AXO reverse lookup
			if (!empty($axos))
			{
				if (!isset($cache['axos'])) {
					$db->setQuery(
						'SELECT id, LOWER(CONCAT_WS(\'/\', section_value, value)) AS section_value_key'.
						' FROM #__core_acl_axo'
					);
					$cache['axos'] = $db->loadRowList('section_value_key');
					if ($cache['axos'] === null) {
						throw new JException($db->getErrorMsg());
					}
				}

				foreach ($axos as $section => $values)
				{
					foreach ($values as $k => $value)
					{
						$key = strtolower($section.'/'.$value);
						if (!isset($cache['axos'][$key])) {
							throw new JException(JText::_sprinf('Error Acl Asset with value %s not found', $value));
						}
						$axos[$section][$k] = $cache['axos'][$key][0];

					}
				}
			}

			// AXO Groups reverse lookup
			if (!empty($levels))
			{
				if (!isset($cache['axo_groups'])) {
					$db->setQuery(
						'SELECT id, value'.
						' FROM #__core_acl_axo_groups'
					);
					$cache['axo_groups'] = $db->loadRowList('value');
					if ($cache['axo_groups'] === null) {
						throw new JException($db->getErrorMsg());
					}
				}

				foreach ($levels as $k => $value) {
					if (!isset($cache['axo_groups'][$value])) {
						throw new JException(JText::_sprinf('Error Acl Asset group with value %s not found', $value));
					}
					$userGroups[$k] = $cache['axo_groups'][$value][0];
				}
			}
		}

		// Now we assemble the References
		$references->addAroGroup($userGroups);

		foreach ($acos As $section => $values) {
			$references->addAco($section, $values);
		}

		if (!empty($axos)) {
			foreach ($axos As $section => $values) {
				$references->addAxo($section, $values);
			}
		}

		if (!empty($axoGroups)) {
			$references->addAroGroup($axoGroups);
		}

		$table = &JTable::getInstance('Acl');

		$input = array(
			'note'			=> $note,
			'enabled'		=> 1,
			'allowed'		=> 1,
			'return_value'	=> '',
		);
		if (!$table->bind($input)) {
			throw new JException(JText::sprintf('Error Acl Rule bind failed', $table->getError()));
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
		if (!$table->addReferences($references)) {
			throw new JException($table->getError());
		}

		return $table->id;
	}

	public static function updateRule($id, $userGroups, $acos, $axos = null, $levels = null)
	{
		throw new JException('TODO');
	}

	public static function deleteRule($id)
	{
		throw new JException('TODO');
	}

}
