<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewDebugGroup extends JViewLegacy
{
	protected $actions;
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.manage', 'com_users') || !JFactory::getConfig()->get('debug'))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$this->actions		= $this->get('DebugActions');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->group		= $this->get('Group');
		$this->levels		= UsersHelperDebug::getLevelsOptions();
		$this->components	= UsersHelperDebug::getComponents();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::sprintf('COM_USERS_VIEW_DEBUG_GROUP_TITLE', $this->group->id, $this->group->title), 'groups');

		JToolBarHelper::help('JHELP_USERS_DEBUG_GROUPS');
	}
}
