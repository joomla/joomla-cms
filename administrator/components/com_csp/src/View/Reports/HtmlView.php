<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\View\Reports;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
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
	 * Warning messages displayed above the list
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $warningMessages = [];

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

		$params = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if (!(PluginHelper::isEnabled('system', 'httpheaders')))
		{
			$this->httpHeadersId = ReporterHelper::getHttpHeadersPluginId();
		}

		if ($params->get('contentsecuritypolicy_mode', 'report') === 'report'
			&& $params->get('contentsecuritypolicy', 0))
		{
			$this->warningMessages[] = Text::_('COM_CSP_REPORT_MODE_WARNING');
		}

		if ($params->get('contentsecuritypolicy_mode', 'report') !== 'report'
			&& $params->get('contentsecuritypolicy', 0)
			&& ReporterHelper::getCspUnsafeInlineStatus())
		{
			$this->warningMessages[] = Text::_('COM_CSP_AUTO_UNSAFE_INLINE_WARNING');
		}

		if ($params->get('contentsecuritypolicy_mode', 'report') !== 'report'
			&& $params->get('contentsecuritypolicy', 0)
			&& ReporterHelper::getCspUnsafeEvalStatus())
		{
			$this->warningMessages[] = Text::_('COM_CSP_AUTO_UNSAFE_EVAL_WARNING');
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
		$user = Factory::getUser();

		$canDo = ContentHelper::getActions('com_csp');

		ToolbarHelper::title(Text::_('COM_CSP_REPORTS'), 'shield-alt');

		$toolbar = Toolbar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('report.add');
		}

		if ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('reports.publish')->listCheck(true);

			$childBar->unpublish('reports.unpublish')->listCheck(true);

			if ($user->authorise('core.admin'))
			{
				$childBar->checkin('reports.checkin')->listCheck(true);
			}

			if ($canDo->get('core.delete'))
			{
				$childBar->delete('reports.delete')
					->text('JTOOLBAR_DELETE')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_csp');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_CSP_REPORTS');
	}
}
