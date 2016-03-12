<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of users.
 *
 * @since  1.6
 */
class UsersViewDebuguser extends JViewLegacy
{
	protected $actions;

	/**
	 * The item data.
	 *
	 * @var   object
	 * @since 1.6
	 */
	protected $items;

	/**
	 * The pagination object.
	 *
	 * @var   JPagination
	 * @since 1.6
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var   JObject
	 * @since 1.6
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.manage', 'com_users') || !JFactory::getConfig()->get('debug'))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$this->actions    = $this->get('DebugActions');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->user       = $this->get('User');
		$this->levels     = UsersHelperDebug::getLevelsOptions();
		$this->components = UsersHelperDebug::getComponents();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::sprintf('COM_USERS_VIEW_DEBUG_USER_TITLE', $this->user->id, $this->user->name), 'users user');

		JToolbarHelper::help('JHELP_USERS_DEBUG_USERS');

		JHtmlSidebar::setAction('index.php?option=com_users&view=debuguser&user_id=' . (int) $this->state->get('filter.user_id'));

		$option = '';

		if (!empty($this->components))
		{
			$option = JHtml::_('select.options', $this->components, 'value', 'text', $this->state->get('filter.component'));
		}

		JHtmlSidebar::addFilter(
			JText::_('COM_USERS_OPTION_SELECT_COMPONENT'),
			'filter_component',
			$option
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_USERS_OPTION_SELECT_LEVEL_START'),
			'filter_level_start',
			JHtml::_('select.options', $this->levels, 'value', 'text', $this->state->get('filter.level_start'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_USERS_OPTION_SELECT_LEVEL_END'),
			'filter_level_end',
			JHtml::_('select.options', $this->levels, 'value', 'text', $this->state->get('filter.level_end'))
		);

		$this->sidebar = JHtmlSidebar::render();
	}
}
