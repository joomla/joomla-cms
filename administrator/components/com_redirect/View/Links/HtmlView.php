<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Redirect\Administrator\View\Links;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

/**
 * View class for a list of redirection links.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * True if "System - Redirect Plugin" is enabled
	 *
	 * @var  boolean
	 */
	protected $enabled;

	/**
	 * True if "Collect URLs" is enabled
	 *
	 * @var  boolean
	 */
	protected $collect_urls_enabled;

	/**
	 * The id of the redirect plugin in mysql
	 *
	 * @var    integer
	 * @since  3.8.0
	 */
	protected $redirectPluginId = 0;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The model state
	 *
	 * @var  \Joomla\Registry\Registry
	 */
	protected $params;

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
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 * @throws  \JViewGenericdataexception
	 */
	public function display($tpl = null)
	{
		// Set variables
		$this->items                = $this->get('Items');
		$this->pagination           = $this->get('Pagination');
		$this->state                = $this->get('State');
		$this->filterForm           = $this->get('FilterForm');
		$this->activeFilters        = $this->get('ActiveFilters');
		$this->params               = ComponentHelper::getParams('com_redirect');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		if (!(PluginHelper::isEnabled('system', 'redirect') && RedirectHelper::collectUrlsEnabled()))
		{
			$this->redirectPluginId = RedirectHelper::getRedirectPluginId();
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
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_redirect');

		\JToolbarHelper::title(\JText::_('COM_REDIRECT_MANAGER_LINKS'), 'refresh redirect');

		if ($canDo->get('core.create'))
		{
			\JToolbarHelper::addNew('link.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($state->get('filter.state') != 2)
			{
				\JToolbarHelper::divider();
				\JToolbarHelper::publish('links.publish', 'JTOOLBAR_ENABLE', true);
				\JToolbarHelper::unpublish('links.unpublish', 'JTOOLBAR_DISABLE', true);
			}

			if ($state->get('filter.state') != -1 )
			{
				\JToolbarHelper::divider();

				if ($state->get('filter.state') != 2)
				{
					\JToolbarHelper::archiveList('links.archive');
				}
				elseif ($state->get('filter.state') == 2)
				{
					\JToolbarHelper::unarchiveList('links.publish', 'JTOOLBAR_UNARCHIVE');
				}
			}
		}

		if ($canDo->get('core.create'))
		{
			// Get the toolbar object instance
			$bar = \JToolbar::getInstance('toolbar');

			$title = \JText::_('JTOOLBAR_BULK_IMPORT');

			\JHtml::_('bootstrap.renderModal', 'collapseModal');

			// Instantiate a new \JLayoutFile instance and render the batch button
			$layout = new FileLayout('toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			\JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'links.delete', 'JTOOLBAR_EMPTY_TRASH');
			\JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::custom('links.purge', 'delete', 'delete', 'COM_REDIRECT_TOOLBAR_PURGE', false);
			\JToolbarHelper::trash('links.trash');
			\JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			\JToolbarHelper::preferences('com_redirect');
			\JToolbarHelper::divider();
		}

		\JToolbarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER');
	}
}
