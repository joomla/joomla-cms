<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		$this->enabled              = RedirectHelper::isEnabled();
		$this->collect_urls_enabled = RedirectHelper::collectUrlsEnabled();
		$this->items                = $this->get('Items');
		$this->pagination           = $this->get('Pagination');
		$this->state                = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
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
			$bar = JToolBar::getInstance('toolbar');

			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'links.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolbarHelper::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('links.trash');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_redirect');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER');

		JHtmlSidebar::setAction('index.php?option=com_redirect&view=links');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_state',
			JHtml::_('select.options', RedirectHelper::publishedOptions(), 'value', 'text', $this->state->get('filter.state'), true)
		);
	}
}
