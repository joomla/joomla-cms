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
	 * The user object of the user being debugged.
	 *
	 * @var   JUser
	 */
	protected $user;

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
		$this->user          = $this->get('User');
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
		JToolbarHelper::title(JText::sprintf('COM_USERS_VIEW_DEBUG_USER_TITLE', $this->user->id, $this->user->name), 'users user');

		JToolbarHelper::help('JHELP_USERS_DEBUG_USERS');
	}
}
