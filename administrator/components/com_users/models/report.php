<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelReport extends JModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'users.report';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$type = JRequest::getWord('type');
		$this->setState('report.type',	$type);

		$groupId = JRequest::getInt('group_id');
		$this->setState('report.group_id',	$groupId);
	}

	public function getActions()
	{
		$actions = array(
			'core.admin'		=> JText::_('JACTION_ADMIN'),
			'core.manage'		=> JText::_('JACTION_MANAGE'),
			'core.create'		=> JText::_('JACTION_CREATE'),
			'core.delete'		=> JText::_('JACTION_DELETE'),
			'core.edit'			=> JText::_('JACTION_EDIT'),
			'core.edit.state'	=> JText::_('JACTION_EDIT_STATE')
		);
		return $actions;
	}

	public function getData()
	{
		switch ($this->getState('report.type')) {
			case 'rules':
				$data = $this->_getRulesData();
				break;
		}

		return $data;
	}

	protected function _getRulesData()
	{
		// get the identities for the group.
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT b.id' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.lft >= b.lft AND a.rgt <= b.rgt' .
			' WHERE a.id = '.(int) $this->getState('report.group_id')
		);
		$identities = $db->loadResultArray();

		// Get list of extensions.
		$query	= $db->getQuery(true);
		$query->select('name, element');
		$query->from('#__extensions');
		$query->where('type = '.$db->quote('component'));

		$db->setQuery($query);
		$extensions = $db->loadObjectList();
		$actions = $this->getActions();

		foreach ($extensions as &$extension) {
			$extension->actions = array();

			$rules = JAccess::getAssetRules($extension->element, true);
			foreach ($actions as $action => $name) {
				$extension->actions[$action] = $rules->allow($action, $identities);
			}
		}

		return array(
			'extensions'	=> $extensions,
			'actions'		=> $actions
		);
	}
}