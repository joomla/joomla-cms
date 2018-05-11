<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Templates\Administrator\View\Templates;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

/**
 * View class for a list of template styles.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The list of templates
	 *
	 * @var		array
	 * @since   1.6
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var		object
	 * @since   1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var		object
	 * @since   1.6
	 */
	protected $state;

	/**
	 * @var		string
	 * @since   3.2
	 */
	protected $file;

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
	 * Is the parameter enabled to show template positions in the frontend?
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	public $preview;

	/**
	 * Execute and display a template script.
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
		$this->preview       = ComponentHelper::getParams('com_templates')->get('template_positions_display');
		$this->file          = base64_encode('home');

		TemplatesHelper::addSubmenu('templates');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
		$canDo = ContentHelper::getActions('com_templates');

		// Set the title.
		if ((int) $this->get('State')->get('client_id') === 1)
		{
			\JToolbarHelper::title(\JText::_('COM_TEMPLATES_MANAGER_TEMPLATES_ADMIN'), 'eye thememanager');
		}
		else
		{
			\JToolbarHelper::title(\JText::_('COM_TEMPLATES_MANAGER_TEMPLATES_SITE'), 'eye thememanager');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			\JToolbarHelper::preferences('com_templates');
			\JToolbarHelper::divider();
		}

		\JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES');

		\JHtmlSidebar::setAction('index.php?option=com_templates&view=templates');

		$this->sidebar = \JHtmlSidebar::render();
	}
}
