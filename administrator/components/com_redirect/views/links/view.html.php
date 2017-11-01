<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of redirection links.
 *
 * @since  1.6
 */
class RedirectViewLinks extends JViewLegacy
{
	protected $enabled;

	protected $collect_urls_enabled;

	protected $redirectPluginId = 0;

	protected $items;

	protected $pagination;

	protected $state;

	public $filterForm;

	public $activeFilters;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Set variables
		$app                        = JFactory::getApplication();
		$this->enabled              = JPluginHelper::isEnabled('system', 'redirect');
		$this->collect_urls_enabled = RedirectHelper::collectUrlsEnabled();
		$this->items                = $this->get('Items');
		$this->pagination           = $this->get('Pagination');
		$this->state                = $this->get('State');
		$this->filterForm           = $this->get('FilterForm');
		$this->activeFilters        = $this->get('ActiveFilters');
		$this->params               = JComponentHelper::getParams('com_redirect');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Show messages about the enabled plugin and if the plugin should collect URLs
		if ($this->enabled && $this->collect_urls_enabled)
		{
			$app->enqueueMessage(JText::sprintf('COM_REDIRECT_COLLECT_URLS_ENABLED', JText::_('COM_REDIRECT_PLUGIN_ENABLED')), 'notice');
		}
		else
		{
			$this->redirectPluginId = RedirectHelper::getRedirectPluginId();

			$link = JHtml::_(
				'link',
				'#plugin' . $this->redirectPluginId . 'Modal',
				JText::_('COM_REDIRECT_SYSTEM_PLUGIN'),
				'class="alert-link" data-toggle="modal" id="title-' . $this->redirectPluginId . '"'
			);

			// To be removed in Joomla 4
			if (JFactory::getApplication()->getTemplate() === 'hathor')
			{
				$link = JHtml::_(
					'link',
					JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . RedirectHelper::getRedirectPluginId()),
					JText::_('COM_REDIRECT_SYSTEM_PLUGIN')
				);
			}

			if ($this->enabled && !$this->collect_urls_enabled)
			{
				$app->enqueueMessage(JText::sprintf('COM_REDIRECT_COLLECT_MODAL_URLS_DISABLED', JText::_('COM_REDIRECT_PLUGIN_ENABLED'), $link), 'notice');
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_REDIRECT_PLUGIN_MODAL_DISABLED', $link), 'error');
			}
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
		$canDo = JHelperContent::getActions('com_redirect');

		JToolbarHelper::title(JText::_('COM_REDIRECT_MANAGER_LINKS'), 'refresh redirect');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('link.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('link.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($state->get('filter.state') != 2)
			{
				JToolbarHelper::divider();
				JToolbarHelper::publish('links.publish', 'JTOOLBAR_ENABLE', true);
				JToolbarHelper::unpublish('links.unpublish', 'JTOOLBAR_DISABLE', true);
			}

			if ($state->get('filter.state') != -1 )
			{
				JToolbarHelper::divider();

				if ($state->get('filter.state') != 2)
				{
					JToolbarHelper::archiveList('links.archive');
				}
				elseif ($state->get('filter.state') == 2)
				{
					JToolbarHelper::unarchiveList('links.publish', 'JTOOLBAR_UNARCHIVE');
				}
			}
		}

		if ($canDo->get('core.create'))
		{
			// Get the toolbar object instance
			$bar = JToolbar::getInstance('toolbar');

			$title = JText::_('JTOOLBAR_BULK_IMPORT');

			JHtml::_('bootstrap.modal', 'collapseModal');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'links.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::custom('links.purge', 'delete', 'delete', 'COM_REDIRECT_TOOLBAR_PURGE', false);
			JToolbarHelper::trash('links.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_redirect');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER');
	}
}
