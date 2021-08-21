<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cookiemanager\Administrator\View\Scripts;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of scripts.
 *
 * @since   __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \JPagination
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	public $activeFilters;

	/**
	 * Is this view an Empty State
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	private $isEmptyState = false;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$user  = Factory::getApplication()->getIdentity();
		$canDo = ContentHelper::getActions('com_cookiemanager', 'category', $this->state->get('filter.category_id'));

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(Text::_('COM_COOKIEMANAGER_SCRIPTS'), 'code');

		if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_cookiemanager', 'core.create')) > 0)
		{
			$toolbar->addNew('script.add');
		}

		if (!$this->isEmptyState && $canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('scripts.publish')->listCheck(true);

			$childBar->unpublish('scripts.unpublish')->listCheck(true);

			$childBar->archive('scripts.archive')->listCheck(true);

			if ($this->state->get('filter.published') != -2)
			{
				$childBar->trash('scripts.trash')->listCheck(true);
			}
		}

		if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('scripts.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_cookiemanager'))
		{
			$toolbar->preferences('com_cookiemanager');
		}

		$toolbar->help('JHELP_COMPONENTS_COOKIEMANAGER_SCRIPTS');
	}
}
