<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Modules\Administrator\View\Modules;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * View class for a list of modules.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

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
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->total         = $this->get('Total');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->clientId      = $this->state->get('client_id');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// We do not need the Language filter when modules are not filtered
		if ($this->clientId == 1 && !ModuleHelper::isAdminMultilang())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		// We don't need the toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		// If in modal layout.
		else
		{
			// Client id selector should not exist.
			$this->filterForm->removeField('client_id', '');

			// If in the frontend state and language should not activate the search tools.
			if (\JFactory::getApplication()->isClient('site'))
			{
				unset($this->activeFilters['state']);
				unset($this->activeFilters['language']);
			}
		}

		// Include the component HTML helpers.
		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		return parent::display($tpl);
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
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_modules');
		$user  = \JFactory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		if ($state->get('client_id') == 1)
		{
			ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_ADMIN'), 'cube module');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_SITE'), 'cube module');
		}

		if ($canDo->get('core.create'))
		{
			// Instantiate a new FileLayout instance and render the layout
			$layout = new FileLayout('toolbar.newmodule');

			$bar->appendButton('Custom', $layout->render(array()), 'new');
		}

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::custom('modules.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('modules.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('modules.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::checkin('modules.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_modules') && $user->authorise('core.edit', 'com_modules')
			&& $user->authorise('core.edit.state', 'com_modules'))
		{
			\JHtml::_('bootstrap.renderModal', 'collapseModal');
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new \FileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('modules.trash');
		}

		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_modules');
		}

		ToolbarHelper::help('JHELP_EXTENSIONS_MODULE_MANAGER');

		if (\JHtmlSidebar::getEntries())
		{
			$this->sidebar = \JHtmlSidebar::render();
		}
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
		$this->state = $this->get('State');

		if ($this->state->get('client_id') == 0)
		{
			if ($this->getLayout() == 'default')
			{
				return array(
					'ordering'       => Text::_('JGRID_HEADING_ORDERING'),
					'a.published'    => Text::_('JSTATUS'),
					'a.title'        => Text::_('JGLOBAL_TITLE'),
					'position'       => Text::_('COM_MODULES_HEADING_POSITION'),
					'name'           => Text::_('COM_MODULES_HEADING_MODULE'),
					'pages'          => Text::_('COM_MODULES_HEADING_PAGES'),
					'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
					'language_title' => Text::_('JGRID_HEADING_LANGUAGE'),
					'a.id'           => Text::_('JGRID_HEADING_ID')
				);
			}

			return array(
				'a.title'        => Text::_('JGLOBAL_TITLE'),
				'position'       => Text::_('COM_MODULES_HEADING_POSITION'),
				'name'           => Text::_('COM_MODULES_HEADING_MODULE'),
				'pages'          => Text::_('COM_MODULES_HEADING_PAGES'),
				'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
				'language_title' => Text::_('JGRID_HEADING_LANGUAGE'),
				'a.id'           => Text::_('JGRID_HEADING_ID')
			);
		}
		else
		{
			if ($this->getLayout() == 'default')
			{
				return array(
					'ordering'       => Text::_('JGRID_HEADING_ORDERING'),
					'a.published'    => Text::_('JSTATUS'),
					'a.title'        => Text::_('JGLOBAL_TITLE'),
					'position'       => Text::_('COM_MODULES_HEADING_POSITION'),
					'name'           => Text::_('COM_MODULES_HEADING_MODULE'),
					'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
					'a.language'     => Text::_('JGRID_HEADING_LANGUAGE'),
					'a.id'           => Text::_('JGRID_HEADING_ID')
				);
			}

			return array(
				'a.title'     => Text::_('JGLOBAL_TITLE'),
				'position'    => Text::_('COM_MODULES_HEADING_POSITION'),
				'name'        => Text::_('COM_MODULES_HEADING_MODULE'),
				'a.access'    => Text::_('JGRID_HEADING_ACCESS'),
				'a.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
				'a.id'        => Text::_('JGRID_HEADING_ID')
			);
		}
	}
}
