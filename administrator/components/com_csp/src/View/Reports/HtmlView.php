<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\View\Reports;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Csp\Administrator\Helper\ReporterHelper;

/**
 * Reports view class for the Csp package.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 * @since  4.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  4.0.0
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
	 * The id of the httpheaders plugin in mysql
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected $httpHeadersId = 0;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->filterForm    = $this->get('FilterForm');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if (!(PluginHelper::isEnabled('system', 'httpheaders')))
		{
			$this->httpHeadersId = ReporterHelper::getHttpHeadersPluginId();
		}

		if (ComponentHelper::getParams('com_csp')->get('contentsecuritypolicy_mode', 'custom') === 'detect'
			&& ComponentHelper::getParams('com_csp')->get('contentsecuritypolicy', 0)
			&& ReporterHelper::getCspTrashStatus())
		{
			$this->trashWarningMessage = Text::_('COM_CSP_COLLECTING_TRASH_WARNING');
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_csp');

		ToolbarHelper::title(Text::_('COM_CSP_REPORTS'), 'shield-alt');

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('reports.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('reports.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'reports.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('reports.trash');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_csp');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_CSP_REPORTS');
	}
}
