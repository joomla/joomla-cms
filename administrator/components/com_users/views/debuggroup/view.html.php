<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of users.
 *
 * @since  1.6
 */
class UsersViewDebuggroup extends JViewLegacy
{
	/**
	 * List of component actions
	 *
	 * @var  array
	 */
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
	 * The id and title for the user group.
	 *
	 * @var   stdClass
	 * @since __DEPLOY_VERSION__
	 */
	protected $group;

	/**
	 * Form object for search filters
	 *
	 * @var  JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * An array containing the component levels.
	 *
	 * @var         array
	 * @since       __DEPLOY_VERSION__
	 * @deprecated  4.0 To be removed with Hathor
	 */
	public $levels;

	/**
	 * An array of installed components with a text property containing component name and the value property
	 * containing the extension element (e.g. plg_system_debug)
	 *
	 * @var         stdClass[]
	 * @since       __DEPLOY_VERSION__
	 * @deprecated  4.0 To be removed with Hathor
	 */
	public $components;

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
		if (!JFactory::getUser()->authorise('core.manage', 'com_users'))
		{
			throw new JUserAuthorizationexception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->actions       = $this->get('DebugActions');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->group         = $this->get('Group');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
		$canDo = JHelperContent::getActions('com_users');

		JToolbarHelper::title(JText::sprintf('COM_USERS_VIEW_DEBUG_GROUP_TITLE', $this->group->id, $this->group->title), 'users groups');
		JToolbarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_users');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_USERS_DEBUG_GROUPS');
	}
}
