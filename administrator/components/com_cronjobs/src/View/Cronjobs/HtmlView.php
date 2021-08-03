<?php
/**
 * Declares the MVC View for CronjobsModel.
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\View\Cronjobs;

// Restrict direct access
\defined('_JEXEC') or die;

use Exception;
use JObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

/**
 * The MVC View AddCronjob
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @since __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var   Pagination
	 * @since __DEPLOY_VERSION__
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var   JObject
	 * @since __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var   \JForm
	 * @since __DEPLOY_VERSION__
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var   array
	 * @since 4.0.0
	 */
	public $activeFilters;

	/**
	 * Is this view an Empty State
	 *
	 * @var   boolean
	 * @since 4.0.0
	 */
	private $isEmptyState = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed   A string if successful, otherwise an Error object.
	 */


	/**
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function display($tpl = null): void
	{
		// ! TODO : testing

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('empty_state');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		parent::display($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function addToolbar(): void
	{
		$canDo = ContentHelper::getActions('com_cronjobs');
		$user = Factory::getApplication()->getIdentity();

		/*
		* Get the toolbar object instance
		* !! TODO : Replace usage with ToolbarFactoryInterface
		*/
		$toolbar = Toolbar::getInstance('toolbar');

		// TODO : 'cronjobs' icon
		ToolbarHelper::title(Text::_('COM_CRONJOBS_MANAGER_CRONJOBS'), 'tags');

		if ($canDo->get('core.create'))
		{
			$toolbar->linkButton('new', 'JTOOLBAR_NEW')
				->url('index.php?option=com_cronjobs&view=select&layout=default')
				->buttonClass('btn btn-success')
				->icon('icon-new');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $user->authorise('core.admin')))
		{
			/** @var DropdownButton $dropdown */
			$dropdown = $toolbar->dropdownButton('status-group')
				->toggleSplit(false)
				->text('JTOOLBAR_CHANGE_STATUS')
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			// Add the batch Enable, Disable and Trash buttons if privileged
			if ($canDo->get('core.edit.state'))
			{
				$childBar->addNew('cronjobs.publish', 'JTOOLBAR_ENABLE')->listCheck(true)->icon('icon-publish');
				$childBar->addNew('cronjobs.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true)->icon('icon-unpublish');

				// We don't want the batch Trash button if displayed entries are all trashed
				if ($this->state->get('filter.state') != -2)
				{
					$childBar->trash('cronjobs.trash')->listCheck(true);
				}
			}
		}

		// Add "Empty Trash" button if filtering by trashed.
		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('cronjobs.delete')
				->message('JGLOBAL_CONFIRM_DELETE')
				->text('JTOOLBAR_EMPTY_TRASH')
				->listCheck(true);
		}

		// Link to component preferences if user has admin privileges
		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_cronjobs');
		}

		$toolbar->help('JHELP_COMPONENTS_CRONJOBS_MANAGER');
	}
}
