<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Users\Administrator\Helper\UsersHelper;
use Joomla\Database\DatabaseDriver;

/**
 * View class for a list of users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
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
	 * @var   \Joomla\CMS\Pagination\Pagination
	 * @since 1.6
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var   CMSObject
	 * @since 1.6
	 */
	protected $state;

	/**
	 * A \JForm instance with filter fields.
	 *
	 * @var    \JForm
	 * @since  3.6.3
	 */
	public $filterForm;

	/**
	 * An array with active filters.
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	public $activeFilters;

	/**
	 * An ACL object to verify user rights.
	 *
	 * @var    CMSObject
	 * @since  3.6.3
	 */
	protected $canDo;

	/**
	 * An instance of DatabaseDriver.
	 *
	 * @var    DatabaseDriver
	 * @since  3.6.3
	 */
	protected $db;

	/**
	 * The sidebar markup
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo         = ContentHelper::getActions('com_users');
		$this->db            = Factory::getDbo();

		UsersHelper::addSubmenu('users');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		$this->sidebar = HTMLHelper::_('sidebar.render');

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
		$canDo = $this->canDo;
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_USERS_VIEW_USERS_TITLE'), 'users user');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('user.add');
		}

		if ($canDo->get('core.edit.state') || $canDo->get('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('fa fa-globe')
					->buttonClass('btn btn-info')
					->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('users.activate', 'COM_USERS_TOOLBAR_ACTIVATE', true);
			$childBar->unpublish('users.block', 'COM_USERS_TOOLBAR_BLOCK', true);
			$childBar->standardButton('unblock')
					->text('COM_USERS_TOOLBAR_UNBLOCK')
					->task('users.unblock')
					->listCheck(true);
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_users')
			&& $user->authorise('core.edit', 'com_users')
			&& $user->authorise('core.edit.state', 'com_users'))
		{
			$toolbar->popupButton('batch')
				->text('JTOOLBAR_BATCH')
				->selector('collapseModal')
				->listCheck(true);
		}

		if ($canDo->get('core.delete'))
		{
			$toolbar->delete('users.delete')
				->text('JTOOLBAR_DELETE')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_users');
		}

		$toolbar->help('JHELP_USERS_USER_MANAGER');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.name'          => Text::_('COM_USERS_HEADING_NAME'),
			'a.username'      => Text::_('JGLOBAL_USERNAME'),
			'a.block'         => Text::_('COM_USERS_HEADING_ENABLED'),
			'a.activation'    => Text::_('COM_USERS_HEADING_ACTIVATED'),
			'a.email'         => Text::_('JGLOBAL_EMAIL'),
			'a.lastvisitDate' => Text::_('COM_USERS_HEADING_LAST_VISIT_DATE'),
			'a.registerDate'  => Text::_('COM_USERS_HEADING_REGISTRATION_DATE'),
			'a.id'            => Text::_('JGRID_HEADING_ID'),
		);
	}
}
