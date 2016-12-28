<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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

	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Set variables
		$app                        = JFactory::getApplication();
		$this->enabled              = RedirectHelper::isEnabled();
		$this->collect_urls_enabled = RedirectHelper::collectUrlsEnabled();
		$this->items                = $this->get('Items');
		$this->pagination           = $this->get('Pagination');
		$this->state                = $this->get('State');
		$this->filterForm           = $this->get('FilterForm');
		$this->activeFilters        = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Show messages about the enabled plugin and if the plugin should collect URLs
		if ($this->enabled && $this->collect_urls_enabled)
		{
			$app->enqueueMessage(JText::_('COM_REDIRECT_PLUGIN_ENABLED') . ' ' . JText::_('COM_REDIRECT_COLLECT_URLS_ENABLED'), 'notice');
		}
		elseif ($this->enabled && !$this->collect_urls_enabled)
		{
			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . RedirectHelper::getRedirectPluginId());
			$app->enqueueMessage(JText::_('COM_REDIRECT_PLUGIN_ENABLED') . JText::sprintf('COM_REDIRECT_COLLECT_URLS_DISABLED', $link), 'notice');
		}
		else
		{
			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . RedirectHelper::getRedirectPluginId());
			$app->enqueueMessage(JText::sprintf('COM_REDIRECT_PLUGIN_DISABLED', $link), 'error');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void.
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
