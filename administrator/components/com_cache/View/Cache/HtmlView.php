<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Cache\Administrator\View\Cache;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * HTML View class for the Cache component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $data;

	protected $pagination;

	protected $state;

	/**
	 * Display a view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->data          = $this->get('Data');
		$this->pagination    = $this->get('Pagination');
		$this->total         = $this->get('Total');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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
		ToolbarHelper::title(Text::_('COM_CACHE_CLEAR_CACHE'), 'lightning clear');

		ToolbarHelper::custom('delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
		ToolbarHelper::custom('deleteAll', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE_ALL', false);
		ToolbarHelper::divider();

		if (Factory::getUser()->authorise('core.admin', 'com_cache'))
		{
			ToolbarHelper::preferences('com_cache');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_SITE_MAINTENANCE_CLEAR_CACHE');

		\JHtmlSidebar::setAction('index.php?option=com_cache');
	}
}
