<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Menus\Administrator\View\Menus;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

/**
 * The HTML Menus Menu Menus View.
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
	 * List of all mod_mainmenu modules collated by menutype
	 *
	 * @var  array
	 */
	protected $modules;

	/**
	 * The pagination object
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
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
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->modules    = $this->get('Modules');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		if ($this->getLayout() == 'default')
		{
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}

		MenusHelper::addSubmenu('menus');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

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
		$canDo = ContentHelper::getActions('com_menus');

		ToolbarHelper::title(\JText::_('COM_MENUS_VIEW_MENUS_TITLE'), 'list menumgr');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('menu.add');
		}

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::deleteList('COM_MENUS_MENU_CONFIRM_DELETE', 'menus.delete', 'JTOOLBAR_DELETE');
		}

		ToolbarHelper::custom('menus.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);

		if ($canDo->get('core.admin') && $this->state->get('client_id') == 1)
		{
			ToolbarHelper::custom('menu.exportXml', 'download', 'download', 'COM_MENUS_MENU_EXPORT_BUTTON', true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::preferences('com_menus');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_MENUS_MENU_MANAGER');
	}
}
